<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, store } from '@/routes/bots';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Bots', href: index.url() },
            { title: 'Add Bot', href: '#' },
        ],
    },
});

const form = useForm({
    name: '',
    telegram_token: '',
    telegram_username: '',
    system_prompt: '',
    image_prompt: '',
    negative_prompt: '',
});

function submit() {
    form.post(store.url());
}
</script>

<template>
    <Head title="Add Bot" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 max-w-2xl">
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Sara"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram Token</label>
                <input
                    v-model="form.telegram_token"
                    type="text"
                    placeholder="123456789:AAHxyz..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                />
                <p v-if="form.errors.telegram_token" class="text-red-500 text-sm mt-1">{{ form.errors.telegram_token }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram Username</label>
                <div class="flex items-center">
                    <span class="text-gray-400 mr-1">@</span>
                    <input
                        v-model="form.telegram_username"
                        type="text"
                        placeholder="sara_ai_gf_bot"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <p v-if="form.errors.telegram_username" class="text-red-500 text-sm mt-1">{{ form.errors.telegram_username }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System Prompt (Persona)</label>
                <textarea
                    v-model="form.system_prompt"
                    rows="5"
                    placeholder="Kamu adalah Sara, pacar virtual yang manis..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.system_prompt" class="text-red-500 text-sm mt-1">{{ form.errors.system_prompt }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image Prompt</label>
                <textarea
                    v-model="form.image_prompt"
                    rows="5"
                    placeholder="Leave blank to use the default prompt..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <p class="text-xs text-gray-400 mt-1">Describes what the selfie should look like. Leave blank to use the system default.</p>
                <p v-if="form.errors.image_prompt" class="text-red-500 text-sm mt-1">{{ form.errors.image_prompt }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Negative Prompt</label>
                <textarea
                    v-model="form.negative_prompt"
                    rows="3"
                    placeholder="Leave blank to use the default negative prompt..."
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                />
                <p class="text-xs text-gray-400 mt-1">Things to exclude from the image. Leave blank to use the system default.</p>
                <p v-if="form.errors.negative_prompt" class="text-red-500 text-sm mt-1">{{ form.errors.negative_prompt }}</p>
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium disabled:opacity-50"
            >
                Create Bot & Register Webhook
            </button>
        </div>
    </div>
</template>