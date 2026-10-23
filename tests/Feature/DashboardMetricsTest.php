<?php

use App\Models\Equipo;
use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('dashboard aggregates registration totals and revenue by game', function () {
    $user = User::factory()->create(['phone' => '5551234567']);
    $user->assignRole(Role::create(['name' => 'admin', 'guard_name' => 'web']));

    $individualGame = Juego::create([
        'nombre' => 'Individual dashboard game',
        'genero' => 'Test',
        'costo_inscripcion' => 5,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'individual',
    ]);
    $groupGame = Juego::create([
        'nombre' => 'Group dashboard game',
        'genero' => 'Test',
        'costo_inscripcion' => 7,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'grupo',
    ]);

    InscripcionIndividual::create([
        'user_id' => $user->id,
        'id_juego' => $individualGame->id,
        'estado' => 'inscrito',
        'valor_comprobante' => 5,
    ]);

    $team = Equipo::create([
        'user_id' => $user->id,
        'id_juego' => $groupGame->id,
        'nombre_equipo' => 'Dashboard test team',
        'miembros' => '[]',
    ]);

    InscripcionGrupal::create([
        'id_equipo' => $team->id,
        'id_juego' => $groupGame->id,
        'estado' => 'inscrito',
        'valor_comprobante' => 7,
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->where('inscripciones_data.numero_inscripciones_totales', 2)
            ->where('inscripciones_data.ingresos_totales', 12)
            ->has('inscritos_por_juego', 2)
            ->where('inscritos_por_juego.0.total', 1)
            ->where('inscritos_por_juego.1.total', 1));
});
