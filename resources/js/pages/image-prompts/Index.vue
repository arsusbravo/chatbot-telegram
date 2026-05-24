<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { create, edit, destroy } from '@/routes/image-prompts';
import { index } from '@/routes/image-prompts';
import type { ImagePrompt } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Selfie Prompts', href: index.url() },
        ],
    },
});

defineProps<{
    prompts: ImagePrompt[];
}>();

function deletePrompt(prompt: ImagePrompt) {
    if (confirm(`Delete "${prompt.label}"? This cannot be undone.`)) {
        router.delete(destroy.url(prompt.id));
    }
}
</script>

<template>
    <Head title="Selfie Prompts" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Selfie Prompts</h1>
            <Link
                :href="create.url()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
            >
                + Add Prompt
            </Link>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
            One prompt is selected at random for every selfie generated, across all bots.
        </p>

        <div class="grid gap-4">
            <div
                v-for="prompt in prompts"
                :key="prompt.id"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">{{ prompt.label }}</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 line-clamp-2 font-mono">{{ prompt.prompt }}</p>
                        <p v-if="prompt.negative_prompt" class="text-sm text-red-500 dark:text-red-400 mt-1 line-clamp-1 font-mono">
                            <span class="font-sans text-xs font-medium text-gray-400 mr-1">neg:</span>{{ prompt.negative_prompt }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <Link
                            :href="edit.url(prompt.id)"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 transition"
                        >
                            Edit
                        </Link>
                        <button
                            @click="deletePrompt(prompt)"
                            class="px-3 py-1.5 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="prompts.length === 0" class="text-center py-12 text-gray-400">
                No prompts yet. Add one to enable selfie variety.
            </div>
        </div>
    </div>
</template>
