<?php

namespace App\Notifications;

use App\Models\Nonconformity;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KtsPerbaikanDitolakNotification extends Notification
{
    use Queueable;

    public function __construct(protected Nonconformity $kts) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Perbaikan KTS Ditolak')
            ->body("Perbaikan KTS {$this->kts->kts} ditolak oleh auditor. Alasan: {$this->kts->alasan_penolakan}. Silakan ajukan perbaikan ulang.")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->getDatabaseMessage();
    }
}
