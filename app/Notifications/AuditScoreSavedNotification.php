<?php

namespace App\Notifications;

use App\Models\Auditscore;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AuditScoreSavedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Auditscore $auditScore) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $scoreText = match ($this->auditScore->score) {
            1 => 'Kurang Cukup',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Sangat Cukup',
            default => 'N/A',
        };

        $standardNomor = $this->auditScore->standard?->nomor ?? '-';

        return [
            'message' => "Nilai audit untuk Standar {$standardNomor} telah diberikan: {$scoreText}.",
            'standards_id' => $this->auditScore->standards_id,
            'score' => $this->auditScore->score,
        ];
    }
}
