<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SupabaseStorage
{
    public function isConfigured(): bool
    {
        return filled(config('services.supabase.url'))
            && filled(config('services.supabase.secret_key'))
            && filled(config('services.supabase.storage_bucket'));
    }

    /**
     * @return array{path: string, url: string}
     */
    public function createSignedUploadUrl(int $userId, string $contentType): array
    {
        $extension = match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Unsupported receipt image type.'),
        };

        $path = "users/{$userId}/".Str::uuid().".{$extension}";
        $response = $this->request()
            ->post($this->objectUrl('object/upload/sign', $path), []);
        $response->throw();

        $signedPath = $response->json('url');

        if (! is_string($signedPath) || ! str_starts_with($signedPath, '/')) {
            throw new RuntimeException('Supabase returned an invalid signed upload URL.');
        }

        return [
            'path' => $path,
            'url' => $this->storageBaseUrl().$signedPath,
        ];
    }

    public function createSignedDownloadUrl(string $path): string
    {
        if (! preg_match('/\Ausers\/\d+\/[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(?:jpg|png|webp)\z/i', $path)) {
            throw new RuntimeException('Invalid receipt storage path.');
        }

        $response = $this->request()
            ->post($this->objectUrl('object/sign', $path), [
                'expiresIn' => (int) config('services.supabase.signed_url_ttl', 300),
            ]);
        $response->throw();

        $signedPath = $response->json('signedURL');

        if (! is_string($signedPath) || ! str_starts_with($signedPath, '/')) {
            throw new RuntimeException('Supabase returned an invalid signed download URL.');
        }

        return $this->storageBaseUrl().$signedPath;
    }

    private function request(): PendingRequest
    {
        $secretKey = config('services.supabase.secret_key');

        if (! is_string($secretKey) || $secretKey === '') {
            throw new RuntimeException('Supabase Storage is not configured.');
        }

        $request = Http::acceptJson()
            ->withHeader('apikey', $secretKey)
            ->withOptions([
                'verify' => filled(config('services.supabase.ca_bundle'))
                    ? config('services.supabase.ca_bundle')
                    : true,
            ])
            ->timeout(15);

        if (! str_starts_with($secretKey, 'sb_secret_')) {
            $request->withToken($secretKey);
        }

        return $request;
    }

    private function objectUrl(string $operation, string $path): string
    {
        $bucket = config('services.supabase.storage_bucket');

        if (! is_string($bucket) || $bucket === '') {
            throw new RuntimeException('Supabase Storage bucket is not configured.');
        }

        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        return $this->storageBaseUrl()
            .'/'
            .$operation
            .'/'
            .rawurlencode($bucket)
            .'/'
            .$encodedPath;
    }

    private function storageBaseUrl(): string
    {
        return rtrim(config('services.supabase.url'), '/').'/storage/v1';
    }
}
