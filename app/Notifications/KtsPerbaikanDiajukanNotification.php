<?php

namespace App\Notifications;

use App\Models\Nonconformity;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KtsPerbaikanDiajukanNotification extends Notification
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
            ->title('Tindakan Perbaikan Diajukan')
            ->body("Prodi \"{$this->kts->prodi?->programstudi}\" telah mengajukan tindakan perbaikan untuk KTS {$this->kts->kts}. Silakan verifikasi.")
            ->icon('heroicon-o-arrow-path')
            ->iconColor('warning')
            ->getDatabaseMessage();
    }
}
