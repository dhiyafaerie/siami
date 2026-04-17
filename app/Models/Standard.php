<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standard extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'deskriptor' => 'string',
    ];

    public function getDeskriptorAttribute(?string $value): ?string
    {
        if ($value === null) return null;
        return str_replace('&nbsp;', ' ', $value);
    }

    /**
     * Convert HTML deskriptor to plain text, preserving line breaks
     * from block-level elements (<p>, <br>, <li>, <div>).
     */
    public static function htmlToPlainText(?string $html): string
    {
        if ($html === null || $html === '') return '';

        $text = str_replace('&nbsp;', ' ', $html);

        // Insert newlines before closing block-level tags
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        $text = preg_replace('/<\/(?:p|div|li|ol|ul|tr|h[1-6])>/i', "\n", $text);
        $text = preg_replace('/<(?:p|div|li|ol|ul|tr|h[1-6])[^>]*>/i', '', $text);

        // Strip remaining tags
        $text = strip_tags($text);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // Normalize whitespace: collapse multiple spaces within lines, trim each line
        $lines = explode("\n", $text);
        $lines = array_map(fn ($line) => trim(preg_replace('/\s+/', ' ', $line)), $lines);

        // Remove empty lines but keep structure
        $lines = array_filter($lines, fn ($line) => $line !== '');

        return implode("\n", $lines);
    }

    public function cycle()
    {
        return $this->belongsTo(Cycle::class, 'cycles_id');    
    }

    public function prodiattachment(): HasMany
    {
        return $this->hasMany(Prodiattachment::class, 'standards_id');
    }

    public function auditscore()
    {
        return $this->hasMany(Auditscore::class, 'standards_id');
    }
}
