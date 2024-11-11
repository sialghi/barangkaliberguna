@component('mail::message')
# Pemberitahuan: Atur Ulang Kata Sandi Anda

Anda menerima email ini karena kami menerima permintaan atur ulang kata sandi untuk akun Anda.

Silakan klik tombol di bawah ini untuk mengatur ulang kata sandi Anda:

@component('mail::button', ['url' => $resetUrl])
Atur Ulang Kata Sandi
@endcomponent

Jika Anda tidak melakukan permintaan atur ulang kata sandi, abaikan email ini.

@component('mail::subcopy')
Jika Anda mengalami masalah dengan tombol di atas, salin dan tempel URL berikut ke browser web Anda: [{{ $resetUrl }}]({{ $resetUrl }})
@endcomponent

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
