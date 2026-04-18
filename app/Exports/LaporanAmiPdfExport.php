<?php

namespace App\Exports;

use App\Models\Cycle;
use App\Models\Nonconformity;
use App\Models\Prodi;
use App\Models\Standard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class LaporanAmiPdfExport
{
    public function __construct(
        protected Prodi $prodi,
        protected ?Cycle $cycle = null,
    ) {
        $this->cycle ??= Cycle::getActive();
    }

    public function download(): Response
    {
        $cycleId = $this->cycle?->id;

        $standards = Standard::query()
            ->when($cycleId, fn ($q) => $q->where('cycles_id', $cycleId))
            ->with([
                'prodiattachment' => fn ($q) => $q->where('prodis_id', $this->prodi->id),
                'auditscore' => fn ($q) => $q->where('prodis_id', $this->prodi->id),
            ])
            ->orderByRaw('CAST(nomor AS UNSIGNED) ASC, nomor ASC')
            ->get();

        $nonconformities = Nonconformity::where('prodis_id', $this->prodi->id)
            ->when($cycleId, fn ($q) => $q->whereHas('standard', fn ($s) => $s->where('cycles_id', $cycleId)))
            ->with('standard')
            ->get();

        $pdf = Pdf::loadView('exports.laporan-ami-pdf', [
            'prodi'           => $this->prodi,
            'cycle'           => $this->cycle,
            'standards'       => $standards,
            'nonconformities' => $nonconformities,
            'generatedAt'     => now()->format('d M Y H:i'),
            'tahun'           => $this->cycle?->year ?? now()->year,
        ])->setPaper('a4', 'portrait');

        $filename = 'laporan-ami-' . str($this->prodi->programstudi)->slug() . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
