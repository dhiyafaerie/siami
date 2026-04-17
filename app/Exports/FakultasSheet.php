<?php

namespace App\Exports;

use App\Models\Faculty;
use App\Models\Standard;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FakultasSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithStyles, WithEvents
{
    protected array $data = [];
    protected array $mergeRanges = [];
    protected int $totalRows = 0;

    public function __construct(protected Faculty $faculty, protected ?int $prodiId = null)
    {
        $prodiIds = $this->prodiId
            ? collect([$this->prodiId])
            : $faculty->prodis()->pluck('id');
        $letters = range('A', 'Z');

        $standards = Standard::with([
            'prodiattachment' => fn ($q) => $q->whereIn('prodis_id', $prodiIds)->with('prodi'),
            'auditscore' => fn ($q) => $q->whereIn('prodis_id', $prodiIds)->with('prodi'),
        ])->get();

        $currentRow = 2; // row 1 = header

        foreach ($standards as $standard) {
            $prodis = $standard->auditscore->pluck('prodi')->filter()->unique('id');

            if ($prodis->isEmpty()) {
                $prodis = $standard->prodiattachment->pluck('prodi')->filter()->unique('id');
            }

            $keywords = array_filter(array_map('trim', explode(',', $standard->keywords ?? '')));
            $hasMultiple = count($keywords) > 1;

            if ($prodis->isEmpty()) {
                if ($hasMultiple) {
                    $deskParts = preg_split('/\s*(?=[B-Z]\.\s)/', Standard::htmlToPlainText($standard->deskriptor), -1, PREG_SPLIT_NO_EMPTY);
                    $startRow = $currentRow;

                    foreach ($keywords as $i => $kw) {
                        $this->data[] = [
                            '-',
                            $i === 0 ? $standard->nomor : '',
                            trim($deskParts[$i] ?? ''),
                            trim($kw),
                            '-', '-', '-', '-',
                        ];
                        $currentRow++;
                    }

                    $this->addMerges($startRow, $currentRow - 1, ['A', 'B', 'G', 'H']);
                } else {
                    $this->data[] = [
                        '-',
                        $standard->nomor,
                        Standard::htmlToPlainText($standard->deskriptor),
                        $standard->keywords,
                        '-', '-', '-', '-',
                    ];
                    $currentRow++;
                }
                continue;
            }

            foreach ($prodis as $prodi) {
                $attachments = $standard->prodiattachment->where('prodis_id', $prodi->id)->values();
                $score = $standard->auditscore->where('prodis_id', $prodi->id)->first();

                $scoreText = match ($score?->score) {
                    1 => '1 - Kurang',
                    2 => '2 - Cukup',
                    3 => '3 - Baik',
                    4 => '4 - Sangat Baik',
                    default => '-',
                };

                if ($hasMultiple) {
                    $deskParts = preg_split('/\s*(?=[B-Z]\.\s)/', Standard::htmlToPlainText($standard->deskriptor), -1, PREG_SPLIT_NO_EMPTY);
                    $startRow = $currentRow;

                    foreach ($keywords as $i => $kw) {
                        $att = $attachments[$i] ?? null;

                        $this->data[] = [
                            $i === 0 ? ($prodi->programstudi ?? '-') : '',
                            $i === 0 ? $standard->nomor : '',
                            trim($deskParts[$i] ?? ''),
                            trim($kw),
                            $att ? $att->link_bukti : '-',
                            $att ? $att->keterangan : '-',
                            $i === 0 ? $scoreText : '',
                            $i === 0 ? ($score?->notes ?? '-') : '',
                        ];
                        $currentRow++;
                    }

                    $this->addMerges($startRow, $currentRow - 1, ['A', 'B', 'G', 'H']);
                } else {
                    $this->data[] = [
                        $prodi->programstudi ?? '-',
                        $standard->nomor,
                        Standard::htmlToPlainText($standard->deskriptor),
                        $standard->keywords,
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

    public function title(): string
    {
        return substr($this->faculty->fakultas, 0, 31);
    }

    public function headings(): array
    {
        return [
            'Program Studi',
            'No. Standar',
            'Deskriptor',
            'Keywords',
            'Link Bukti',
            'Keterangan',
            'Nilai Audit',
            'Catatan',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->totalRows;

        $sheet->getStyle("A2:H{$lastRow}")
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // No. Standar column (B) — left-aligned
        $sheet->getStyle("B2:B{$lastRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

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
