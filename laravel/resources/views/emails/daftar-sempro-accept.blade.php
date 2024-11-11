@component('mail::message')
# Pemberitahuan: Pendaftaran Seminar Proposal Disetujui

Yth.

Nama Mahasiswa: {{ $semproRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $semproRequest->mahasiswa->nim_nip_nidn }} <br> 

------------------------------------------------------------ <br>

Judul Skripsi: {{ $semproRequest->judul_proposal }} <br>
Periode Seminar Proposal: {{ $semproRequest->periodeSempro->periode }} <br>

------------------------------------------------------------ <br>

Kami ingin memberitahukan bahwa pendaftaran seminar proposal anda telah disetujui.
Anda dapat mengakses website kami untuk melihat detail pendaftaran seminar proposal dan menunggu kabar lebih lanjut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent