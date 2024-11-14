@component('mail::message')
# Pemberitahuan: Input Jadwal Sidang Tugas Akhir Selesai

Yth.

Nama Mahasiswa: {{ $skripsiRequest->nama_mahasiswa }} <br>
Nomor Induk Mahasiswa: {{ $skripsiRequest->nim }} <br>
Judul Skripsi: {{ $skripsiRequest->judul_skripsi }} <br>

------------------------------------------------------------ <br>
Dosen Pembimbing 1: {{ $skripsiRequest->pembimbing_1 }} <br>
Dosen Pembimbing 2: {{ $skripsiRequest->pembimbing_2 }} <br>
------------------------------------------------------------ <br>
Dosen Penguji 1: {{ $skripsiRequest->penguji_1 }} <br>
Dosen Penguji 2: {{ $skripsiRequest->penguji_2 }} <br>
------------------------------------------------------------ <br>

Kami ingin memberitahukan bahwa input jadwal sidang tugas akhir mahasiswa tersebut telah selesai.
Anda dapat mengakses website kami untuk melihat detail dan mengisi nilai sidang tugas akhir tersebut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
