<x-filament-panels::page>
    @php
        $cycles = $this->getViewData()['cycles'];
        $rows = $this->getViewData()['rows'];
    @endphp

    @if($cycles->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 gap-3 text-gray-400 dark:text-gray-500">
            <svg class="w-12 h-12 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm font-medium">Belum ada data siklus.</p>
        </div>
    @else
        {{-- Card wrapper --}}
        <div class="rounded-2xl ring-1 ring-gray-200 dark:ring-gray-700 shadow-sm overflow-hidden bg-white dark:bg-gray-900">

            {{-- Card header --}}
            <div class="bg-gradient-to-r from-green-400 to-green-500 px-6 py-4 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-green-900">Perbandingan Nilai Audit</h2>
                    <p class="text-xs text-green-800/70 mt-0.5">Rata-rata nilai per program studi per siklus</p>
                </div>
                <span class="inline-flex items-center gap-1 bg-white/30 text-green-900 text-xs font-semibold px-3 py-1 rounded-full">
                    {{ $cycles->count() }} Siklus
                </span>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider
                                       text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700
                                       sticky left-0 bg-gray-50 dark:bg-gray-800 z-10 min-w-[200px]">
                                Program Studi
                            </th>
                            @foreach($cycles as $cycle)
                                <th class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider
                                           text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700
                                           whitespace-nowrap min-w-[120px]">
                                    {{ $cycle->name }}
                                    <span class="block normal-case font-normal text-gray-400 dark:text-gray-500">
                                        {{ $cycle->year }}
                                    </span>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($rows as $index => $row)
                            <tr class="group transition-colors duration-150 hover:bg-green-50/50 dark:hover:bg-green-900/10
                                       {{ $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/30' }}">
                                <td class="px-5 py-3.5 font-medium text-gray-800 dark:text-gray-100
                                           border-r border-gray-200 dark:border-gray-700
                                           sticky left-0 bg-inherit z-10">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/40
                                                     text-green-700 dark:text-green-300 text-xs font-bold
                                                     flex items-center justify-center flex-shrink-0">
                                            {{ $index + 1 }}
                                        </span>
                                        {{ $row['prodi']->programstudi }}
                                    </div>
                                </td>
                                @foreach($cycles as $cycle)
                                    @php $avg = $row['scores'][$cycle->id] ?? null; @endphp
                                    <td class="px-5 py-3.5 text-center border-r border-gray-200 dark:border-gray-700">
                                        @if($avg !== null)
                                            @php
                                                if ($avg >= 3.5) {
                                                    $badgeClass = 'bg-green-100 text-green-800 ring-green-200 dark:bg-green-900/30 dark:text-green-300 dark:ring-green-700/50';
                                                } elseif ($avg >= 2.5) {
                                                    $badgeClass = 'bg-yellow-100 text-yellow-800 ring-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-300 dark:ring-yellow-700/50';
                                                } else {
                                                    $badgeClass = 'bg-red-100 text-red-800 ring-red-200 dark:bg-red-900/30 dark:text-red-300 dark:ring-red-700/50';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center justify-center min-w-[52px]
                                                         px-2.5 py-1 rounded-full text-xs font-semibold
                                                         ring-1 ring-inset {{ $badgeClass }}">
                                                {{ number_format($avg, 2) }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 font-medium">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $cycles->count() + 1 }}"
                                    class="px-5 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    Belum ada data prodi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Legend footer --}}
            <div class="border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 px-6 py-3
                        flex flex-wrap gap-4 items-center">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan:</span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-3 h-3 rounded-full bg-green-400 shadow-sm"></span>
                    ≥ 3.5 &mdash; <span class="font-semibold text-green-700 dark:text-green-400">Baik</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 shadow-sm"></span>
                    2.5 – 3.49 &mdash; <span class="font-semibold text-yellow-700 dark:text-yellow-400">Cukup</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                    <span class="w-3 h-3 rounded-full bg-red-400 shadow-sm"></span>
                    &lt; 2.5 &mdash; <span class="font-semibold text-red-700 dark:text-red-400">Perlu Perhatian</span>
                </span>
            </div>
        </div>
    @endif
</x-filament-panels::page>
