@component('mail::message')
# Pemberitahuan: Pendaftaran Seminar Hasil Disetujui

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

Kami ingin memberitahukan bahwa pendaftaran seminar hasil anda telah disetujui.
Anda dapat mengakses website kami untuk melihat detail pendaftaran seminar hasil dan menunggu kabar lebih lanjut.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
