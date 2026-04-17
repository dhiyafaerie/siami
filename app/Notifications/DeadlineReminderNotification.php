<?php

namespace App\Notifications;

use App\Models\Cycle;
use Filament\Notifications\Notification as FilamentNotification;
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
        $endDate = \Carbon\Carbon::parse($this->cycle->end_date)->format('d M Y');

        return FilamentNotification::make()
            ->title('Pengingat Deadline')
            ->body("Batas akhir pengumpulan dokumen siklus \"{$this->cycle->name}\" adalah {$endDate}.")
            ->icon('heroicon-o-clock')
            ->iconColor('warning')
            ->getDatabaseMessage();
    }
}
