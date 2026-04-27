<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';
</script>

<template>
    <Head title="Pembayaran — ChatCewek">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-[#0c0a14] text-white flex flex-col" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <!-- Glow -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-40 left-1/4 w-[500px] h-[500px] rounded-full bg-pink-600/10 blur-[120px]" />
            <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] rounded-full bg-purple-600/8 blur-[100px]" />
        </div>

        <div class="relative z-10 flex-1 flex flex-col">
            <!-- Nav -->
            <nav class="flex items-center justify-between px-6 py-5 max-w-5xl mx-auto w-full">
                <Link :href="home()" class="flex items-center gap-2">
                    <span class="text-2xl">💬</span>
                    <span class="text-lg font-bold tracking-tight">ChatCewek</span>
                </Link>
                <Link :href="home()" class="text-sm text-white/50 hover:text-white transition">
                    &larr; Kembali
                </Link>
            </nav>

            <!-- Content -->
            <div class="flex-1 flex items-center justify-center px-6 pb-20">
                <div class="max-w-lg w-full text-center">
                    <div class="text-6xl mb-6">🏗️</div>

                    <h1 class="text-3xl sm:text-4xl font-extrabold mb-4">
                        Lagi di-<span class="text-pink-400">bangun</span> nih!
                    </h1>

                    <p class="text-white/40 leading-relaxed mb-10">
                        Halaman pembayaran lagi kita siapin biar kamu bisa top-up kredit chat dengan gampang. Sabar ya, bentar lagi jadi!
                    </p>

                    <!-- Placeholder pricing cards -->
                    <div class="grid sm:grid-cols-3 gap-4 mb-10">
                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-5 opacity-50">
                            <p class="text-sm text-white/40 mb-1">Starter</p>
                            <p class="text-2xl font-bold text-white/60">???</p>
                            <p class="text-xs text-white/20 mt-1">50 kredit chat</p>
                        </div>
                        <div class="bg-white/[0.04] border border-pink-500/20 rounded-2xl p-5 relative">
                            <div class="absolute -top-2.5 left-1/2 -translate-x-1/2 px-3 py-0.5 bg-pink-500 rounded-full text-[10px] font-bold uppercase tracking-wider">
                                Populer
                            </div>
                            <p class="text-sm text-white/40 mb-1">Regular</p>
                            <p class="text-2xl font-bold text-white/80">???</p>
                            <p class="text-xs text-white/30 mt-1">200 kredit chat</p>
                        </div>
                        <div class="bg-white/[0.03] border border-white/[0.06] rounded-2xl p-5 opacity-50">
                            <p class="text-sm text-white/40 mb-1">Sultan</p>
                            <p class="text-2xl font-bold text-white/60">???</p>
                            <p class="text-xs text-white/20 mt-1">1000 kredit chat</p>
                        </div>
                    </div>

                    <!-- Possible payment methods placeholder -->
                    <div class="bg-white/[0.02] border border-white/[0.04] rounded-xl p-5">
                        <p class="text-sm text-white/30 mb-3">Metode pembayaran yang mungkin tersedia:</p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">GoPay</span>
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">OVO</span>
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">Dana</span>
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">ShopeePay</span>
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">Transfer Bank</span>
                            <span class="px-3 py-1.5 bg-white/5 rounded-lg text-xs text-white/40">QRIS</span>
                        </div>
                    </div>

                    <div class="mt-10">
                        <Link
                            :href="home()"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-pink-500 to-rose-500 font-semibold text-sm hover:from-pink-400 hover:to-rose-400 transition-all"
                        >
                            Chat Dulu Aja Gratis
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>