<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { create, edit, update, destroy } from '@/routes/bots';
import type { Bot } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Bots', href: '/bots' },
        ],
    },
});

defineProps<{
    bots: (Bot & { messages_count: number })[];
}>();

function toggleActive(bot: Bot) {
    router.put(update.url(bot.id), {
        ...bot,
        is_active: !bot.is_active,
    }, { preserveScroll: true });
}

function deleteBot(bot: Bot) {
    if (confirm(`Delete ${bot.name}? This cannot be undone.`)) {
        router.delete(destroy.url(bot.id));
    }
}

function copyLink(username: string) {
    const url = `https://t.me/${username}`;

    if (window.isSecureContext && navigator.clipboard) {
        navigator.clipboard.writeText(url);
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
}
</script>

<template>
    <Head title="Bots" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bots</h1>
            <Link
                :href="create.url()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium"
            >
                + Add Bot
            </Link>
        </div>

        <div class="grid gap-4">
            <div
                v-for="bot in bots"
                :key="bot.id"
                class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ bot.name }}</h2>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-medium"
                                :class="bot.is_active
                                    ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                            >
                                {{ bot.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">@{{ bot.telegram_username }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <a
                                :href="`https://t.me/${bot.telegram_username}`"
                                target="_blank"
                                class="text-sm text-blue-500 hover:underline"
                            >
                                t.me/{{ bot.telegram_username }}
                            </a>
                            <button
                                @click="copyLink(bot.telegram_username)"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition"
                                title="Copy link"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ bot.messages_count }} messages</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 line-clamp-2">{{ bot.system_prompt }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            @click="toggleActive(bot)"
                            class="px-3 py-1.5 text-sm rounded-lg border transition"
                            :class="bot.is_active
                                ? 'border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700'
                                : 'border-emerald-300 text-emerald-600 hover:bg-emerald-50 dark:border-emerald-700 dark:text-emerald-400'"
                        >
                            {{ bot.is_active ? 'Disable' : 'Enable' }}
                        </button>
                        <Link
                            :href="edit.url(bot.id)"
                            class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700 transition"
                        >
                            Edit
                        </Link>
                        <button
                            @click="deleteBot(bot)"
                            class="px-3 py-1.5 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900 transition"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="bots.length === 0" class="text-center py-12 text-gray-400">
                No bots yet. Create one to get started.
            </div>
        </div>
    </div>
</template>