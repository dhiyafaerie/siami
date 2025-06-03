<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\LandingPage\LandingpageController::class, 'index'])->name('index');
Route::get('/sejarah', [App\Http\Controllers\Sejarah\SejarahController::class, 'index'])->name('sejarah');
Route::get('/visimisi', [App\Http\Controllers\Visimisi\VisimisiController::class, 'index'])->name('visimisi');
Route::get('/fasilitas', [App\Http\Controllers\Fasilitas\FasilitasController::class, 'index'])->name('fasilitas');
Route::get('/sambutan', [App\Http\Controllers\Sambutan\SambutanController::class, 'index'])->name('sambutan');
Route::get('/sdm', [App\Http\Controllers\Sdm\SdmController::class, 'index'])->name('sdm');
Route::get('/pengumuman', [App\Http\Controllers\Pengumuman\PengumumanController::class, 'index'])->name('pengumuman');
Route::get('/pendaftaran', [App\Http\Controllers\Pendaftaran\PendaftaranController::class, 'index'])->name('pendaftaran');
// Data pendaftaran
Route::post('/pendaftaran', [App\Http\Controllers\Pendaftaran\PendaftaranController::class, 'store'])->name('pendaftaran.store');
// Detail pengumuman slug
Route::post('/pengumuman/{slug}', [App\Http\Controllers\Pengumuman\PengumumanController::class, 'show'])->name('pengumuman.show');
Route::post('/berita/{slug}', [App\Http\Controllers\News\NewsController::class, 'show'])->name('berita.show');

