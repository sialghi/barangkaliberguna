<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DaftarSemproAcceptNotification extends Mailable implements ShouldQueue
{
   use Queueable, SerializesModels;

   public $semproRequest;

   /**
    * Create a new message instance.
    *
    * @param  \App\Models\NilaiSemhas  $semproRequest
    * @return void
    */
   public function __construct($semproRequest)
   {
      $this->semproRequest = $semproRequest;
   }

   /**
    * Build the message.
    *
    * @return $this
    */
   public function build()
   {
      return $this->subject('Layanan Prodi Fakultas Sains dan Teknologi: Pendaftaran Seminar Proposal Disetujui')
                  ->markdown('emails.daftar-sempro-accept');
   }
}
