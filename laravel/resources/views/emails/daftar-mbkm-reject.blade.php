@component('mail::message')
# Pemberitahuan: Pendaftaran MBKM Ditolak

Yth.

Pendaftaran MBKM anda DITOLAK dengan alasan: <br>
{{ $mbkmRequest->alasan }} <br>

Detail: <br>
Nama Mahasiswa: {{ $mbkmRequest->mahasiswa->name }} <br>
Nomor Induk Mahasiswa: {{ $mbkmRequest->mahasiswa->nim_nip_nidn }} <br>
Dosen Pembimbing: {{ $mbkmRequest->pembimbing->name }} <br>

------------------------------------------------------------ <br>

Jenis MBKM: {{ $mbkmRequest->jenisMbkm }} <br>
Mitra : {{ $mbkmRequest->mitra }} <br>
Learning Path: {{ $mbkmRequest->learning_path }} <br>

Mata Kuliah Dikonversi: {{ $mbkmRequest->jumlah_sks }} <br>
Total SKS: {{ $mbkmRequest->jumlah_sks }} <br>

------------------------------------------------------------ <br>

Detail pendaftaran bisa dilihat pada website kami.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
