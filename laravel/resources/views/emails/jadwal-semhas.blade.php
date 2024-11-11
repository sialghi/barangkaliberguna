@component('mail::message')
# Pemberitahuan: Input Jadwal Seminar Hasil Selesai

Yth.

Nama Mahasiswa: {{ $semhasRequest->nama_mahasiswa }} <br>
Nomor Induk Mahasiswa: {{ $semhasRequest->nim }} <br>
Judul Skripsi: {{ $semhasRequest->judul_skripsi }} <br>

------------------------------------------------------------ <br>
Dosen Pembimbing 1: {{ $semhasRequest->pembimbing_1 }} <br>
Dosen Pembimbing 2: {{ $semhasRequest->pembimbing_2 }} <br>
------------------------------------------------------------ <br>
Dosen Penguji 1: {{ $semhasRequest->penguji_1 }} <br>
Dosen Penguji 2: {{ $semhasRequest->penguji_2 }} <br>
------------------------------------------------------------ <br>

Kami ingin memberitahukan bahwa input jadwal seminar hasil mahasiswa tersebut telah selesai.
Anda dapat mengakses website kami untuk melihat detail dan mengisi nilai seminar hasil tersebut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
