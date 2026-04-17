<?php

use App\Exports\LaporanAmiPdfExport;
use App\Exports\ProdiAuditPdfExport;
use App\Http\Controllers\PublicStatsController;
use App\Models\Cycle;
use App\Models\Prodi;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('landing'))->name('landing');
Route::get('/api/public/stats', PublicStatsController::class)->name('public.stats');

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/pdf/prodi/{prodi}/{cycle?}', function (Prodi $prodi, ?Cycle $cycle = null) {
        return (new ProdiAuditPdfExport($prodi, $cycle))->download();
    })->name('pdf.prodi');

    Route::get('/pdf/laporan-ami/{prodi}/{cycle?}', function (Prodi $prodi, ?Cycle $cycle = null) {
        return (new LaporanAmiPdfExport($prodi, $cycle))->download();
    })->name('pdf.laporan-ami');
});
