<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Report $report)
    {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return config('sideni.notifications.mail') ? ['database', 'mail'] : ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Perubahan status laporan '.$this->report->registration_number)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Status laporan Anda telah diperbarui menjadi: '.$this->statusLabel().'.')
            ->action('Lihat laporan', route('reports.show', $this->report))
            ->line('Terima kasih telah menggunakan SI-DENI Way Kanan.')
            ->salutation('Salam, '.config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'registration_number' => $this->report->registration_number,
            'status' => $this->report->status->value,
            'message' => 'Status laporan '.$this->report->registration_number.' menjadi '.$this->statusLabel().'.',
        ];
    }

    private function statusLabel(): string
    {
        return str($this->report->status->value)->replace('_', ' ')->title()->toString();
    }
}
