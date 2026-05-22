<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    public function generateSelfie(string $referenceImageUrl): ?string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(60)->post('https://fal.run/fal-ai/pulid', [
            'prompt'           => 'selfie photo, casual pose, holding phone up, natural lighting, photorealistic, high quality',
            'reference_images' => [['image_url' => $referenceImageUrl]],
            'image_size'       => 'portrait_4_3',
            'num_images'       => 1,
        ]);

        if ($response->successful()) {
            return $response->json('images.0.url');
        }

        Log::error('fal.ai PuLID error', [
            'status' => $response->status(),
            'body'   => $response->json() ?? $response->body(),
        ]);

        return null;
    }
}
