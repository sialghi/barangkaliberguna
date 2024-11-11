@component('mail::message')
# Pemberitahuan: Pendaftaran Seminar Proposal Membutuhkan Revisi

Yth.

Nama Mahasiswa: {{ $semproRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $semproRequest->mahasiswa->nim_nip_nidn }} <br>

------------------------------------------------------------ <br>

Judul Skripsi: {{ $semproRequest->judul_proposal }} <br>
Periode Seminar Proposal: {{ $semproRequest->periodeSempro->periode }} <br>

------------------------------------------------------------ <br>

Alasan Revisi: <br>
{{ $semproRequest->alasan }} <br>

------------------------------------------------------------ <br>

Anda dapat mengakses website dan mengedit detail pendaftaran anda. <br>

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
