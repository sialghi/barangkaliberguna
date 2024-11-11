@component('mail::message')
# Pemberitahuan: Pendaftaran Seminar Hasil Membutuhkan Revisi

Yth.

Nama Mahasiswa: {{ $semhasRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $semhasRequest->mahasiswa->nim_nip_nidn }} <br>
Dosen Pembimbing Akademik: {{ $semhasRequest->dosenPembimbingAkademik->name }} <br>

------------------------------------------------------------ <br>

Judul Skripsi: {{ $semhasRequest->judul_skripsi }} <br>
Dosen Pembimbing 1: {{ $semhasRequest->pembimbing1->name }} <br>
Dosen Pembimbing 2: {{ $semhasRequest->pembimbing2->name }} <br>
<br>
Tanggal: {{ explode(' ', $semhasRequest->waktu_seminar)[0] }} <br>
Waktu: {{ explode(' ', $semhasRequest->waktu_seminar)[1] }} <br>

------------------------------------------------------------ <br>

Alasan Revisi: <br>
{{ $semhasRequest->alasan }} <br>

------------------------------------------------------------ <br>

Anda dapat mengakses website dan mengedit detail pendaftaran anda. <br>

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
