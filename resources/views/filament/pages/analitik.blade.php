<x-filament-panels::page>
    @if (empty($this->getHeaderWidgets()))
        <x-filament::section>
            <p class="text-sm text-gray-500">Belum ada data untuk ditampilkan.</p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
