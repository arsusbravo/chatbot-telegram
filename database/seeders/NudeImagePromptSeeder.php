<?php

namespace Database\Seeders;

use App\Models\ImagePrompt;
use Illuminate\Database\Seeder;

class NudeImagePromptSeeder extends Seeder
{
    public function run(): void
    {
        $prompts = [
            [
                'label'           => 'Bedroom',
                'type'            => 'nude',
                'prompt'          => 'lying on bed, silk sheets, soft warm bedroom lighting, facing camera, knees slightly bent, back arched',
                'negative_prompt' => 'standing, outdoor',
            ],
            [
                'label'           => 'Bathroom',
                'type'            => 'nude',
                'prompt'          => 'leaning against bathroom wall, wet skin, steamy bathroom, shower tiles background, water droplets on skin, sensual pose',
                'negative_prompt' => 'fully dry, outdoor',
            ],
            [
                'label'           => 'Poolside',
                'type'            => 'nude',
                'prompt'          => 'poolside, lounge chair, wet glistening skin, tropical setting, golden hour sunlight, lying on back',
                'negative_prompt' => 'indoor, dark lighting',
            ],
            [
                'label'           => 'Hotel Room',
                'type'            => 'nude',
                'prompt'          => 'luxury hotel room, standing near floor-to-ceiling window, city skyline at night, back to camera glancing over shoulder, dramatic backlighting',
                'negative_prompt' => 'daytime, rural',
            ],
            [
                'label'           => 'Sofa',
                'type'            => 'nude',
                'prompt'          => 'lying on sofa, living room, warm lamp light, cushions scattered, one leg raised, arm above head, relaxed seductive pose',
                'negative_prompt' => 'outdoor, bright sunlight',
            ],
            [
                'label'           => 'Rooftop Night',
                'type'            => 'nude',
                'prompt'          => 'rooftop at night, city lights bokeh background, moonlight on skin, leaning on railing, cool night breeze, neon reflections',
                'negative_prompt' => 'daytime, indoor',
            ],
            [
                'label'           => 'Mirror',
                'type'            => 'nude',
                'prompt'          => 'standing in front of full length mirror, dimly lit room, reflection visible, one hand on hip, bedroom vanity, candlelight',
                'negative_prompt' => 'outdoor, bright lighting',
            ],
            [
                'label'           => 'Balcony Sunset',
                'type'            => 'nude',
                'prompt'          => 'balcony at sunset, ocean view in background, golden warm light, leaning on railing, hair blowing in breeze, warm orange glow on skin',
                'negative_prompt' => 'indoor, nighttime, city',
            ],
        ];

        foreach ($prompts as $prompt) {
            ImagePrompt::firstOrCreate(
                ['label' => $prompt['label'], 'type' => 'nude'],
                $prompt
            );
        }
    }
}
