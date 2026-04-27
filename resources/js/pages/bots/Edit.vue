<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { index, update } from '@/routes/bots';
import type { Bot } from '@/types';

const props = defineProps<{
    bot: Bot;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Bots', href: index.url() },
            { title: 'Edit', href: '#' },
        ],
    },
});

const form = useForm({
    name: props.bot.name,
    telegram_token: props.bot.telegram_token,
    telegram_username: props.bot.telegram_username,
    system_prompt: props.bot.system_prompt,
    is_active: props.bot.is_active,
});

function submit() {
    form.put(update.url(props.bot.id));
}
</script>

<template>
    <Head :title="`Edit ${bot.name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4 max-w-2xl">
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-6 space-y-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                <input
                    v-model="form.name"
                    type="text"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram Token</label>
                <input
                    v-model="form.telegram_token"
                    type="text"
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
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.system_prompt" class="text-red-500 text-sm mt-1">{{ form.errors.system_prompt }}</p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
            </div>

            <button
                @click="submit"
                :disabled="form.processing"
                class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium disabled:opacity-50"
            >
                Save Changes
            </button>
        </div>
    </div>
</template>