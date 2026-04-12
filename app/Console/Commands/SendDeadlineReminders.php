<?php

namespace App\Console\Commands;

use App\Models\Cycle;
use App\Models\User;
use App\Notifications\DeadlineReminderNotification;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'ami:send-deadline-reminders';
    protected $description = 'Kirim notifikasi pengingat deadline siklus AMI ke semua pengguna prodi';

    public function handle(): void
    {
        $cycles = Cycle::where('is_active', true)
            ->whereNotNull('end_date')
            ->where('end_date', '>=', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->get();

        if ($cycles->isEmpty()) {
            $this->info('Tidak ada siklus aktif dengan deadline dalam 7 hari ke depan.');
            return;
        }

        $prodiUsers = User::whereHas('prodi')->get();

        foreach ($cycles as $cycle) {
            foreach ($prodiUsers as $user) {
                $user->notify(new DeadlineReminderNotification($cycle));
            }
            $this->info("Notifikasi terkirim untuk siklus: {$cycle->name} ke {$prodiUsers->count()} pengguna.");
        }
    }
}
