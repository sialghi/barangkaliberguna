@component('mail::message')
# Pemberitahuan: Pendaftaran Seminar Proposal Ditolak

Yth.

Nama Mahasiswa: {{ $semproRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $semproRequest->mahasiswa->nim_nip_nidn }} <br>

------------------------------------------------------------ <br>

Judul Skripsi: {{ $semproRequest->judul_proposal }} <br>
Periode Seminar Proposal: {{ $semproRequest->periodeSempro->periode }} <br>

------------------------------------------------------------ <br>

Alasan Penolakan: <br>
{{ $semproRequest->alasan }} <br>

------------------------------------------------------------ <br>

Demikian pemberitahuan dari kami. <br>

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent