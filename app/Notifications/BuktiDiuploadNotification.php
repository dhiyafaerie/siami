<?php

namespace App\Notifications;

use App\Models\Prodi;
use App\Models\Standard;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BuktiDiuploadNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Standard $standard,
        protected Prodi $prodi,
        protected int $itemCount = 1,
        protected bool $isUpdate = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $standardNomor = $this->standard->nomor ?? '-';
        $prodiNama     = $this->prodi->programstudi ?? '-';
        $verb          = $this->isUpdate ? 'memperbarui' : 'mengisi';
        $itemSuffix    = $this->itemCount > 1 ? " ({$this->itemCount} item)" : '';

        return FilamentNotification::make()
            ->title('Dokumen Bukti ' . ($this->isUpdate ? 'Diperbarui' : 'Diupload'))
            ->body("Prodi {$prodiNama} {$verb} dokumen bukti untuk Standar {$standardNomor}{$itemSuffix}.")
            ->icon('heroicon-o-document-arrow-up')
            ->iconColor('info')
            ->getDatabaseMessage();
    }
}
