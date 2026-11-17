<?php

use App\Models\Equipo;
use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('account with linked registrations cannot be deleted and its records are preserved', function () {
    $user = User::factory()->create(['phone' => '5551234567']);
    $role = Role::create(['name' => 'jugador', 'guard_name' => 'web']);
    $user->assignRole($role);

    $individualGame = Juego::create([
        'nombre' => 'Juego individual',
        'genero' => 'Deportes',
        'costo_inscripcion' => 10,
        'fecha_limite_inscripcion' => now()->addWeek()->toDateString(),
        'modalidad' => 'individual',
    ]);

    $teamGame = Juego::create([
        'nombre' => 'Juego grupal',
        'genero' => 'Estrategia',
        'costo_inscripcion' => 10,
        'fecha_limite_inscripcion' => now()->addWeek()->toDateString(),
        'modalidad' => 'grupo',
    ]);

    $individualRegistration = InscripcionIndividual::create([
        'user_id' => $user->id,
        'id_juego' => $individualGame->id,
        'estado' => 'inscrito',
    ]);

    $team = Equipo::create([
        'user_id' => $user->id,
        'id_juego' => $teamGame->id,
        'nombre_equipo' => 'Equipo de prueba',
        'miembros' => 'Participante de prueba',
    ]);

    $groupRegistration = InscripcionGrupal::create([
        'id_equipo' => $team->id,
        'id_juego' => $teamGame->id,
        'estado' => 'inscrito',
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/settings/profile')
        ->delete('/settings/profile', ['password' => 'password']);

    $response
        ->assertSessionHasErrors('account')
        ->assertRedirect('/settings/profile');

    expect($user->fresh())->not->toBeNull()
        ->and($user->fresh()->hasRole('jugador'))->toBeTrue()
        ->and($individualRegistration->fresh())->not->toBeNull()
        ->and($team->fresh())->not->toBeNull()
        ->and($groupRegistration->fresh())->not->toBeNull();
});
