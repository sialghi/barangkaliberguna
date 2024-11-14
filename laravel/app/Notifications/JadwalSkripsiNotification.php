<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JadwalSkripsiNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $skripsiRequest;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\NilaiSemhas  $semhasRequest
     * @return void
     */
    public function __construct($skripsiRequest)
    {
        $this->skripsiRequest = $skripsiRequest;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Layanan Prodi Fakultas Sains dan Teknologi: Input Jadwal Sidang Tugas Akhir Selesai')
                    ->markdown('emails.jadwal-skripsi');
    }
}
