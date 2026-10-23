<?php

use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('receipt upload URLs are signed and scoped to the authenticated user', function () {
    config([
        'services.supabase.url' => 'https://project.supabase.co',
        'services.supabase.secret_key' => 'sb_secret_test-secret-key',
        'services.supabase.storage_bucket' => 'inscripciones',
    ]);

    Http::fake(function (Request $request) {
        $requestPath = parse_url($request->url(), PHP_URL_PATH);

        return Http::response([
            'url' => substr($requestPath, strlen('/storage/v1')).'?token=signed-token',
        ]);
    });

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => '5551234567',
    ]);

    $response = $this->actingAs($user)
        ->postJson('/comprobantes/upload-url', ['content_type' => 'image/jpeg'])
        ->assertOk()
        ->assertJsonStructure(['path', 'url']);

    $path = $response->json('path');
    expect($path)->toMatch('/^users\/'.$user->id.'\/[0-9a-f-]+\.jpg$/i');
    expect(str_ends_with($response->json('url'), "/storage/v1/object/upload/sign/inscripciones/{$path}?token=signed-token"))->toBeTrue();

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/object/upload/sign/inscripciones/users/'.$user->id.'/')
        && $request->hasHeader('apikey', 'sb_secret_test-secret-key')
        && ! $request->hasHeader('Authorization'));
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

test('only admins receive short-lived download URLs for stored receipts', function () {
    config([
        'services.supabase.url' => 'https://project.supabase.co',
        'services.supabase.secret_key' => 'test-secret-key',
        'services.supabase.storage_bucket' => 'inscripciones',
        'services.supabase.signed_url_ttl' => 300,
    ]);

    Http::fake(fn (Request $request) => Http::response([
        'signedURL' => '/object/sign/inscripciones/users/1/00000000-0000-4000-8000-000000000000.jpg?token=read-token',
    ]));

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
    $inscription = InscripcionIndividual::create([
        'user_id' => $admin->id,
        'id_juego' => $game->id,
        'estado' => 'inscrito',
        'comprobante_pago' => $receiptPath,
    ]);

    $this->actingAs($admin)
        ->getJson("/admin/comprobantes/individual/{$inscription->id}")
        ->assertOk()
        ->assertJson([
            'url' => "https://project.supabase.co/storage/v1/object/sign/inscripciones/{$receiptPath}?token=read-token",
        ]);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), '/object/sign/inscripciones/'.$receiptPath)
        && $request['expiresIn'] === 300);

    $user = User::factory()->create(['phone' => '5559876543']);

    $this->actingAs($user)
        ->getJson("/admin/comprobantes/individual/{$inscription->id}")
        ->assertForbidden();
});
