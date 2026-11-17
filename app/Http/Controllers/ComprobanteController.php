<?php

namespace App\Http\Controllers;

use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ComprobanteController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'comprobante' => 'required|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $file = $data['comprobante'];
        $extension = match ($file->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => abort(422, 'El formato del comprobante no es válido.'),
        };
        $path = $file->storeAs(
            "users/{$request->user()->id}",
            Str::uuid().".{$extension}",
            'local',
        );

        if (! is_string($path)) {
            throw new \RuntimeException('No se pudo guardar el comprobante en el almacenamiento local.');
        }

        return response()->json(['path' => $path], 201);
    }

    public function show(
        string $modalidad,
        int $inscripcion,
    ): BinaryFileResponse {
        $comprobante = match ($modalidad) {
            'individual' => InscripcionIndividual::query()->findOrFail($inscripcion)->comprobante_pago,
            'grupo' => InscripcionGrupal::query()->findOrFail($inscripcion)->comprobante_pago,
            default => abort(404),
        };

        if (! is_string($comprobante) || ! preg_match(
            '/\Ausers\/\d+\/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(?:jpg|png|webp)\z/i',
            $comprobante,
        )) {
            abort(404);
        }

        $disk = Storage::disk('local');

        if (! $disk->exists($comprobante)) {
            abort(404);
        }

        $response = response()->file($disk->path($comprobante), [
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();

        return $response;
    }
}
