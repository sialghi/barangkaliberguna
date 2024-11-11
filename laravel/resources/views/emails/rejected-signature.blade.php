@component('mail::message')
# Pemberitahuan: Dokumen Anda Telah Ditolak

Yth {{ $signatureRequest->name }},

Deskripsi Surat: {{ $signatureRequest->deskripsi_surat }} <br>
Alasan ditolak: {{ $signatureRequest->alasan_penolakan }} <br>

Kami ingin memberitahukan bahwa tanda tangan untuk permintaan Anda ditolak.
Anda dapat mengakses website kami untuk melihat alasan ditolak dokumen Anda.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
