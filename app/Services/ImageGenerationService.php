<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    private string $imagePrompt = 'Core Constraint: High-fidelity facial reconstruction of the input figure, preserving facial features and structure.

Composition: Extreme close-up portrait (framing the face and upper shoulders), direct eye-contact with the lens, intimate angle, slightly tilted head, wearing tight top.

Expression & Mood: A magnetic, deeply confident expression; a knowing, alluring gaze; a subtle, inviting smile; a soft but impactful presence.

Lighting & Aesthetic: Soft, warm, intimate studio lighting (e.g., cinematic rim lighting), diffused background with bokeh, sharp focus on the eyes, natural skin texture, clean finish.

Exclusions: At least one hand not visible, no phone or camera in frame, no accessories obscuring the face.';

    public function generateSelfie(string $referenceImageUrl): ?string
    {
        $model = config('services.fal.model');

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(60)->post("https://fal.run/{$model}", [
            'prompt'           => $this->imagePrompt,
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
