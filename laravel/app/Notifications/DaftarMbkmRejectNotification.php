<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DaftarMbkmRejectNotification extends Mailable implements ShouldQueue
{
   use Queueable, SerializesModels;

   public $mbkmRequest;

   /**
    * Create a new message instance.
    *
    * @param  \App\Models\NilaiSemhas  $mbkmRequest
    * @return void
    */
   public function __construct($mbkmRequest)
   {
      $this->mbkmRequest = $mbkmRequest;
   }

   /**
    * Build the message.
    *
    * @return $this
    */
   public function build()
   {
      return $this->subject('Layanan Prodi Fakultas Sains dan Teknologi: Pendaftaran MBKM Ditolak')
                  ->markdown('emails.daftar-mbkm-reject');
   }
}
