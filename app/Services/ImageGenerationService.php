<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    private string $imagePrompt = "Indonesian woman taking a selfie, one arm raised holding camera slightly above eye level, body turned at a 3/4 angle, not facing straight, leaning slightly forward, tilted head, soft flirtatious smile with eye contact, big expressive brown eyes, long straight black hair falling naturally, warm golden-tan skin, low-cut top, curvy voluptuous figure, large bust visible, cozy bedroom interior background, warm soft lighting, bedside lamp light, shallow depth of field, bokeh background, selfie perspective, portrait orientation, photorealistic, 8k, natural makeup";

    private string $imageNegativePrompt = "both arms visible, straight-on pose, studio background, outdoor, sunlight, blurry face, deformed hands, extra fingers, bad anatomy, watermark, logo, text, low quality, cartoon, anime, ugly, stiff pose, formal clothing, busy background, multiple people, no eye contact, sad expression, closed eyes, short hair, light skin, masculine features";

    public function generateSelfie(string $referenceImageUrl): ?string
    {
        $model = config('services.fal.model');

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(180)->post("https://fal.run/{$model}", [
            'prompt'              => $this->imagePrompt,
            'negative_prompt'     => $this->imageNegativePrompt,
            'reference_images'    => [['image_url' => $referenceImageUrl]],
            'id_scale'            => 0.8,
            'guidance_scale'      => 7.5,
            'num_inference_steps' => 28,
            'image_size'          => 'portrait_4_3',
            'num_images'          => 1,
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
