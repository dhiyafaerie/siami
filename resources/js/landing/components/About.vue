<template>
    <section class="grid md:grid-cols-2 gap-8 items-center">
        <div>
            <h2 class="text-3xl font-bold text-slate-800 mb-4">Tentang LPM Undar</h2>
            <p class="text-slate-600 leading-relaxed mb-4">
                Lembaga Penjaminan Mutu Universitas Darul &lsquo;Ulum Jombang berkomitmen
                untuk menjaga dan meningkatkan mutu pendidikan tinggi melalui pelaksanaan
                Audit Mutu Internal yang sistematis, terukur, dan berkelanjutan.
            </p>
            <p class="text-slate-600 leading-relaxed mb-6">
                Mengacu pada Standar Nasional Pendidikan Tinggi (SN DIKTI) No. 53 Tahun 2024,
                SiAMI menjadi instrumen digital yang memudahkan auditor, program studi, dan
                pimpinan dalam melakukan siklus PPEPP (Penetapan, Pelaksanaan, Evaluasi,
                Pengendalian, Peningkatan).
            </p>

            <div class="flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <a href="mailto:adm.lpm@undar.ac.id" class="text-emerald-700 hover:underline font-medium">
                    adm.lpm@undar.ac.id
                </a>
            </div>
        </div>

        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-400 to-teal-600 rounded-2xl transform rotate-3"></div>
            <div class="relative bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Siklus PPEPP</h3>
                <ul class="space-y-3">
                    <li v-for="(step, i) in ppepp" :key="i" class="flex items-start gap-3">
                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 font-bold flex items-center justify-center text-sm">
                            {{ i + 1 }}
                        </span>
                        <div>
                            <div class="font-semibold text-slate-800">{{ step.title }}</div>
                            <div class="text-sm text-slate-500">{{ step.desc }}</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>

<script setup>
const ppepp = [
    { title: 'Penetapan', desc: 'Menyusun standar mutu sesuai SN DIKTI' },
    { title: 'Pelaksanaan', desc: 'Implementasi standar di program studi' },
    { title: 'Evaluasi', desc: 'Audit dan penilaian oleh tim internal' },
    { title: 'Pengendalian', desc: 'Tindak lanjut temuan & ketidaksesuaian' },
    { title: 'Peningkatan', desc: 'Perbaikan mutu berkelanjutan' },
];
</script>
