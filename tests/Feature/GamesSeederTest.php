<?php

use App\Models\Juego;
use App\Models\User;
use Database\Seeders\GamesSeeder;

test('games seeder synchronizes the active catalog without deleting old games', function () {
    $fifa = Juego::create([
        'nombre' => 'FIFA',
        'genero' => 'Sports',
        'costo_inscripcion' => 3,
        'fecha_limite_inscripcion' => '2025-05-04',
        'modalidad' => 'individual',
        'img_id' => 'fifa',
    ]);

    $retiredGame = Juego::create([
        'nombre' => 'Tetris',
        'genero' => 'Logica',
        'costo_inscripcion' => 3,
        'fecha_limite_inscripcion' => '2025-06-02',
        'modalidad' => 'individual',
        'img_id' => 'tetris',
    ]);

    $this->seed(GamesSeeder::class);
    $this->seed(GamesSeeder::class);

    $updatedFifa = $fifa->fresh();
    expect($updatedFifa->nombre)->toBe('FIFA 26');
    expect($updatedFifa->img_id)->toBe('/imgGames/fifa-26.webp');
    expect((bool) $updatedFifa->active)->toBeTrue();
    expect((bool) $retiredGame->fresh()->active)->toBeFalse();
    expect(Juego::query()->where('active', true)->count())->toBe(12);
    expect(
        Juego::query()
            ->where('active', true)
            ->pluck('img_id')
            ->every(fn (string $imagePath): bool => str_starts_with($imagePath, '/imgGames/')),
    )->toBeTrue();
    expect(
        Juego::query()
            ->where('active', true)
            ->pluck('reglamentos_pdf')
            ->every(fn (string $pdfPath): bool => str_starts_with($pdfPath, '/reglamento/') && str_ends_with($pdfPath, '.pdf')),
    )->toBeTrue();

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => '5551234567',
    ]);

    $this->actingAs($user)
        ->postJson('/guardar_all_inscripciones', [
            'juegos' => json_encode([['id' => $retiredGame->id]], JSON_THROW_ON_ERROR),
            'valor_comprobante' => 3,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('juegos');
});
