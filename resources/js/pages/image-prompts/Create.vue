<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { index, store } from '@/routes/image-prompts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Selfie Prompts', href: index.url() },
            { title: 'Add Prompt', href: '#' },
        ],
    },
});

defineProps<{
    opening_prompt: string;
    closing_prompt: string;
    negative_prefix: string;
}>();

const form = useForm({
    label: '',
    prompt: '',
    negative_prompt: '',
});

function submit() {
    form.post(store.url());
}
</script>

<template>
    <Head title="Add Selfie Prompt" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 max-w-2xl">
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                <input
                    v-model="form.label"
                    type="text"
                    placeholder="e.g. Indoor selfie"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.label" class="text-red-500 text-sm mt-1">{{ form.errors.label }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prompt</label>
                <pre class="w-full rounded-lg rounded-b-none border border-b-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-mono px-3 py-2 whitespace-pre-wrap">{{ opening_prompt }}</pre>
                <textarea
                    v-model="form.prompt"
                    rows="6"
                    placeholder="mirror selfie, upper body, leaning forward,"
                    class="w-full rounded-none border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-mono"
                />
                <pre class="w-full rounded-lg rounded-t-none border border-t-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-mono px-3 py-2 whitespace-pre-wrap">{{ closing_prompt }}</pre>
                <p v-if="form.errors.prompt" class="text-red-500 text-sm mt-1">{{ form.errors.prompt }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Negative Prompt <span class="font-normal text-gray-400">(optional)</span></label>
                <pre class="w-full rounded-lg rounded-b-none border border-b-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 text-xs font-mono px-3 py-2 whitespace-pre-wrap">{{ negative_prefix }}</pre>
                <textarea
                    v-model="form.negative_prompt"
                    rows="3"
                    placeholder="full body, legs visible, watermark,"
                    class="w-full rounded-lg rounded-t-none border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-mono"
                />
                <p v-if="form.errors.negative_prompt" class="text-red-500 text-sm mt-1">{{ form.errors.negative_prompt }}</p>
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium disabled:opacity-50"
            >
                Save Prompt
            </button>
        </div>
    </div>
</template>
