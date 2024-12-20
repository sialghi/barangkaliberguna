@component('mail::message')
# Pemberitahuan: Input Nilai Sidang Tugas Akhir Selesai

Yth.

Nama Mahasiswa: {{ $skripsiRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $skripsiRequest->mahasiswa->nim_nip_nidn }} <br>
Judul Skripsi: {{ $skripsiRequest->judul_skripsi }} <br>

------------------------------------------------------------ <br>
Dosen Pembimbing 1: {{ $skripsiRequest->pembimbing1->name }} <br>
Dosen Pembimbing 2: {{ $skripsiRequest->pembimbing2->name }} <br>
------------------------------------------------------------ <br>
Dosen Penguji 1: {{ $skripsiRequest->penguji1->name }} <br>
Dosen Penguji 2: {{ $skripsiRequest->penguji2->name }} <br>
------------------------------------------------------------ <br>

Kami ingin memberitahukan bahwa input nilai sidang tugas akhir mahasiswa tersebut telah selesai.
Anda dapat mengakses website kami untuk melihat detail sidang tugas akhir tersebut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
