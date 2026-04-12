<?php

namespace App\Exports;

use App\Models\Faculty;
use App\Models\Standard;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FakultasRekapExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return Faculty::all()->map(fn ($faculty) => new FakultasSheet($faculty))->toArray();
    }
}
