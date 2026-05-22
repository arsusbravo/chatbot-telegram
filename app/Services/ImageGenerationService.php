<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    private string $imagePrompt = 'Core Subject: A single half-body shot of the beautiful woman from the reference image, smiling, wearing a low-scoop loose grey cotton tank top, relaxed loungewear.
Composition: Casual hand-held smartphone selfie perspective, dynamic asymmetric pose, intentional slight camera tilt, off-center framing, looking over shoulder into the lens, direct eye-contact.
Aesthetics & Vibe: Soft enticing expression, deeply bedroom-alluring gaze, subtle knowing smirk, relaxed and highly intimate posture, confident mood.
Setting & Lighting: Photographed inside a cozy dimly-lit bedroom, sitting on a bed with rumpled sheets, warm low-key ambient lighting, soft realistic depth-of-field blurring the bedroom background, cinematic shadow depth, high-quality rendering.
Exclusions: perfectly level horizon, straight studio alignment, grids, split screens, multiple angles, phone visible in hand.';

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
