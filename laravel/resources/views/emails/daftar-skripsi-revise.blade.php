@component('mail::message')
# Pemberitahuan: Pendaftaran Sidang Skripsi Membutuhkan Revisi

Yth.

Nama Mahasiswa: {{ $skripsiRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $skripsiRequest->mahasiswa->nim_nip_nidn }} <br>
Dosen Pembimbing Akademik: {{ $skripsiRequest->dosenPembimbingAkademik->name }} <br>

------------------------------------------------------------ <br>

Judul Skripsi: {{ $skripsiRequest->judul_skripsi }} <br>
Dosen Pembimbing 1: {{ $skripsiRequest->pembimbing1->name }} <br>
Dosen Pembimbing 2: {{ $skripsiRequest->pembimbing2->name }} <br>
<br>
Tanggal: {{ explode(' ', $skripsiRequest->waktu_ujian)[0] }} <br>
Waktu: {{ explode(' ', $skripsiRequest->waktu_ujian)[1] }} <br>

------------------------------------------------------------ <br>

Alasan Revisi: <br>
{{ $skripsiRequest->alasan }} <br>

------------------------------------------------------------ <br>

Demikian pemberitahuan dari kami. <br>

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
