<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageGenerationService
{
    private string $imagePrompt = "mirror selfie, upper body shot framed from chest to head,
low-cut v-neck crop top, visible décolletage, cleavage showing,
leaning slightly forward toward mirror,
3/4 angle, back slightly arched, one hand on hip,
flirtatious smile, direct eye contact,
tight low-cut crop top, high waisted jeans,
bedroom mirror, warm dim lighting, fairy lights,
photorealistic, 8k";

    private string $imageNegativePrompt = "full body, legs visible, feet visible, turtleneck, high neckline,
phone covering chest, arm blocking body,
gray background, studio lighting, cartoon, anime,
deformed, bad anatomy, watermark, low quality";

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
            'guidance_scale'      => 1.5,  // max is 1.5
            'num_inference_steps' => 12,   // max is 12
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
