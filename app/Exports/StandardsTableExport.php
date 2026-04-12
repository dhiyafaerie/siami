<?php

namespace App\Exports;

use App\Models\Standard;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StandardsTableExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected array $data = [];
    protected array $mergeRanges = [];
    protected int $totalRows = 0;

    public function __construct()
    {
        $userId = Auth::id();
        $letters = range('A', 'Z');

        $standards = Standard::with([
            'prodiattachment.prodi',
            'auditscore' => fn ($q) => $q->where('auditors_id', $userId)->with('prodi'),
        ])->get();

        $currentRow = 2; // row 1 = header

        foreach ($standards as $standard) {
            $prodis = $standard->auditscore->pluck('prodi')->filter()->unique('id');
            $keywords = array_filter(array_map('trim', explode(',', $standard->keywords ?? '')));
            $hasMultiple = count($keywords) > 1;

            if ($prodis->isEmpty()) {
                if ($hasMultiple) {
                    $deskParts = preg_split('/\s*(?=[B-Z]\.\s)/', strip_tags($standard->deskriptor), -1, PREG_SPLIT_NO_EMPTY);
                    $startRow = $currentRow;

                    foreach ($keywords as $i => $kw) {
                        $this->data[] = [
                            $i === 0 ? $standard->nomor : '',
                            trim($deskParts[$i] ?? ''),
                            trim($kw),
                            '-',
                            '-',
                            '-',
                            '-',
                            '-',
                        ];
                        $currentRow++;
                    }

                    $this->addMerges($startRow, $currentRow - 1, ['A', 'D', 'G', 'H']);
                } else {
                    $this->data[] = [
                        $standard->nomor,
                        strip_tags($standard->deskriptor),
                        $standard->keywords,
                        '-', '-', '-', '-', '-',
                    ];
                    $currentRow++;
                }
                continue;
            }

            foreach ($prodis as $prodi) {
                $attachments = $standard->prodiattachment
                    ->where('prodis_id', $prodi->id)
                    ->values();

                $score = $standard->auditscore
                    ->where('prodis_id', $prodi->id)
                    ->first();

                $scoreText = match ($score?->score) {
                    1 => '1 - Kurang Cukup',
                    2 => '2 - Kurang',
                    3 => '3 - Cukup',
                    4 => '4 - Sangat Cukup',
                    default => '-',
                };

                if ($hasMultiple) {
                    $deskParts = preg_split('/\s*(?=[B-Z]\.\s)/', strip_tags($standard->deskriptor), -1, PREG_SPLIT_NO_EMPTY);
                    $startRow = $currentRow;

                    foreach ($keywords as $i => $kw) {
                        $att = $attachments[$i] ?? null;

                        $this->data[] = [
                            $i === 0 ? $standard->nomor : '',
                            trim($deskParts[$i] ?? ''),
                            trim($kw),
                            $i === 0 ? ($prodi->programstudi ?? '-') : '',
                            $att ? $att->link_bukti : '-',
                            $att ? $att->keterangan : '-',
                            $i === 0 ? $scoreText : '',
                            $i === 0 ? ($score?->notes ?? '-') : '',
                        ];
                        $currentRow++;
                    }

                    $this->addMerges($startRow, $currentRow - 1, ['A', 'D', 'G', 'H']);
                } else {
                    $this->data[] = [
                        $standard->nomor,
                        strip_tags($standard->deskriptor),
                        $standard->keywords,
                        $prodi->programstudi ?? '-',
                        $attachments->first()?->link_bukti ?? '-',
                        $attachments->first()?->keterangan ?? '-',
                        $scoreText,
                        $score?->notes ?? '-',
                    ];
                    $currentRow++;
                }
            }
        }

        $this->totalRows = $currentRow - 1;
    }

    public function array(): array
    {
        return $this->data;
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

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->totalRows;

        $sheet->getStyle("A2:H{$lastRow}")
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Thin borders for all data
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC107'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                foreach ($this->mergeRanges as $range) {
                    $sheet->mergeCells($range);
                }
            },
        ];
    }

    protected function addMerges(int $startRow, int $endRow, array $columns): void
    {
        if ($startRow >= $endRow) {
            return;
        }
        foreach ($columns as $col) {
            $this->mergeRanges[] = "{$col}{$startRow}:{$col}{$endRow}";
        }
    }
}
