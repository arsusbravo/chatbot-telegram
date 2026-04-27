<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { index, show } from '@/routes/telegram-users';
import type { PaginatedData, TelegramUser } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Telegram Users', href: index.url() },
        ],
    },
});

defineProps<{
    users: PaginatedData<TelegramUser & { messages_count: number }>;
}>();
</script>

<template>
    <Head title="Telegram Users" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Telegram ID</th>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Free Messages</th>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Paid Credits</th>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Messages</th>
                        <th class="px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Joined</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-gray-900 dark:text-white font-medium">
                            {{ user.first_name || '—' }}
                            <span v-if="user.username" class="text-gray-400 text-xs ml-1">@{{ user.username }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ user.telegram_id }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ user.free_messages_left }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ user.paid_credits }}</td>
                        <td class="px-4 py-3 text-gray-900 dark:text-white">{{ user.messages_count }}</td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                        <td class="px-4 py-3">
                            <Link
                                :href="show.url(user.id)"
                                class="text-blue-600 hover:underline text-sm"
                            >
                                View
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="users.data.length === 0" class="p-8 text-center text-gray-400">
                No users yet
            </div>
        </div>

        <div v-if="users.last_page > 1" class="flex items-center gap-1">
            <template v-for="link in users.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="px-3 py-1.5 text-sm rounded-lg transition"
                    :class="link.active
                        ? 'bg-blue-600 text-white'
                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
                    v-html="link.label"
                />
                <span v-else class="px-3 py-1.5 text-sm text-gray-300 dark:text-gray-600" v-html="link.label" />
            </template>
        </div>
    </div>
</template>