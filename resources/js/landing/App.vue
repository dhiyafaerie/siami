<script setup>
import { ref, onMounted } from 'vue';
import Hero from './components/Hero.vue';
import Stats from './components/Stats.vue';
import Timeline from './components/Timeline.vue';
import About from './components/About.vue';
import Footer from './components/Footer.vue';

const data = ref({
    stats: { prodi: 0, fakultas: 0, siklus: 0, auditor: 0 },
    cycles: [],
});
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        const res = await fetch('/api/public/stats', {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) throw new Error('Gagal memuat data');
        data.value = await res.json();
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-emerald-50 text-slate-800">
        <Hero />

        <main class="max-w-6xl mx-auto px-6 py-12 space-y-16">
            <section v-if="error" class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                {{ error }}
            </section>

            <Stats :stats="data.stats" :loading="loading" />
            <Timeline :cycles="data.cycles" :loading="loading" />
            <About />
        </main>

        <Footer />
    </div>
</template>
