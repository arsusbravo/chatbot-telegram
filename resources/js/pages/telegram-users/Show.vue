<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, updateCredits } from '@/routes/telegram-users';
import type { PaginatedData, Message, TelegramUser } from '@/types';

const props = defineProps<{
    telegramUser: TelegramUser;
    messages: PaginatedData<Message & { bot?: { name: string } }>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Telegram Users', href: index.url() },
            { title: 'Detail', href: '#' },
        ],
    },
});

const creditsForm = useForm({
    free_messages_left: props.telegramUser.free_messages_left,
    paid_credits: props.telegramUser.paid_credits,
});

function saveCredits() {
    creditsForm.put(updateCredits.url(props.telegramUser.id));
}
</script>

<template>
    <Head :title="telegramUser.first_name || `User #${telegramUser.telegram_id}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <!-- Credits Management -->
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-5">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                {{ telegramUser.first_name || 'Unknown' }}
                <span v-if="telegramUser.username" class="text-gray-400 text-base font-normal">@{{ telegramUser.username }}</span>
            </h2>
            <div class="flex items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Free Messages Left</label>
                    <input
                        v-model.number="creditsForm.free_messages_left"
                        type="number"
                        min="0"
                        class="w-32 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Paid Credits</label>
                    <input
                        v-model.number="creditsForm.paid_credits"
                        type="number"
                        min="0"
                        class="w-32 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                </div>
                <button
                    @click="saveCredits"
                    :disabled="creditsForm.processing"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium disabled:opacity-50"
                >
                    Save
                </button>
            </div>
        </div>

        <!-- Conversation -->
        <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
            <div class="p-5 border-b border-sidebar-border/70 dark:border-sidebar-border">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Conversation</h2>
            </div>
            <div class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border max-h-150 overflow-y-auto">
                <div
                    v-for="msg in messages.data"
                    :key="msg.id"
                    class="p-4 flex gap-3"
                    :class="msg.role === 'assistant' ? 'bg-gray-50 dark:bg-gray-900/30' : ''"
                >
                    <span
                        class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm"
                        :class="msg.role === 'user'
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                            : 'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300'"
                    >
                        {{ msg.role === 'user' ? '👤' : '🤖' }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-xs text-gray-400">
                            <span class="font-medium">{{ msg.role === 'user' ? telegramUser.first_name : msg.bot?.name }}</span>
                            <span>{{ new Date(msg.created_at).toLocaleString() }}</span>
                        </div>
                        <p class="text-gray-800 dark:text-gray-200 mt-1 whitespace-pre-wrap">{{ msg.content }}</p>
                    </div>
                </div>

                <div v-if="messages.data.length === 0" class="p-8 text-center text-gray-400">
                    No messages yet
                </div>
            </div>
        </div>

        <div v-if="messages.last_page > 1" class="flex items-center gap-1">
            <template v-for="link in messages.links" :key="link.label">
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