<?php

namespace App\Notifications;

use App\Models\Nonconformity;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KtsDitutupNotification extends Notification
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
            ->title('KTS Ditutup')
            ->body("KTS {$this->kts->kts} untuk program studi Anda telah diverifikasi dan ditutup oleh auditor.")
            ->icon('heroicon-o-check-badge')
            ->iconColor('success')
            ->getDatabaseMessage();
    }
}
