<template>
    <header class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 text-white">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <div class="absolute -top-20 -left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-amber-300 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-6xl mx-auto px-6 py-24 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-sm text-sm font-medium mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                Lembaga Penjaminan Mutu
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight mb-4">
                SiAMI
            </h1>
            <p class="text-lg sm:text-xl text-emerald-50 mb-2">
                Sistem Informasi Audit Mutu Internal
            </p>
            <p class="text-emerald-100/80 max-w-2xl mx-auto mb-10">
                Universitas Darul &lsquo;Ulum Jombang &mdash; platform penjaminan mutu akademik mengacu BAN-PT SN DIKTI No. 53 Tahun 2024
            </p>

            <a
                href="/admin"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-white text-emerald-700 font-semibold shadow-lg hover:shadow-xl hover:scale-105 transition-all"
            >
                Login ke Admin
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </header>
</template>
