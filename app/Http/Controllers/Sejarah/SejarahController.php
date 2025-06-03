<?php

namespace App\Http\Controllers\Sejarah;

use App\Models\History;
use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

class SejarahController extends Controller
{
    public function index()
    {
        $historys = History::all();

        return view('sejarah', compact('historys'));
    }
}
