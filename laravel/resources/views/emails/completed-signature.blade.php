@component('mail::message')
# Pemberitahuan: Dokumen Anda Telah Ditandatangani

Yth {{ $signatureRequest->name }},

Deskripsi Surat: {{ $signatureRequest->deskripsi_surat }} <br>

Kami ingin memberitahukan bahwa tanda tangan untuk permintaan Anda telah selesai.
Anda dapat mengakses website kami untuk melihat detail surat Anda.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
