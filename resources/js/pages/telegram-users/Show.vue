<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { index, updateCredits } from '@/routes/telegram-users';
import type { TelegramUser } from '@/types';

const props = defineProps<{
    telegramUser: TelegramUser;
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
    </div>
</template>
