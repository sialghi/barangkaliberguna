@component('mail::message')
# Pemberitahuan: Pendaftaran MBKM Disetujui

Yth.

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

Kami ingin memberitahukan bahwa pendaftaran mbkm anda telah disetujui.
Surat rekomendasi dan detail pendaftaran bisa dilihat pada website kami.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
