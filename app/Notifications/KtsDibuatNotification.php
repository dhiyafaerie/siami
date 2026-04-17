<?php

namespace App\Notifications;

use App\Models\Nonconformity;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KtsDibuatNotification extends Notification
{
    use Queueable;

    public function __construct(protected Nonconformity $kts) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $standardNomor = $this->kts->standard?->nomor ?? '-';
        $deadline = $this->kts->deadline_perbaikan
            ? ' Deadline perbaikan: ' . $this->kts->deadline_perbaikan->format('d M Y') . '.'
            : '';

        return FilamentNotification::make()
            ->title('KTS Baru Dicatat')
            ->body("KTS {$this->kts->kts} ({$this->kts->kategori}) tercatat untuk Standar {$standardNomor} program studi Anda.{$deadline}")
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->getDatabaseMessage();
    }
}
