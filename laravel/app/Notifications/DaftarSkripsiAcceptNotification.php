<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DaftarSkripsiAcceptNotification extends Mailable implements ShouldQueue
{
   use Queueable, SerializesModels;

   public $skripsiRequest;

   /**
    * Create a new message instance.
    *
    * @param  \App\Models\NilaiSemhas  $skripsiRequest
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
      return $this->subject('Layanan Prodi Fakultas Sains dan Teknologi: Pendaftaran Sidang Skripsi Disetujui')
                  ->markdown('emails.daftar-skripsi-accept');
   }
}
