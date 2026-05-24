<?php

namespace App\Http\Controllers;

use App\Models\ImagePrompt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ImagePromptController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('image-prompts/Index', [
            'prompts' => ImagePrompt::orderByDesc('id')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('image-prompts/Create', $this->promptContext());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label'           => 'required|string|max:100',
            'prompt'          => 'required|string',
            'negative_prompt' => 'nullable|string',
        ]);

        ImagePrompt::create($validated);

        return redirect()->route('image-prompts.index');
    }

    public function edit(ImagePrompt $imagePrompt): Response
    {
        return Inertia::render('image-prompts/Edit', [
            'prompt' => $imagePrompt,
            ...$this->promptContext(),
        ]);
    }

    public function update(Request $request, ImagePrompt $imagePrompt): RedirectResponse
    {
        $validated = $request->validate([
            'label'           => 'required|string|max:100',
            'prompt'          => 'required|string',
            'negative_prompt' => 'nullable|string',
        ]);

        $imagePrompt->update($validated);

        return redirect()->route('image-prompts.index');
    }

    private function promptContext(): array
    {
        return [
            'opening_prompt'   => __('messages.selfie_default_prompt.main.opening'),
            'closing_prompt'   => __('messages.selfie_default_prompt.main.closing'),
            'negative_prefix'  => __('messages.selfie_default_prompt.negative'),
        ];
    }

    public function destroy(ImagePrompt $imagePrompt): RedirectResponse
    {
        $imagePrompt->delete();

        return redirect()->route('image-prompts.index');
    }
}
