<?php

use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('authenticated users can upload a private local receipt', function () {
    Storage::fake('local');

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => '5551234567',
    ]);

    $response = $this->actingAs($user)
        ->post('/comprobantes/upload', [
            'comprobante' => UploadedFile::fake()->createWithContent(
                'receipt.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC'),
            ),
        ])
        ->assertCreated()
        ->assertJsonStructure(['path']);

    $path = $response->json('path');
    expect($path)->toMatch('/^users\/'.$user->id.'\/[0-9a-f-]+\.png$/i');
    Storage::disk('local')->assertExists($path);
});

test('inscriptions use the authenticated user and the database price', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => '5551234567',
    ]);
    $otherUser = User::factory()->create(['email_verified_at' => now()]);
    $game = Juego::create([
        'nombre' => 'Paid test game',
        'genero' => 'Test',
        'costo_inscripcion' => 5,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'individual',
    ]);
    $receiptPath = "users/{$user->id}/00000000-0000-4000-8000-000000000000.jpg";

    $this->actingAs($user)
        ->postJson('/guardar_all_inscripciones', [
            'user_id' => $otherUser->id,
            'juegos' => json_encode([['id' => $game->id]], JSON_THROW_ON_ERROR),
            'nro_comprobante' => 'PAY-123',
            'valor_comprobante' => '5.00',
            'comprobante_pago' => $receiptPath,
        ])
        ->assertCreated();

    $this->assertDatabaseHas('inscripciones_individuales', [
        'user_id' => $user->id,
        'id_juego' => $game->id,
        'valor_comprobante' => 5,
        'comprobante_pago' => $receiptPath,
    ]);

    expect(InscripcionIndividual::query()->where('user_id', $otherUser->id)->exists())->toBeFalse();
});

test('only admins can view locally stored receipts', function () {
    Storage::fake('local');

    $admin = User::factory()->create(['phone' => '5551234567']);
    $admin->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));

    $game = Juego::create([
        'nombre' => 'Receipt access game',
        'genero' => 'Test',
        'costo_inscripcion' => 5,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'individual',
    ]);
    $receiptPath = "users/{$admin->id}/00000000-0000-4000-8000-000000000000.jpg";
    Storage::disk('local')->put($receiptPath, 'private receipt');
    $inscription = InscripcionIndividual::create([
        'user_id' => $admin->id,
        'id_juego' => $game->id,
        'estado' => 'inscrito',
        'comprobante_pago' => $receiptPath,
    ]);

    $response = $this->actingAs($admin)
        ->get("/admin/comprobantes/individual/{$inscription->id}")
        ->assertOk();

    expect($response->baseResponse->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
    expect(file_get_contents($response->baseResponse->getFile()->getPathname()))
        ->toBe('private receipt');

    $user = User::factory()->create(['phone' => '5559876543']);

    $this->actingAs($user)
        ->getJson("/admin/comprobantes/individual/{$inscription->id}")
        ->assertForbidden();
});
