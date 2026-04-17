<script setup>
defineProps({
    cycles: { type: Array, required: true },
    loading: { type: Boolean, default: false },
});
</script>

<template>
    <section>
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-800 mb-2">Riwayat Siklus Audit</h2>
            <p class="text-slate-500">Perjalanan penjaminan mutu per periode</p>
        </div>

        <div v-if="loading" class="space-y-3">
            <div v-for="i in 3" :key="i" class="h-20 bg-slate-100 rounded-xl animate-pulse"></div>
        </div>

        <div v-else-if="cycles.length === 0" class="text-center py-12 text-slate-400">
            Belum ada siklus yang dicatat.
        </div>

        <ol v-else class="relative border-s-2 border-emerald-200 ml-4">
            <li v-for="cycle in cycles" :key="cycle.id" class="mb-8 ms-6">
                <span
                    :class="[
                        'absolute flex items-center justify-center w-6 h-6 rounded-full -start-3 ring-4 ring-white',
                        cycle.is_active ? 'bg-emerald-500 animate-pulse' : (cycle.is_locked ? 'bg-slate-400' : 'bg-emerald-300')
                    ]"
                >
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">{{ cycle.name }}</h3>
                            <p class="text-sm text-slate-500">Tahun {{ cycle.year }}</p>
                        </div>
                        <span
                            v-if="cycle.is_active"
                            class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold"
                        >
                            Aktif
                        </span>
                        <span
                            v-else-if="cycle.is_locked"
                            class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold"
                        >
                            Terkunci
                        </span>
                        <span
                            v-else
                            class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold"
                        >
                            Selesai
                        </span>
                    </div>
                    <div v-if="cycle.standards_count" class="mt-2 text-xs text-slate-400">
                        {{ cycle.standards_count }} standar dievaluasi
                    </div>
                </div>
            </li>
        </ol>
    </section>
</template>
