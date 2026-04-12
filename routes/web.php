<?php

use App\Exports\ProdiAuditPdfExport;
use App\Models\Cycle;
use App\Models\Prodi;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/pdf/prodi/{prodi}/{cycle?}', function (Prodi $prodi, ?Cycle $cycle = null) {
        return (new ProdiAuditPdfExport($prodi, $cycle))->download();
    })->name('pdf.prodi');
});
