@component('mail::message')
# Pemberitahuan: Verifikasi Alamat Email Anda

Terima kasih telah mendaftar. Untuk melengkapi proses pendaftaran akun, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini:

@component('mail::button', ['url' => $verificationUrl])
Verifikasi Alamat Email
@endcomponent

Jika Anda tidak mendaftar, Anda dapat mengabaikan pesan ini.

@component('mail::subcopy')
Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser web Anda: [{{ $verificationUrl }}]({{ $verificationUrl }})
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
