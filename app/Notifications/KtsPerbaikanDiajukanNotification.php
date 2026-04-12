<?php

namespace App\Notifications;

use App\Models\Nonconformity;
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
        return [
            'message' => "Prodi \"{$this->kts->prodi?->programstudi}\" telah mengajukan tindakan perbaikan untuk KTS {$this->kts->kts}. Silakan verifikasi.",
            'nonconformity_id' => $this->kts->id,
            'kts' => $this->kts->kts,
        ];
    }
}
