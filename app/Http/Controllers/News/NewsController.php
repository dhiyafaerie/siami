<?php

namespace App\Http\Controllers\News;

use App\Models\News;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function show($slug)
    {
        $berita = News::where('slug', $slug)->first();
        
        return view('berita/show', compact('berita'));
    }
}
    
