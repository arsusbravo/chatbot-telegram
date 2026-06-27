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

    private string $nudeImagePrompt = "(nude:1.4), (naked:1.3), (large breasts:1.3), (big tits:1.2),
half body shot, lying on bed, silk sheets, facing camera,
slight arch in back, legs together, knees bent,
(pleasure face:1.4), (ahegao:1.2), mouth open, tongue out, (flushed cheeks:1.2),
(eyes rolling back:1.2), (moaning expression:1.3), heavy breathing, (ecstasy:1.2),
soft warm dim bedroom lighting, natural skin texture,
(perfect anatomy:1.3), (perfect body proportions:1.3), anatomically correct,
RAW photo, (photorealistic:1.4), 8k uhd, masterpiece, best quality, ultra detailed, DSLR, sharp focus, 85mm lens";

    private string $nudeImageNegativePrompt = "(clothes:1.5), (dressed:1.5), (underwear:1.4), (bra:1.4), (panties:1.4), covered, clothed,
(small breasts:1.3), (flat chest:1.3),
(deformed:1.5), (bad anatomy:1.5), (poorly drawn:1.4),
(extra limbs:1.5), (missing limbs:1.4), (extra legs:1.5), (extra arms:1.5),
(mutated hands:1.4), (fused fingers:1.4), (too many fingers:1.4), (malformed limbs:1.5),
(unproportional:1.4), (disproportionate body:1.4), (long torso:1.3), (short legs:1.3),
(blurry:1.3), watermark, text, cartoon, anime, painting, sketch,
(worst quality:1.4), (low quality:1.4)";

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
            'num_inference_steps' => 25,
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
            $body = $response->json();
            Log::info('fal.ai ip-adapter-face-id response', ['body' => $body]);

            $imageUrl = $body['images'][0]['url']
                ?? $body['images'][0]
                ?? $body['image']['url']
                ?? $body['image']
                ?? null;

            return $imageUrl;
        }

        Log::error('fal.ai ip-adapter-face-id error', [
            'status' => $response->status(),
            'body'   => $response->json() ?? $response->body(),
        ]);

        return null;
    }
}
