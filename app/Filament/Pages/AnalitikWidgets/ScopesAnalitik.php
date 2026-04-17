<?php

namespace App\Filament\Pages\AnalitikWidgets;

use App\Models\Prodi;
use Illuminate\Support\Facades\Auth;

trait ScopesAnalitik
{
    /**
     * Return the list of prodi IDs the current user is allowed to see.
     * null = no scope restriction (super_admin).
     */
    protected function scopedProdiIds(): ?array
    {
        $user = Auth::user();
        if (! $user) return [];

        if ($user->hasRole('super_admin')) {
            return null;
        }

        if ($user->faculty !== null) {
            return Prodi::where('faculties_id', $user->faculty->id)->pluck('id')->all();
        }

        if ($user->hasRole('auditor')) {
            // Auditor melihat prodi yang pernah mereka beri nilai / KTS
            return \App\Models\Auditscore::where('auditors_id', $user->id)
                ->distinct('prodis_id')
                ->pluck('prodis_id')
                ->merge(
                    \App\Models\Nonconformity::where('auditors_id', $user->id)
                        ->distinct('prodis_id')
                        ->pluck('prodis_id')
                )
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }
}
