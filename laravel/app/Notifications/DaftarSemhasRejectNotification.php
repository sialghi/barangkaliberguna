<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DaftarSemhasRejectNotification extends Mailable implements ShouldQueue
{
   use Queueable, SerializesModels;

   public $semhasRequest;

   /**
    * Create a new message instance.
    *
    * @param  \App\Models\NilaiSemhas  $semhasRequest
    * @return void
    */
   public function __construct($semhasRequest)
   {
      $this->semhasRequest = $semhasRequest;
   }

   /**
    * Build the message.
    *
    * @return $this
    */
   public function build()
   {
      return $this->subject('Layanan Prodi Fakultas Sains dan Teknologi: Pendaftaran Seminar Hasil Ditolak')
                  ->markdown('emails.daftar-semhas-reject');
   }
}
