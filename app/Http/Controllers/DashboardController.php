<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use App\Models\Juego;
use App\Models\Sponsor;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{

    public function index()
    {
        $individualesPorJuego = InscripcionIndividual::query()
            ->select('id_juego')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(valor_comprobante), 0) as ingresos')
            ->groupBy('id_juego')
            ->get()
            ->keyBy('id_juego');

        $grupalesPorJuego = InscripcionGrupal::query()
            ->select('id_juego')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(valor_comprobante), 0) as ingresos')
            ->groupBy('id_juego')
            ->get()
            ->keyBy('id_juego');

        $juegos = Juego::query()
            ->select(['id', 'nombre', 'modalidad'])
            ->get()
            ->map(function (Juego $juego) use ($individualesPorJuego, $grupalesPorJuego) {
                $individuales = $individualesPorJuego->get($juego->id);
                $grupales = $grupalesPorJuego->get($juego->id);

                return [
                    'nombre' => $juego->nombre,
                    'total' => (int) ($individuales->total ?? 0) + (int) ($grupales->total ?? 0),
                    'individuales' => (int) ($individuales->total ?? 0),
                    'grupales' => (int) ($grupales->total ?? 0),
                    'modalidad' => $juego->modalidad,
                ];
            });

        $totalSponsors = Sponsor::count();
        $totalUsuarios = User::count();

        $ultimasIndividuales = InscripcionIndividual::query()
            ->select(['id', 'user_id', 'id_juego', 'created_at', 'estado_pago'])
            ->with(['user:id,name,phone', 'juego:id,nombre'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'tipo' => 'Individual',
                    'nombre' => $inscripcion->user ? $inscripcion->user->name : '',
                    'telefono' => $inscripcion->user ? $inscripcion->user->phone : '',
                    'juego' => $inscripcion->juego ? $inscripcion->juego->nombre : '',
                    'fecha' => $inscripcion->created_at ? $inscripcion->created_at->format('Y-m-d H:i') : '',
                    'estado_pago' => $inscripcion->estado_pago,
                ];
            });

        $ultimasGrupales = InscripcionGrupal::query()
            ->select(['id', 'id_equipo', 'id_juego', 'created_at', 'estado_pago'])
            ->with([
                'equipo:id,user_id,nombre_equipo',
                'equipo.user:id,name,phone',
                'juego:id,nombre',
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'tipo' => 'Grupal',
                    'nombre' => $inscripcion->equipo && $inscripcion->equipo->user ? $inscripcion->equipo->user->name : '',
                    'telefono' => $inscripcion->equipo && $inscripcion->equipo->user ? $inscripcion->equipo->user->phone : '',
                    'juego' => $inscripcion->juego ? $inscripcion->juego->nombre : '',
                    'equipo' => $inscripcion->equipo ? $inscripcion->equipo->nombre_equipo : '',
                    'fecha' => $inscripcion->created_at ? $inscripcion->created_at->format('Y-m-d H:i') : '',
                    'estado_pago' => $inscripcion->estado_pago,
                ];
            });
        $ultimasInscripciones = collect($ultimasIndividuales)->merge($ultimasGrupales)->sortByDesc('fecha')->take(10)->values();

        $numeroInscripcionesIndividuales = $individualesPorJuego->sum('total');
        $numeroInscripcionesGrupales = $grupalesPorJuego->sum('total');
        $ingresosIndividuales = $individualesPorJuego->sum('ingresos');
        $ingresosGrupales = $grupalesPorJuego->sum('ingresos');

        $inscripciones_data = [
            'numero_inscripciones_grupales' => $numeroInscripcionesGrupales,
            'numero_inscripciones_individuales' => $numeroInscripcionesIndividuales,
            'numero_inscripciones_totales' => $numeroInscripcionesGrupales + $numeroInscripcionesIndividuales,
            'suma_inscripciones_grupales' => $ingresosGrupales,
            'suma_inscripciones_individuales' => $ingresosIndividuales,
            'ingresos_totales' => $ingresosGrupales + $ingresosIndividuales,
        ];

        return Inertia::render('Dashboard', [
            'role' => Auth::check() && Auth::user()->hasRole("admin"),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'inscripciones_data' => $inscripciones_data,
            'inscritos_por_juego' => $juegos,
            'total_sponsors' => $totalSponsors,
            'total_usuarios' => $totalUsuarios,
            'ultimas_inscripciones' => $ultimasInscripciones,
        ]);
    }


    public function list_reportes()
    {
        return Inertia::render('DashboardPage/ParticipantesPage/ListReportes', [
            'role' => Auth::user()->hasRole("admin"),
        ]);
    }

    public function mis_equipos()
    {
        return Inertia::render('DashboardPage/ParticipantesPage/EquiposPage', [
            'user_id' => Auth::user()->id,
            'role' => Auth::user()->hasRole("admin"),
        ]);
    }


    public function get_comprobante($rutaImagen)
    {
        // Verificamos que el archivo exista
        if (Storage::exists('/app/' . $rutaImagen)) {
            // Obtenemos el contenido del archivo
            $file = Storage::get('/app/' . $rutaImagen);

            // Creamos una respuesta con el contenido del archivo
            $response = response($file, 200);

            // Definimos el tipo de contenido en la respuesta
            $response->header("Content-Type", Storage::mimeType($rutaImagen));

            // Retornamos la respuesta
            return $response;
        } else {
            // Si el archivo no existe, retornamos un mensaje de error
            return response()->json(['error' => 'File not found.'], 404);
        }
    }

    public function inscripciones()
    {
        $inscripciones_individuales = InscripcionIndividual::with(['user', 'juego'])->get();
        $inscripciones_grupales = InscripcionGrupal::with(['equipo.user', 'juego'])->get();
        return Inertia::render('DashboardPage/Inscripciones/Incripciones', [
            'inscripciones_individuales' => $inscripciones_individuales,
            'inscripciones_grupales' => $inscripciones_grupales,
            'role' => Auth::user()->hasRole("admin"),
        ]);
    }

    public function actualizarEstado(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'user_id' => 'required|integer',
            'id_juego' => 'required|integer',
            'estado_pago' => 'required|string',
            'modalidad' => 'required|string',
        ]);

        if ($request->modalidad === 'Individual') {
            $inscripcion = InscripcionIndividual::find($request->id);
        } else {
            $inscripcion = InscripcionGrupal::find($request->id);
        }

        if (!$inscripcion) {
            return redirect()->back()->with('error', 'Inscripción no encontrada.');
        }

        $inscripcion->estado_pago = $request->estado_pago;
        $inscripcion->save();

        // Redirigir de vuelta con un mensaje de éxito
        return redirect()->back()->with('success', 'Estado de pago actualizado correctamente.');
    }

}
