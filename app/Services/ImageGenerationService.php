<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    private string $imagePrompt = 'Core Subject: A single half-body shot of the woman, Composition: casual smartphone selfie perspective, dynamic casual pose, off-center framing, looking over shoulder into the lens, eye-contact, Aesthetics & Vibe: highly inviting and confident expression, alluring gaze, subtle smirk, natural relaxed posture, Setting & Lighting: atmospheric depth, soft realistic shadows, high-quality rendering, Exclusions: no grids, no split screens, no multiple angles, no phone visible in hand.';

    public function generateSelfie(string $referenceImageUrl): ?string
    {
        $model = config('services.fal.model');

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(180)->post("https://fal.run/{$model}", [
            'prompt'           => $this->imagePrompt,
            'reference_images' => [['image_url' => $referenceImageUrl]],
            'id_scale'         => 1.5,
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
