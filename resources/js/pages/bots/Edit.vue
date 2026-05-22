<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, update, syncAvatar } from '@/routes/bots';
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
    image_prompt: props.bot.image_prompt ?? '',
    negative_prompt: props.bot.negative_prompt ?? '',
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

            <div class="flex items-center gap-2">
                <input
                    v-model="form.is_active"
                    type="checkbox"
                    id="is_active"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                />
                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Active</label>
            </div>

            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Selfie Avatar (from Telegram profile photo)
                </label>
                <div class="flex items-center gap-4">
                    <img
                        v-if="bot.avatar_url"
                        :src="bot.avatar_url"
                        class="h-16 w-16 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600"
                        alt="Bot avatar"
                    />
                    <div
                        v-else
                        class="h-16 w-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 text-xs text-center leading-tight p-1"
                    >
                        No avatar
                    </div>
                    <div class="space-y-1">
                        <Link
                            :href="syncAvatar(bot.id).url"
                            method="post"
                            as="button"
                            class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-sm rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition text-gray-700 dark:text-gray-300"
                        >
                            Sync from Telegram
                        </Link>
                        <p class="text-xs text-gray-400">
                            Set the bot's profile photo in BotFather, then click Sync.
                        </p>
                    </div>
                </div>
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