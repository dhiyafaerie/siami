<?php

namespace App\Notifications;

use App\Models\Auditscore;
use Filament\Notifications\Notification as FilamentNotification;
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
            1 => 'Kurang',
            2 => 'Cukup',
            3 => 'Baik',
            4 => 'Sangat Baik',
            default => 'N/A',
        };

        $standardNomor = $this->auditScore->standard?->nomor ?? '-';

        return FilamentNotification::make()
            ->title('Nilai Audit Diberikan')
            ->body("Nilai untuk Standar {$standardNomor}: {$scoreText}.")
            ->icon('heroicon-o-clipboard-document-check')
            ->iconColor('success')
            ->getDatabaseMessage();
    }
}
