<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InscripcionIndividual;
use App\Models\InscripcionGrupal;
use App\Models\Equipo;
use App\Models\Juego;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;

class InscripcionIndividualController extends Controller
{
    public function index()
    {
        try {
            $inscripciones = InscripcionIndividual::all();
            return response()->json($inscripciones);
        } catch (\Exception $e) {
            return response()->json(['message' => "Error al obtener las inscripciones: " . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'id_juego' => 'required|integer',
                'estado' => 'required|string',
                'comprobante_pago' => 'required|string',
                'nro_comprobante' => 'required|string',
            ]);

            $inscripcion = InscripcionIndividual::create($data);

            return response()->json([
                'data' => $inscripcion,
                'message' => 'Inscripcion creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => "Error al crear la inscripción individual: " . $e->getMessage()], 500);
        }
    }

    public function show(InscripcionIndividual $inscripcionIndividual)
    {
        try {
            return response()->json($inscripcionIndividual);
        } catch (\Exception $e) {
            return response()->json(['message' => "Error al obtener la inscripcion: " . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, InscripcionIndividual $inscripcionIndividual)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'id_juego' => 'required|integer',
                'estado' => 'required|string',
                'comprobante_pago' => 'required|string',
                'nro_comprobante' => 'required|string',
            ]);

            $inscripcionIndividual->update($data);

            return response()->json([
                'data' => $inscripcionIndividual,
                'message' => 'Inscripcion actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => "Error al actualizar la inscripcion: " . $e->getMessage()], 500);
        }
    }

    public function destroy(InscripcionIndividual $inscripcionIndividual)
    {
        try {
            $inscripcionIndividual->delete();

            return response()->json([
                'message' => 'Inscripcion eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => "Error al eliminar la inscripcion: " . $e->getMessage()], 500);
        }
    }

    public function guardar_all_inscripciones(Request $request)
    {
        $data = $request->validate([
            'juegos' => 'required|string|max:10000',
            'comprobante_pago' => 'nullable|string|max:512',
            'nro_comprobante' => 'nullable|string|max:100',
            'valor_comprobante' => 'required|numeric|min:0',
        ]);

        try {
            $juegos = json_decode($data['juegos'], true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'juegos' => 'La lista de juegos no tiene un formato válido.',
            ]);
        }

        if (! is_array($juegos) || count($juegos) < 1 || count($juegos) > 20) {
            throw ValidationException::withMessages([
                'juegos' => 'Selecciona entre 1 y 20 juegos.',
            ]);
        }

        $ids = array_map(
            static fn ($juego) => is_array($juego) ? filter_var($juego['id'] ?? null, FILTER_VALIDATE_INT) : false,
            $juegos,
        );

        if (in_array(false, $ids, true) || count(array_unique($ids)) !== count($ids)) {
            throw ValidationException::withMessages([
                'juegos' => 'La selección de juegos no es válida.',
            ]);
        }

        $juegosDisponibles = Juego::query()->whereIn('id', $ids)->get()->keyBy('id');

        if ($juegosDisponibles->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'juegos' => 'Uno o más juegos seleccionados ya no están disponibles.',
            ]);
        }

        $total = (float) $juegosDisponibles->sum('costo_inscripcion');
        $comprobante = $data['comprobante_pago'] ?? null;
        $userId = (int) $request->user()->id;

        if (abs($total - (float) $data['valor_comprobante']) > 0.01) {
            throw ValidationException::withMessages([
                'valor_comprobante' => 'El total enviado no coincide con el precio actual de los juegos.',
            ]);
        }

        if ($total > 0 && (! is_string($comprobante) || $comprobante === '')) {
            throw ValidationException::withMessages([
                'comprobante_pago' => 'Debes subir el comprobante de pago.',
            ]);
        }

        if ($comprobante !== null && ! preg_match(
            '/\Ausers\/' . $userId . '\/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(?:jpg|png|webp)\z/i',
            $comprobante,
        )) {
            throw ValidationException::withMessages([
                'comprobante_pago' => 'La ruta del comprobante no es válida.',
            ]);
        }

        if ($total > 0 && empty($data['nro_comprobante'])) {
            throw ValidationException::withMessages([
                'nro_comprobante' => 'Indica el número de comprobante.',
            ]);
        }

        DB::transaction(function () use ($juegosDisponibles, $userId, $comprobante, $data): void {
            foreach ($juegosDisponibles as $juego) {
                if ($juego->modalidad === 'grupo') {
                    $equipo = Equipo::query()
                        ->where('user_id', $userId)
                        ->where('id_juego', $juego->id)
                        ->first();

                    if (! $equipo) {
                        throw ValidationException::withMessages([
                            'juegos' => "Necesitas crear un equipo para {$juego->nombre} antes de inscribirte.",
                        ]);
                    }

                    $inscripcion = InscripcionGrupal::firstOrNew([
                        'id_equipo' => $equipo->id,
                        'id_juego' => $juego->id,
                    ]);
                } else {
                    $inscripcion = InscripcionIndividual::firstOrNew([
                        'user_id' => $userId,
                        'id_juego' => $juego->id,
                    ]);
                }

                if (! $inscripcion->exists) {
                    $inscripcion->estado = 'inscrito';
                    $inscripcion->comprobante_pago = $comprobante;
                }

                $inscripcion->nro_comprobante = $data['nro_comprobante'] ?? null;
                $inscripcion->valor_comprobante = $juego->costo_inscripcion;
                $inscripcion->save();
            }
        });

        return response()->json([
            'message' => 'Inscripción creada exitosamente.',
        ], 201);
    }

    public function get_inscripciones_by_game(Request $request)
    {
        try {
            $idJuego = $request->input('game');

            if ($idJuego) {
                $juego = Juego::find($idJuego);
                if (!$juego) {
                    return response()->json(['error' => 'Juego no encontrado'], 404);
                }

                // Obtiene las inscripciones individuales y grupales basadas en el juego
                $inscripcionesIndividuales = $juego->inscripcionesIndividuales()
                    ->with([
                        'user' => function ($query) {
                            $query->select('id', 'name', 'phone');
                        }
                    ])
                    ->select('id', 'user_id', 'estado_pago', 'id_juego', 'nro_comprobante', 'comprobante_pago', 'valor_comprobante') // incluye nro_comprobante y valor_comprobante
                    ->paginate(100)
                    ->map(function ($inscripcion) use ($juego) {
                        $inscripcion->nombre_juego = $juego->nombre; // incluye nombre del juego
                        return $inscripcion;
                    });

                $inscripcionesGrupales = $juego->inscripcionesGrupales()
                    ->with([
                        'equipo' => function ($query) {
                            $query->select('id', 'nombre_equipo', 'miembros', 'user_id')
                                ->with([
                                    'user' => function ($query) {
                                        $query->select('id', 'name', 'phone');
                                    }
                                ]);
                        }
                    ])
                    ->select('id', 'id_equipo', 'estado_pago', 'id_juego', 'nro_comprobante', 'comprobante_pago', 'valor_comprobante') // incluye nro_comprobante y valor_comprobante
                    ->paginate(10)
                    ->map(function ($inscripcion) use ($juego) {
                        $inscripcion->nombre_juego = $juego->nombre; // incluye nombre del juego
                        // incluye los datos del equipo y el usuario
                        if ($inscripcion->equipo) {
                            $inscripcion->nombre_equipo = $inscripcion->equipo->nombre_equipo;
                            $inscripcion->miembros = $inscripcion->equipo->miembros;
                            if ($inscripcion->equipo->user) {
                                $inscripcion->nombre_usuario = $inscripcion->equipo->user->nombre;
                            }
                        }
                        return $inscripcion;
                    });

                // puedes personalizar los datos que envías aquí, según tus necesidades
                return response()->json([
                    'inscripcionesIndividuales' => $inscripcionesIndividuales,
                    'inscripcionesGrupales' => $inscripcionesGrupales
                ]);
            }

            return response()->json(['error' => 'ID de juego no especificado'], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ha ocurrido un error: ' . $e->getMessage()], 500);
        }
    }

    public function get_mis_inscripciones(Request $request): Response
    {
        // Obteniendo el usuario autenticado
        $user = Auth::guard('web')->user();

        // Verifica que $user sea instancia de App\Models\User antes de llamar a hasRole
        $role = false;
        if ($user instanceof \App\Models\User) {
            $role = $user->hasRole("admin");
        }

        // Recuperando las inscripciones individuales
        $inscripcionesIndividuales = InscripcionIndividual::where('user_id', $user->id)
            ->with('juego')
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'tipo' => 'Individual',
                    'juego_nombre' => $inscripcion->juego->nombre,
                    'img_id' => $inscripcion->juego->img_id,
                    'estado_pago' => $inscripcion->estado_pago,
                ];
            });

        // Recuperando los equipos del usuario
        $equipos = Equipo::where('user_id', $user->id)->get();

        // Recuperando las inscripciones grupales
        $inscripcionesGrupales = collect();
        foreach ($equipos as $equipo) {
            $inscripcionesDelEquipo = InscripcionGrupal::where('id_equipo', $equipo->id)
                ->with('juego')
                ->get()
                ->map(function ($inscripcion) use ($equipo) {
                    return [
                        'tipo' => 'Grupal',
                        'juego_nombre' => $inscripcion->juego->nombre,
                        'img_id' => $inscripcion->juego->img_id,
                        'equipo_nombre' => $equipo->nombre_equipo,
                        'equipo_id' => $equipo->id,
                        'estado_pago' => $inscripcion->estado_pago,
                    ];
                });
            $inscripcionesGrupales = $inscripcionesGrupales->concat($inscripcionesDelEquipo);
        }

        // Fusionando las inscripciones
        $inscripciones = $inscripcionesIndividuales->concat($inscripcionesGrupales);

        // Enviando a la vista
        return Inertia::render('DashboardPage/Inscripciones/MisInscripciones', [
            'role' => $role,
            'inscripciones' => $inscripciones,
        ]);
    }
}
