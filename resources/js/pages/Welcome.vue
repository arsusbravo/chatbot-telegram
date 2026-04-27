<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';
import { ref } from 'vue';

interface Wallet {
    network: string;
    currency: string;
    address: string;
}

interface Package {
    name: string;
    credits: number;
    price: number;
    currency: string;
    popular?: boolean;
}

const props = withDefaults(
    defineProps<{
        canRegister: boolean;
        wallets: Wallet[];
        packages: Package[];
    }>(),
    {
        canRegister: true,
        wallets: () => [],
        packages: () => [],
    },
);

const copiedAddress = ref<string | null>(null);

function copyAddress(address: string) {
    if (window.isSecureContext && navigator.clipboard) {
        navigator.clipboard.writeText(address);
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = address;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
    }
    copiedAddress.value = address;
    setTimeout(() => copiedAddress.value = null, 2000);
}
</script>

<template>
    <Head title="ChatCewek — Temen Ngobrol AI Kamu">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="flex-1 flex items-center justify-center px-6 pb-20">
        <div class="max-w-lg w-full text-center">
            <h1 class="text-3xl sm:text-4xl font-extrabold my-4">
                Pay here
            </h1>

            <p class="text-white/40 leading-relaxed mb-10">
                You can add your credits here
            </p>

            <!-- Pricing & Payment -->
            <section class="px-6 pb-24 max-w-2xl mx-auto">
                <div class="bg-gradient-to-b from-white/[0.04] to-transparent border border-white/[0.06] rounded-2xl p-8 sm:p-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-center mb-4">Harga? Santai aja.</h2>
                    <p class="text-white/40 text-center leading-relaxed mb-8">
                        10 chat pertama <span class="text-white/80 font-semibold">GRATIS</span>. Abis itu tinggal top-up.
                    </p>

                    <!-- Packages -->
                    <div class="grid sm:grid-cols-3 gap-4 mb-10">
                        <div
                            v-for="pkg in packages"
                            :key="pkg.name"
                            class="rounded-2xl p-5 relative transition-all"
                            :class="pkg.popular
                                ? 'bg-white/[0.06] border border-pink-500/20'
                                : 'bg-white/[0.03] border border-white/[0.06] opacity-80 hover:opacity-100'"
                        >
                            <div
                                v-if="pkg.popular"
                                class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-pink-500 rounded-full text-[10px] font-bold uppercase tracking-wider"
                            >
                                Populer
                            </div>
                            <p class="text-sm text-white/40 mb-1">{{ pkg.name }}</p>
                            <p class="text-2xl font-bold" :class="pkg.popular ? 'text-white/90' : 'text-white/60'">
                                ${{ pkg.price }}
                            </p>
                            <p class="text-xs mt-1" :class="pkg.popular ? 'text-white/40' : 'text-white/20'">
                                {{ pkg.credits }} kredit chat
                            </p>
                        </div>
                    </div>

                    <!-- Wallets -->
                    <div v-if="wallets.length > 0" class="space-y-4">
                        <p class="text-sm text-white/30 text-center">Transfer ke salah satu wallet ini, lalu hubungi admin:</p>

                        <div
                            v-for="wallet in wallets"
                            :key="wallet.address"
                            class="bg-white/[0.03] border border-white/[0.06] rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-white/60">{{ wallet.currency }} — {{ wallet.network }}</span>
                                <button
                                    @click="copyAddress(wallet.address)"
                                    class="text-xs px-3 py-1 rounded-lg transition"
                                    :class="copiedAddress === wallet.address
                                        ? 'bg-emerald-500/20 text-emerald-400'
                                        : 'bg-white/5 text-white/40 hover:text-white/60'"
                                >
                                    {{ copiedAddress === wallet.address ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                            <p class="text-xs font-mono text-white/40 break-all">{{ wallet.address }}</p>
                        </div>
                    </div>

                    <!-- No wallets yet -->
                    <div v-else class="text-center">
                        <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-white/50">
                            🚧 Pembayaran segera tersedia
                        </div>
                    </div>

                    <p class="text-center text-white/20 text-xs mt-6">Setelah transfer, kirim bukti ke admin untuk aktivasi kredit.</p>
                </div>
            </section>
        </div>
    </div>
</template>