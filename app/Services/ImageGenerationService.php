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

    private string $nudeImagePrompt = "nude, naked, bare skin, no clothing,
boudoir photo, lying on bed, soft warm lighting,
seductive expression, direct eye contact,
photorealistic, 8k, high detail";

    private string $nudeImageNegativePrompt = "clothes, dressed, underwear, bra, panties, bikini, covered,
cartoon, anime, deformed, bad anatomy, extra limbs,
watermark, low quality, blurry";

    public function generateSelfie(string $referenceImageUrl, ?string $imagePrompt = null, ?string $negativePrompt = null, string $type = 'selfie'): ?string
    {
        if ($type === 'nude') {
            return $this->generateNude($referenceImageUrl, $imagePrompt, $negativePrompt);
        }

        $model = config('services.fal.model');

        $openingPrompt  = __('messages.selfie_default_prompt.main.opening');
        $closingPrompt  = __('messages.selfie_default_prompt.main.closing');
        $negativePrefix = __('messages.selfie_default_prompt.negative');
        $langLoaded     = $openingPrompt !== 'messages.selfie_default_prompt.main.opening';

        $finalPrompt = ($imagePrompt && $langLoaded)
            ? $openingPrompt . $imagePrompt . $closingPrompt
            : ($imagePrompt ?: $this->imagePrompt);

        $finalNegativePrompt = $langLoaded
            ? $negativePrefix . ($negativePrompt ?? '')
            : ($negativePrompt ?? $this->imageNegativePrompt);

        $payload = [
            'prompt'                => $finalPrompt,
            'negative_prompt'       => $finalNegativePrompt,
            'reference_image_url'   => $referenceImageUrl,
            'id_weight'             => 0.6,
            'guidance_scale'        => 5.5,
            'num_inference_steps'   => 20,
            'true_cfg'              => 1,
            'image_size'            => 'portrait_4_3',
            'enable_safety_checker' => false,
            'num_images'            => 1,
            'seed'                  => random_int(1, 2147483647),
        ];

        Log::info('fal.ai flux-pulid request', [
            'model'           => $model,
            'prompt'          => $finalPrompt,
            'negative_prompt' => $finalNegativePrompt,
            'reference_url'   => $referenceImageUrl,
            'seed'            => $payload['seed'],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(180)->post("https://fal.run/{$model}", $payload);

        if ($response->successful()) {
            $imageUrl = $response->json('images.0.url');
            Log::info('fal.ai flux-pulid response OK', ['image_url' => $imageUrl]);
            return $imageUrl;
        }

        Log::error('fal.ai flux-pulid error', [
            'status' => $response->status(),
            'body'   => $response->json() ?? $response->body(),
        ]);

        return null;
    }

    private function generateNude(string $referenceImageUrl, ?string $imagePrompt = null, ?string $negativePrompt = null): ?string
    {
        $openingPrompt  = __('messages.nude_default_prompt.main.opening');
        $closingPrompt  = __('messages.nude_default_prompt.main.closing');
        $negativePrefix = __('messages.nude_default_prompt.negative');
        $langLoaded     = $openingPrompt !== 'messages.nude_default_prompt.main.opening';

        $finalPrompt = ($imagePrompt && $langLoaded)
            ? $openingPrompt . $imagePrompt . $closingPrompt
            : ($imagePrompt ?: $this->nudeImagePrompt);

        $finalNegative = $langLoaded
            ? $negativePrefix . ($negativePrompt ?? '')
            : ($negativePrompt ?? $this->nudeImageNegativePrompt);

        $payload = [
            'face_image_url'      => $referenceImageUrl,
            'prompt'              => $finalPrompt,
            'negative_prompt'     => $finalNegative,
            'model_type'          => '1_5-v2-plus',
            'num_samples'         => 1,
            'num_inference_steps' => 50,
            'guidance_scale'      => 7.5,
            'width'               => 512,
            'height'              => 768,
            'seed'                => random_int(1, 2147483647),
        ];

        Log::info('fal.ai ip-adapter-face-id request', [
            'prompt'          => $finalPrompt,
            'negative_prompt' => $finalNegative,
            'reference_url'   => $referenceImageUrl,
            'seed'            => $payload['seed'],
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.key'),
        ])->timeout(180)->post('https://fal.run/fal-ai/ip-adapter-face-id', $payload);

        if ($response->successful()) {
            $imageUrl = $response->json('images.0.url');
            Log::info('fal.ai ip-adapter-face-id response OK', ['image_url' => $imageUrl]);
            return $imageUrl;
        }

        Log::error('fal.ai ip-adapter-face-id error', [
            'status' => $response->status(),
            'body'   => $response->json() ?? $response->body(),
        ]);

        return null;
    }
}
