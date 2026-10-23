<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AdModel;
use App\Models\Juego;
use App\Models\Sponsor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function index(): Response
    {
        $userId = Auth::id();
        $juegos = Juego::query()
            ->select([
                'id',
                'nombre',
                'costo_inscripcion',
                'img_id',
                'reglamentos_pdf',
                'active',
                'modalidad',
            ])
            ->withExists([
                'inscripcionesIndividuales as tiene_inscripcion_individual' => fn (Builder $query) => $query
                    ->where('user_id', $userId),
                'inscripcionesGrupales as tiene_inscripcion_grupal' => fn (Builder $query) => $query
                    ->whereHas('equipo', fn (Builder $query) => $query->where('user_id', $userId)),
            ])
            ->get();

        $toPageData = fn (Juego $juego, bool $esGrupal): array => [
            'id' => $juego->id,
            'nombre' => $juego->nombre,
            'costo_inscripcion' => $juego->costo_inscripcion,
            'img_id' => $juego->img_id,
            'reglamentos_pdf' => $juego->reglamentos_pdf,
            'estaInscrito' => (bool) $juego->{$esGrupal
                ? 'tiene_inscripcion_grupal'
                : 'tiene_inscripcion_individual'},
            'active' => $juego->active,
        ];

        $juegosGrupo = $juegos
            ->where('modalidad', 'grupo')
            ->values()
            ->map(fn (Juego $juego) => $toPageData($juego, true));

        $juegosIndividual = $juegos
            ->where('modalidad', 'individual')
            ->values()
            ->map(fn (Juego $juego) => $toPageData($juego, false));

        $sponsors = Sponsor::all();

        $ads = AdModel::all();

        return Inertia::render('Welcome', [
            'role' => Auth::check() && Auth::user()->hasRole("admin"),
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'juegosGrupo' => $juegosGrupo,
            'juegosIndividual' => $juegosIndividual,
            'sponsors' => $sponsors,
            'ads' => $ads,
        ]);
    }

}
