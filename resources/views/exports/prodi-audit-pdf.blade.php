<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan AMI - {{ $prodi->programstudi }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        h2 { font-size: 13px; margin-bottom: 2px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { background-color: #FFC107; color: #333; padding: 6px 8px; text-align: left; border: 1px solid #ccc; font-size: 10px; }
        td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: top; font-size: 10px; }
        tr:nth-child(even) td { background-color: #fafafa; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold; }
        .badge-1 { background: #fee2e2; color: #b91c1c; }
        .badge-2 { background: #fef3c7; color: #92400e; }
        .badge-3 { background: #d1fae5; color: #065f46; }
        .badge-4 { background: #a7f3d0; color: #047857; }
        .badge-none { background: #f3f4f6; color: #6b7280; }
        .section-title { font-size: 12px; font-weight: bold; margin: 16px 0 6px; }
        .footer { font-size: 9px; color: #999; text-align: right; margin-top: 20px; }
        a { color: #2563eb; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Audit Mutu Internal (AMI)</h1>
        <h2>{{ $prodi->programstudi }}</h2>
        @if($cycle)
            <p style="margin:4px 0 0">Siklus: <strong>{{ $cycle->name }}</strong> ({{ $cycle->year }})</p>
        @endif
    </div>

    <div class="meta">
        Fakultas: {{ $prodi->faculty?->fakultas ?? '-' }} &nbsp;|&nbsp;
        Jenjang: {{ $prodi->jenjang ?? '-' }} &nbsp;|&nbsp;
        Digenerate: {{ $generatedAt }}
    </div>

    <div class="section-title">Tabel Standar &amp; Dokumen</div>
    <table>
        <thead>
            <tr>
                <th style="width:6%">No.</th>
                <th style="width:24%">Deskriptor</th>
                <th style="width:12%">Keywords</th>
                <th style="width:18%">Link Bukti</th>
                <th style="width:16%">Keterangan</th>
                <th style="width:10%">Nilai</th>
                <th style="width:14%">Catatan Audit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($standards as $standard)
                @php
                    $attachments = $standard->prodiattachment->values();
                    $keywords = array_filter(array_map('trim', explode(',', $standard->keywords ?? '')));
                    $hasMultiple = count($keywords) > 1;
                    $letters = range('A', 'Z');
                    $score = $standard->auditscore->first();
                    $deskParts = $hasMultiple
                        ? preg_split('/\s*(?=[B-Z]\.\s)/', strip_tags($standard->deskriptor), -1, PREG_SPLIT_NO_EMPTY)
                        : [strip_tags($standard->deskriptor)];
                    $subCount = $hasMultiple ? count($keywords) : 1;
                @endphp
                @if($hasMultiple)
                    @foreach($keywords as $i => $kw)
                        <tr>
                            @if($i === 0)
                                <td rowspan="{{ $subCount }}" style="vertical-align:middle">{{ $standard->nomor }}</td>
                            @endif
                            <td>{{ trim($deskParts[$i] ?? '') }}</td>
                            <td>{{ trim($kw) }}</td>
                            <td>
                                @if(isset($attachments[$i]))
                                    <a href="{{ $attachments[$i]->link_bukti }}" target="_blank">{{ $attachments[$i]->link_bukti }}</a>
                                @else
                                    <span style="color:#999">-</span>
                                @endif
                            </td>
                            <td>{{ $attachments[$i]->keterangan ?? '-' }}</td>
                            @if($i === 0)
                                <td rowspan="{{ $subCount }}" style="vertical-align:middle">
                                    @if($score)
                                        <span class="badge badge-{{ $score->score }}">
                                            {{ $score->score }} -
                                            {{ match($score->score) { 1 => 'Kurang Cukup', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Sangat Cukup', default => 'N/A' } }}
                                        </span>
                                    @else
                                        <span class="badge badge-none">Belum Dinilai</span>
                                    @endif
                                </td>
                                <td rowspan="{{ $subCount }}" style="vertical-align:middle">{{ $score?->notes ?? '-' }}</td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td>{{ $standard->nomor }}</td>
                        <td>{{ strip_tags($standard->deskriptor) }}</td>
                        <td>{{ $standard->keywords }}</td>
                        <td>
                            @if($attachments->isNotEmpty())
                                <a href="{{ $attachments->first()->link_bukti }}" target="_blank">{{ $attachments->first()->link_bukti }}</a>
                            @else
                                <span style="color:#999">-</span>
                            @endif
                        </td>
                        <td>{{ $attachments->first()?->keterangan ?? '-' }}</td>
                        <td>
                            @if($score)
                                <span class="badge badge-{{ $score->score }}">
                                    {{ $score->score }} -
                                    {{ match($score->score) { 1 => 'Kurang Cukup', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Sangat Cukup', default => 'N/A' } }}
                                </span>
                            @else
                                <span class="badge badge-none">Belum Dinilai</span>
                            @endif
                        </td>
                        <td>{{ $score?->notes ?? '-' }}</td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="7" style="text-align:center;color:#999">Tidak ada standar</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($nonconformities->isNotEmpty())
        <div class="section-title">Daftar Ketidaksesuaian (KTS)</div>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">No.</th>
                    <th style="width:15%">Kode KTS</th>
                    <th style="width:15%">Standar</th>
                    <th style="width:62%">Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($nonconformities as $i => $kts)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $kts->kts }}</td>
                        <td>{{ $kts->standard?->nomor ?? '-' }}</td>
                        <td>{{ $kts->description ?? $kts->kts }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">Digenerate oleh SIAMI &mdash; {{ $generatedAt }}</div>
</body>
</html>
