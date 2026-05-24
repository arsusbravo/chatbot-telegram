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

    public function generateSelfie(string $referenceImageUrl, ?string $imagePrompt = null, ?string $negativePrompt = null): ?string
    {
        $model = config('services.fal.model');

        $openingPrompt         = __('messages.selfie_default_prompt.main.opening');
        $closingPrompt         = __('messages.selfie_default_prompt.main.closing');
        $negativePrefix        = __('messages.selfie_default_prompt.negative');

        // Build final prompts
        $finalPrompt = $imagePrompt
            ? $openingPrompt . $imagePrompt . $closingPrompt
            : $this->imagePrompt;

        // Always use the lang file prefix as the negative base.
        // Append the row's extra terms if provided. Fall back to hardcoded default
        // only when the lang key itself is missing (not yet deployed).
        $finalNegativePrompt = is_string($negativePrefix) && $negativePrefix !== 'messages.selfie_default_prompt.negative'
            ? $negativePrefix . ($negativePrompt ?? '')
            : ($negativePrompt ?? $this->imageNegativePrompt);

        Log::info('fal.ai selfie request', [
            'model'           => $model,
            'prompt'          => $finalPrompt,
            'negative_prompt' => $finalNegativePrompt,
            'reference_image' => $referenceImageUrl,
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(180)->post("https://fal.run/{$model}", [
            'prompt'              => $finalPrompt,
            'negative_prompt'     => $finalNegativePrompt,
            'reference_image_url' => $referenceImageUrl,
            'id_weight'           => 1.0,
            'guidance_scale'      => 4.0,
            'num_inference_steps' => 20,
            'true_cfg'            => 1,
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
