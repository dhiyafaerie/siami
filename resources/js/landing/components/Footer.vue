<template>
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <div class="max-w-6xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">
            <div>
                <h3 class="font-bold text-white text-lg mb-3">SiAMI</h3>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Sistem Informasi Audit Mutu Internal<br>
                    Universitas Darul &lsquo;Ulum Jombang
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Referensi</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li>BAN-PT SN DIKTI No. 53 Tahun 2024</li>
                    <li>Instrumen Akreditasi Program Studi</li>
                    <li>Pedoman SPMI</li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-3">Akses</h4>
                <a href="/admin" class="inline-block text-sm text-emerald-400 hover:text-emerald-300 hover:underline">
                    → Login ke Panel Admin
                </a>
            </div>
        </div>
        <div class="border-t border-slate-800">
            <div class="max-w-6xl mx-auto px-6 py-4 text-xs text-slate-500 text-center">
                &copy; {{ new Date().getFullYear() }} Lembaga Penjaminan Mutu &mdash; Universitas Darul &lsquo;Ulum Jombang
            </div>
        </div>
    </footer>
</template>
