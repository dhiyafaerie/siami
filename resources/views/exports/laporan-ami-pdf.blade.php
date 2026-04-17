<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan AMI - {{ $prodi->programstudi }}</title>
    <style>
        @page { margin: 2cm 2cm 2cm 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; line-height: 1.5; }

        /* Cover page */
        .cover {
            text-align: center;
            page-break-after: always;
            position: relative;
            min-height: 257mm;
        }
        .cover .cover-bg {
            position: absolute;
            top: -2cm; left: -2cm;
            width: 210mm;
            height: 297mm;
        }
        .cover .cover-content {
            position: relative;
            padding-top: 80px;
        }
        .cover .brand-top { font-size: 14pt; letter-spacing: 4px; font-weight: bold; }
        .cover .brand-sub { font-size: 11pt; margin-top: 4px; letter-spacing: 2px; }
        .cover .logo-placeholder {
            width: 130px; height: 130px; margin: 40px auto; border: 3px solid #1e3a8a;
            border-radius: 50%; display: table; text-align: center;
        }
        .cover .logo-placeholder span {
            display: table-cell; vertical-align: middle; font-size: 22pt; font-weight: bold; color: #1e3a8a;
        }
        .cover h1 { font-size: 26pt; font-weight: bold; margin: 40px 0 8px; letter-spacing: 2px; }
        .cover h2 { font-size: 16pt; font-weight: normal; margin: 0 0 6px; }
        .cover h3 { font-size: 14pt; font-weight: normal; margin: 0 0 40px; }
        .cover .prodi-name { font-size: 18pt; font-weight: bold; margin-top: 30px; }
        .cover .cycle-info { font-size: 13pt; margin-top: 8px; }
        .cover .footer-institution {
            position: absolute; bottom: 40px; left: 0; right: 0; text-align: center;
            font-size: 12pt; font-weight: bold;
        }
        .cover .footer-institution .email { font-size: 10pt; font-weight: normal; margin-top: 4px; color: #555; }

        /* Prose pages */
        .page-title { font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 24px; }
        .prose p { text-align: justify; text-indent: 2.5em; margin-bottom: 10pt; }
        .signature-block { margin-top: 40px; text-align: right; }
        .page-break { page-break-after: always; }

        /* TOC */
        .toc-list { list-style: none; padding: 0; margin: 0; }
        .toc-list li { margin: 8px 0; font-size: 12pt; }
        .toc-list .num { display: inline-block; width: 2em; }

        /* Table */
        table.compil { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 9pt; }
        table.compil th {
            background-color: #e0e7ff; color: #1e3a8a; padding: 6px 6px; text-align: center;
            border: 1px solid #333; font-weight: bold; font-size: 9pt;
        }
        table.compil td { padding: 5px 6px; border: 1px solid #666; vertical-align: top; }
        table.compil tr:nth-child(even) td { background-color: #f9fafb; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .badge-1 { background: #fee2e2; color: #b91c1c; }
        .badge-2 { background: #fef3c7; color: #92400e; }
        .badge-3 { background: #d1fae5; color: #065f46; }
        .badge-4 { background: #a7f3d0; color: #047857; }
        .badge-none { background: #f3f4f6; color: #6b7280; }
        .section-title { font-size: 13pt; font-weight: bold; margin: 16px 0 8px; color: #1e3a8a; }
        a { color: #1d4ed8; word-break: break-all; text-decoration: none; }
        .num-cell { text-align: center; font-weight: bold; }
        .small-note { font-size: 9pt; color: #666; margin-bottom: 8px; }
    </style>
</head>
<body>

{{-- =================== COVER =================== --}}
@php
    $pngPath = public_path('images/exports/kborang.png');
    $jpgPath = public_path('images/exports/kborang.jpg');

    // Convert PNG -> JPG sekali saja (DomPDF lebih reliable dengan JPG)
    if (file_exists($pngPath) && !file_exists($jpgPath) && extension_loaded('gd')) {
        $png = @imagecreatefrompng($pngPath);
        if ($png) {
            $w = imagesx($png); $h = imagesy($png);
            $jpg = imagecreatetruecolor($w, $h);
            imagefilledrectangle($jpg, 0, 0, $w, $h, imagecolorallocate($jpg, 255, 255, 255));
            imagecopy($jpg, $png, 0, 0, 0, 0, $w, $h);
            imagejpeg($jpg, $jpgPath, 85);
            imagedestroy($png);
            imagedestroy($jpg);
        }
    }

    $bgSrc = file_exists($jpgPath) ? str_replace('\\', '/', $jpgPath) : null;
@endphp
<div class="cover">
    @if($bgSrc)
        <img src="{{ $bgSrc }}" class="cover-bg" alt="">
    @endif

    <div class="cover-content">
        <div class="brand-top">U N D A R</div>
        <div class="brand-sub">JOMBANG</div>

        <div class="logo-placeholder"><span>LPM</span></div>

        <h1>LAPORAN</h1>
        <h2>AUDIT MUTU INTERNAL (AMI)</h2>
        <h3>Berdasarkan BAN-PT SN DIKTI No. 53 Tahun 2024</h3>

        <div class="prodi-name">Program Studi {{ $prodi->programstudi }}</div>
        <div class="cycle-info">Fakultas: {{ $prodi->faculty?->fakultas ?? '-' }}</div>
        @if($cycle)
            <div class="cycle-info">Siklus: <strong>{{ $cycle->name }}</strong> — {{ $tahun }}</div>
        @endif

        <div class="footer-institution">
            LEMBAGA PENJAMINAN MUTU<br>
            UNIVERSITAS DARUL `ULUM JOMBANG
            <div class="email">adm.lpm@undar.ac.id</div>
        </div>
    </div>
</div>

{{-- =================== DAFTAR ISI =================== --}}
<div class="page-break">
    <div class="page-title">Daftar Isi</div>
    <ul class="toc-list">
        <li><span class="num">1.</span> Daftar Isi</li>
        <li><span class="num">2.</span> Kompilasi Kertas Kerja Borang — Program Studi {{ $prodi->programstudi }}</li>
        @if($nonconformities->isNotEmpty())
            <li><span class="num">3.</span> Daftar Ketidaksesuaian (KTS)</li>
        @endif
    </ul>
</div>

{{-- =================== TABEL KOMPILASI =================== --}}
<div class="section-title">Kompilasi Kertas Kerja Borang BAN-PT SN DIKTI No. 53 Tahun 2024</div>
<div class="small-note">
    Program Studi: <strong>{{ $prodi->programstudi }}</strong> &nbsp;|&nbsp;
    Fakultas: {{ $prodi->faculty?->fakultas ?? '-' }} &nbsp;|&nbsp;
    Jenjang: {{ $prodi->jenjang ? ucfirst($prodi->jenjang) : '-' }} &nbsp;|&nbsp;
    Siklus: {{ $cycle?->name ?? '-' }}
</div>

<table class="compil">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:28%">Deskriptor</th>
            <th style="width:14%">Keywords</th>
            <th style="width:9%">Nilai</th>
            <th style="width:22%">Catatan Audit</th>
            <th style="width:22%">Link Bukti</th>
        </tr>
    </thead>
    <tbody>
        @forelse($standards as $standard)
            @php
                $attachments = $standard->prodiattachment->values();
                $keywords = array_filter(array_map('trim', explode(',', $standard->keywords ?? '')));
                $hasMultiple = count($keywords) > 1;
                $scoreMap = $standard->auditscore->keyBy(fn ($s) => $s->keyword_index ?? 0);
                $plainDesk = \App\Models\Standard::htmlToPlainText($standard->deskriptor);
                $deskParts = $hasMultiple
                    ? preg_split('/\s*(?=[B-Z]\.\s)/', $plainDesk, -1, PREG_SPLIT_NO_EMPTY)
                    : [$plainDesk];
                $subCount = $hasMultiple ? count($keywords) : 1;
                $scoreLabel = fn ($s) => match ($s) { 1 => 'Kurang', 2 => 'Cukup', 3 => 'Baik', 4 => 'Sangat Baik', default => 'N/A' };
            @endphp

            @if($hasMultiple)
                @foreach($keywords as $i => $kw)
                    @php $scoreRow = $scoreMap->get($i); @endphp
                    <tr>
                        @if($i === 0)
                            <td rowspan="{{ $subCount }}" class="num-cell" style="vertical-align:middle">{{ $standard->nomor }}</td>
                        @endif
                        <td>{!! nl2br(e(trim($deskParts[$i] ?? ''))) !!}</td>
                        <td>{{ trim($kw) }}</td>
                        <td style="text-align:center; vertical-align:middle">
                            @if($scoreRow)
                                <span class="badge badge-{{ $scoreRow->score }}">
                                    {{ $scoreRow->score }} — {{ $scoreLabel($scoreRow->score) }}
                                </span>
                            @else
                                <span class="badge badge-none">Belum Dinilai</span>
                            @endif
                        </td>
                        <td style="vertical-align:middle">{{ $scoreRow?->notes ?? '-' }}</td>
                        <td>
                            @if(isset($attachments[$i]))
                                <a href="{{ $attachments[$i]->link_bukti }}" target="_blank">{{ $attachments[$i]->link_bukti }}</a>
                            @else
                                <span style="color:#999">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                @php $scoreRow = $scoreMap->first(); @endphp
                <tr>
                    <td class="num-cell">{{ $standard->nomor }}</td>
                    <td>{!! nl2br(e(\App\Models\Standard::htmlToPlainText($standard->deskriptor))) !!}</td>
                    <td>{{ $standard->keywords }}</td>
                    <td style="text-align:center">
                        @if($scoreRow)
                            <span class="badge badge-{{ $scoreRow->score }}">
                                {{ $scoreRow->score }} — {{ $scoreLabel($scoreRow->score) }}
                            </span>
                        @else
                            <span class="badge badge-none">Belum Dinilai</span>
                        @endif
                    </td>
                    <td>{{ $scoreRow?->notes ?? '-' }}</td>
                    <td>
                        @if($attachments->isNotEmpty())
                            <a href="{{ $attachments->first()->link_bukti }}" target="_blank">{{ $attachments->first()->link_bukti }}</a>
                        @else
                            <span style="color:#999">-</span>
                        @endif
                    </td>
                </tr>
            @endif
        @empty
            <tr><td colspan="6" style="text-align:center;color:#999">Tidak ada standar pada siklus ini</td></tr>
        @endforelse
    </tbody>
</table>

{{-- =================== KTS =================== --}}
@if($nonconformities->isNotEmpty())
    <div class="section-title" style="margin-top:24px">Daftar Ketidaksesuaian (KTS)</div>
    <table class="compil">
        <thead>
            <tr>
                <th style="width:5%">No</th>
                <th style="width:13%">Kode KTS</th>
                <th style="width:11%">Kategori</th>
                <th style="width:10%">Standar</th>
                <th style="width:12%">Status</th>
                <th style="width:49%">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nonconformities as $i => $kts)
                <tr>
                    <td class="num-cell">{{ $i + 1 }}</td>
                    <td>{{ $kts->kts }}</td>
                    <td style="text-align:center">{{ $kts->kategori ?? '-' }}</td>
                    <td style="text-align:center">{{ $kts->standard?->nomor ?? '-' }}</td>
                    <td style="text-align:center">{{ \App\Models\Nonconformity::statusOptions()[$kts->status] ?? $kts->status }}</td>
                    <td>{{ $kts->description ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div style="margin-top:30px; font-size:9pt; color:#666; text-align:right">
    Digenerate oleh SIAMI — {{ $generatedAt }}
</div>

</body>
</html>
