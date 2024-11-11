@component('mail::message')
# Pemberitahuan: Pendaftaran Sidang Skripsi Disetujui

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

Kami ingin memberitahukan bahwa pendaftaran sidang skripsi anda telah disetujui.
Anda dapat mengakses website kami untuk melihat detail pendaftaran sidang skripsi dan menunggu kabar lebih lanjut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
