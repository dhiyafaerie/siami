<script setup>
defineProps({
    stats: { type: Object, required: true },
    loading: { type: Boolean, default: false },
});

const items = [
    { key: 'prodi', label: 'Program Studi', icon: '🎓', color: 'from-emerald-500 to-teal-600' },
    { key: 'fakultas', label: 'Fakultas', icon: '🏛️', color: 'from-amber-500 to-orange-600' },
    { key: 'siklus', label: 'Siklus AMI', icon: '🔄', color: 'from-indigo-500 to-blue-600' },
    { key: 'auditor', label: 'Auditor', icon: '👥', color: 'from-rose-500 to-pink-600' },
];
</script>

<template>
    <section>
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">Cakupan SiAMI</h2>
            <p class="text-slate-500">Angka yang mewakili komitmen mutu kami</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                v-for="item in items"
                :key="item.key"
                class="relative overflow-hidden rounded-xl bg-white shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow"
            >
                <div :class="['absolute -top-4 -right-4 w-20 h-20 rounded-full bg-gradient-to-br opacity-10', item.color]"></div>
                <div class="relative">
                    <div class="text-3xl mb-2">{{ item.icon }}</div>
                    <div class="text-3xl font-bold text-slate-800 tabular-nums">
                        <span v-if="loading" class="inline-block w-10 h-8 bg-slate-100 rounded animate-pulse"></span>
                        <span v-else>{{ stats[item.key] ?? 0 }}</span>
                    </div>
                    <div class="text-sm text-slate-500 mt-1">{{ item.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
