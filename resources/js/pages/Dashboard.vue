<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { DashboardStats, Message } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
        ],
    },
});

defineProps<{
    stats: DashboardStats;
    recentMessages: (Message & { telegram_user: { first_name: string; username: string }; bot: { name: string } })[];
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <!-- Stats Grid -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-5">
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Bots</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_bots }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Active Bots</p>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ stats.active_bots }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Users</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_users }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Messages</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.total_messages }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Messages Today</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">{{ stats.messages_today }}</p>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div class="p-5 border-b border-sidebar-border/70 dark:border-sidebar-border">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Messages</h2>
            </div>
            <div class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                <div
                    v-for="msg in recentMessages"
                    :key="msg.id"
                    class="p-4 flex items-start gap-3"
                >
                    <span
                        class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold"
                        :class="msg.role === 'user'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                            : 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300'"
                    >
                        {{ msg.role === 'user' ? '👤' : '🤖' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ msg.role === 'user' ? (msg.telegram_user?.first_name || 'User') : msg.bot?.name }}
                            </span>
                            <span>&middot;</span>
                            <span>{{ new Date(msg.created_at).toLocaleString() }}</span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mt-0.5 truncate">{{ msg.content }}</p>
                    </div>
                </div>
                <div v-if="recentMessages.length === 0" class="p-8 text-center text-gray-400">
                    No messages yet
                </div>
            </div>
        </div>
    </div>
</template>