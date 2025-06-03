<?php

namespace App\Http\Controllers\Fasilitas;

use App\Models\Facility;
use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class FasilitasController extends Controller
{
    public function index()
    {
        $facilities = Facility::all();
        
        return view('fasilitas', compact('facilities'));
    }
}
