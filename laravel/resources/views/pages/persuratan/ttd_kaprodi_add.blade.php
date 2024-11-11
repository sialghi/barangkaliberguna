@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Pengajuan Surat</h1>
        <i id="panduan" class="fas fa-question-circle ml-2 my-2" data-toggle="modal" data-target="#infoModal"></i>
    </div>
    <hr>
    <div class="modal fade" id="infoModal" tabindex="-1" role="dialog" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Panduan Halaman</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="panduanSection">
                        <div>
                            <p>Panduan Tombol</p>
                            <table>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnAjukanSurat.png"/></th>
                                    <td>Tombol untuk melakukan proses pengajuan dokumen yang akan di TTD.</td>
                                </tr>
                                <tr>
                                    <th><img class="w-100" src="/img/panduan/btnBrowse.png"/></th>
                                    <td>Tombol untuk mengunggah dokumen.</td>
                                </tr>
                            </table>
                        </div>
                        <div class="mt-4">
                            <p>Panduan Pengisian</p>
                            <table>
                                <tr>
                                    <th>NIM</th>
                                    <td>NIM mahasiswa yang mengunggah dokumen.</td>
                                </tr>
                                <tr>
                                    <th>Nama Mahasiswa</th>
                                    <td>Nama mahasiswa yang mengunggah dokumen.</td>
                                </tr>
                                <tr>
                                    <th>Deskripsi Surat</th>
                                    <td>Deskripsi dokumen yang akan diunggah.</td>
                                </tr>
                                <tr>
                                    <th>Upload Surat</th>
                                    <td>Silakan unggah dokumen yang akan dikirim.</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')

@if($errors->has('fileSurat'))
    <div id="fail-alert"class="alert alert-danger" style="width:50%">
        <i class="fas fa-exclamation text-white"></i>&nbsp;&nbsp;{{ $errors->first('fileSurat') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('fail-alert').style.display = 'none';
        }, 3000);
    </script>
@endif

<h3>Data Diri</h3>
<table style="width: 50%;">
    <tr>
        <th>NIM</th>
        <td>{{ $data->nim_nip_nidn }}</td>
    </tr>
    <tr>
        <th>Nama Mahasiswa</th>
        <td>{{ $data->name }}</td>
    </tr>
</table>

<br>

<form method="POST" action="{{ route('submit.ttd.kaprodi') }}" enctype="multipart/form-data">
    @csrf
    <div style="width: 50%;">
        {{-- With prepend slot, sm size and label --}}
        <label for="deskripsiSurat">Deskripsi Surat <span class="text-red">*</span></label>
        <x-adminlte-textarea name="deskripsiSurat" rows="1"
            igroup-size="sm" placeholder="Masukkan deskripsi surat..." style="resize:none;" maxlength="255">
            {{old('deskripsiSurat')}}
        </x-adminlte-textarea>
    </div>

    <div style="width: 50%;">
        <label for="fileSurat">Unggah Surat (PDF) <span class="text-red">*</span></label>
        <x-adminlte-input-file name="fileSurat" placeholder="Klik di sini..."
            disable-feedback onchange="displayFileName(this)" accept=".pdf" />
    </div>

    <x-adminlte-button type="submit" name="ajukanSurat" label="Ajukan Surat" theme="primary" />
</form>
@stop

@push('js')
<script>
    function displayFileName(input) {
        const fileName = input.files[0]?.name || 'Klik di sini...';
        input.parentNode.querySelector('.custom-file-label').innerText = fileName;
    }
</script>
@endpush

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop
