<?php

use App\Models\Equipo;
use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

test('welcome page shows whether the current user is already registered in each game', function () {
    $user = User::factory()->create();

    $individualGame = Juego::create([
        'nombre' => 'Individual test',
        'genero' => 'Test',
        'costo_inscripcion' => 3,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'individual',
    ]);

    $groupGame = Juego::create([
        'nombre' => 'Group test',
        'genero' => 'Test',
        'costo_inscripcion' => 3,
        'fecha_limite_inscripcion' => '2026-12-31',
        'modalidad' => 'grupo',
    ]);

    InscripcionIndividual::create([
        'user_id' => $user->id,
        'id_juego' => $individualGame->id,
        'estado' => 'inscrito',
    ]);

    $team = Equipo::create([
        'user_id' => $user->id,
        'id_juego' => $groupGame->id,
        'nombre_equipo' => 'Test team',
        'miembros' => '[]',
    ]);

    InscripcionGrupal::create([
        'id_equipo' => $team->id,
        'id_juego' => $groupGame->id,
        'estado' => 'inscrito',
    ]);

    $gameQueries = 0;
    DB::listen(function (QueryExecuted $query) use (&$gameQueries) {
        if (str_contains(strtolower($query->sql), 'from "juegos"')) {
            $gameQueries++;
        }
    });

    $this->actingAs($user)
        ->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('juegosIndividual', 1)
            ->has('juegosGrupo', 1)
            ->where('juegosIndividual.0.estaInscrito', true)
            ->where('juegosGrupo.0.estaInscrito', true));

    expect($gameQueries)->toBe(1);
});
