<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { DashboardStats } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard() },
        ],
    },
});

defineProps<{
    stats: DashboardStats;
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
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
    </div>
</template>
