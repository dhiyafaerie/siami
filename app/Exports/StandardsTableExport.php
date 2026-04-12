<?php

namespace App\Exports;

use App\Models\Standard;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class StandardsTableExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected Collection $rows;

    public function __construct()
    {
        $userId = Auth::id();

        $standards = Standard::with([
            'prodiattachment.prodi',
            'auditscore' => fn ($q) => $q->where('auditors_id', $userId)->with('prodi'),
        ])->get();

        $rows = collect();

        foreach ($standards as $standard) {
            $prodis = $standard->auditscore->pluck('prodi')->filter()->unique('id');

            if ($prodis->isEmpty()) {
                $rows->push([
                    'standard'    => $standard,
                    'prodi'       => null,
                    'attachments' => collect(),
                    'auditscore'  => null,
                ]);
                continue;
            }

            foreach ($prodis as $prodi) {
                $attachments = $standard->prodiattachment
                    ->where('prodis_id', $prodi->id)
                    ->values();

                $score = $standard->auditscore
                    ->where('prodis_id', $prodi->id)
                    ->first();

                $rows->push([
                    'standard'    => $standard,
                    'prodi'       => $prodi,
                    'attachments' => $attachments,
                    'auditscore'  => $score,
                ]);
            }
        }

        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'No. Standar',
            'Deskriptor',
            'Keywords',
            'Program Studi',
            'Link Bukti',
            'Keterangan',
            'Nilai Audit',
            'Catatan Audit',
        ];
    }

    public function map($row): array
    {
        $scoreText = match($row['auditscore']?->score) {
            1 => '1 - Kurang Cukup',
            2 => '2 - Kurang',
            3 => '3 - Cukup',
            4 => '4 - Sangat Cukup',
            default => '-',
        };

        $attachments = $row['attachments'] ?? collect();
        $keywords = array_filter(array_map('trim', explode(',', $row['standard']->keywords ?? '')));
        $hasMultiple = count($keywords) > 1;
        $letters = range('A', 'Z');

        if ($hasMultiple) {
            $deskriptorText = strip_tags($row['standard']->deskriptor);
            $deskriptorText = preg_replace('/\s*([B-Z])\.\s/', "\n────────────────\n" . '$1. ', $deskriptorText);

            $separator = "\n────────────────\n";
            $keywordsText = collect($keywords)->values()->map(fn ($kw, $i) => ($letters[$i] ?? '') . '. ' . trim($kw))->implode($separator);

            $linkBukti = $attachments->isNotEmpty()
                ? $attachments->values()->map(fn ($a, $i) => ($letters[$i] ?? '') . '. ' . $a->link_bukti)->implode($separator)
                : '-';
            $keterangan = $attachments->isNotEmpty()
                ? $attachments->values()->map(fn ($a, $i) => ($letters[$i] ?? '') . '. ' . $a->keterangan)->implode($separator)
                : '-';
        } else {
            $deskriptorText = strip_tags($row['standard']->deskriptor);
            $keywordsText = $row['standard']->keywords;
            $linkBukti = $attachments->first()?->link_bukti ?? '-';
            $keterangan = $attachments->first()?->keterangan ?? '-';
        }

        return [
            $row['standard']->nomor,
            $deskriptorText,
            $keywordsText,
            $row['prodi']?->programstudi ?? '-',
            $linkBukti,
            $keterangan,
            $scoreText,
            $row['auditscore']?->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->rows->count() + 1;

        // Wrap text for all data cells so newlines render
        $sheet->getStyle("A2:H{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("A2:H{$lastRow}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC107'],
                ],
            ],
        ];
    }
}
