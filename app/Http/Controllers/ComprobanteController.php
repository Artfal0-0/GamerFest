<?php

namespace App\Http\Controllers;

use App\Models\InscripcionGrupal;
use App\Models\InscripcionIndividual;
use App\Services\SupabaseStorage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ComprobanteController extends Controller
{
    public function createUploadUrl(Request $request, SupabaseStorage $storage): JsonResponse
    {
        $data = $request->validate([
            'content_type' => 'required|in:image/jpeg,image/png,image/webp',
        ]);

        if (! $storage->isConfigured()) {
            return response()->json(['message' => 'El almacenamiento de comprobantes aún no está configurado.'], 503);
        }

        try {
            return response()->json($storage->createSignedUploadUrl(
                (int) $request->user()->id,
                $data['content_type'],
            ));
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::error('Unable to create a Supabase receipt upload URL.', [
                'exception' => $exception::class,
                'status' => $exception instanceof RequestException ? $exception->response->status() : null,
            ]);

            return response()->json(['message' => 'No se pudo preparar la subida del comprobante. Inténtalo de nuevo.'], 502);
        }
    }

    public function show(
        string $modalidad,
        int $inscripcion,
        SupabaseStorage $storage,
    ): JsonResponse {
        $comprobante = match ($modalidad) {
            'individual' => InscripcionIndividual::query()->findOrFail($inscripcion)->comprobante_pago,
            'grupo' => InscripcionGrupal::query()->findOrFail($inscripcion)->comprobante_pago,
            default => abort(404),
        };

        if (! is_string($comprobante) || $comprobante === '') {
            abort(404);
        }

        if (! $storage->isConfigured()) {
            return response()->json(['message' => 'El almacenamiento de comprobantes aún no está configurado.'], 503);
        }

        try {
            return response()->json([
                'url' => $storage->createSignedDownloadUrl($comprobante),
            ]);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            Log::error('Unable to create a Supabase receipt download URL.', [
                'exception' => $exception::class,
                'status' => $exception instanceof RequestException ? $exception->response->status() : null,
            ]);

            return response()->json(['message' => 'No se pudo abrir el comprobante. Inténtalo de nuevo.'], 502);
        }
    }
}
