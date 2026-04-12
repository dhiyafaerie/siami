<?php

namespace App\Notifications;

use App\Models\Cycle;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineReminderNotification extends Notification
{
    use Queueable;

    public function __construct(protected Cycle $cycle) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Pengingat: Batas akhir pengumpulan dokumen siklus \"{$this->cycle->name}\" adalah " . \Carbon\Carbon::parse($this->cycle->end_date)->format('d M Y') . '.',
            'cycles_id' => $this->cycle->id,
        ];
    }
}
