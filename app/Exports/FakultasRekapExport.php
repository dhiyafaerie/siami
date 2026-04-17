<?php

namespace App\Exports;

use App\Models\Faculty;
use App\Models\Standard;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FakultasRekapExport implements WithMultipleSheets
{
    public function __construct(protected ?int $facultyId = null, protected ?int $prodiId = null)
    {
    }

    public function sheets(): array
    {
        $query = $this->facultyId
            ? Faculty::where('id', $this->facultyId)
            : Faculty::query();

        return $query->get()->map(fn ($faculty) => new FakultasSheet($faculty, $this->prodiId))->toArray();
    }
}
