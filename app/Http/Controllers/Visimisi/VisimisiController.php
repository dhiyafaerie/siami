<?php

namespace App\Http\Controllers\Visimisi;

use App\Models\Visimisi;
use App\Http\Controllers\Controller;

class VisimisiController extends Controller
{
    public function index()
    {
        $visiMisi = Visimisi::first();

            // Periksa apakah ada data
        if ($visiMisi) {
            $visi = $visiMisi->visi;
            $misi = $visiMisi->misi;
            $visimisiImg = $visiMisi->image;
        } else {
            $visi = 'No Data Available';
            $misi = 'No Data Available';
            $visimisiImg = [];
        }

        return view('visimisi', compact('visi', 'misi', 'visimisiImg'));
    }

}
