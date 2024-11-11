@extends('adminlte::page')

@section('title', 'Sistem Informasi Layanan FST')

@section('css')
    <link rel="stylesheet" href="/css/styles.css">
@stop

@section('js')
    <script src="/vendor/dist/jquery/jquery.slim.min.js">
    <script src="/vendor/dist/js/bootstrap.bundle.min.js">
    <script src="/vendor/dist/jquery/jquery.min.js">
@stop

@section('content_header')
    <div class="d-flex flex-row">
        <h1>Pengaturan Profil</h1>
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
                        <table>
                            <tr>
                                <th>Nama</th>
                                <td>Nama pengguna yang terdaftar pada saat register.</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>Email pengguna yang terdaftar pada saat register.</td>
                            </tr>
                            <tr>
                                <th>NIM/NIP/NIDN</th>
                                <td>NIM/NIP/NIDN pengguna yang terdaftar pada saat register.</td>
                            </tr>
                            <tr>
                                <th>Nomor HP</th>
                                <td>Fitur untuk menambah nomor HP pengguna.</td>
                            </tr>
                            <tr>
                                <th>Kata Sandi</th>
                                <td>Fitur untuk mengubah kata sandi pengguna.</td>
                            </tr>
                            <tr>
                                <th>Tanda Tangan</th>
                                <td>Fitur untuk menambah tanda tangan khusus dosen.</td>
                            </tr>
                        </table>
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
    @if(session('success'))
        <x-adminlte-alert id="success-alert" theme="success" title="Success">
            {{ session('success') }}
        </x-adminlte-alert>
        <script>
            setTimeout(function() {
                document.getElementById('success-alert').style.display = 'none';
            }, 3000);
        </script>
    @endif

    @if ($errors->hasAny(['no_hp_regex', 'current_password_required', 'current_password_wrong', 'alt_email', 'update_error']))
        <x-adminlte-alert id="error-alert" theme="danger" title="Error">
            @foreach(['no_hp_regex', 'current_password_required', 'current_password_wrong'] as $field)
                @if($errors->has($field))
                    <li>{{ $errors->first($field) }}</li>
                @endif
            @endforeach
        </x-adminlte-alert>

        <script>
            setTimeout(function() {
                document.getElementById('error-alert').style.display = 'none';
            }, 3000);
        </script>
    @endif

    @if(session('error'))
        <x-adminlte-alert id="error-alert" theme="danger" title="Error">
            {{ session('error') }}
        </x-adminlte-alert>

        <script>
            setTimeout(function() {
                document.getElementById('error-alert').style.display = 'none';
            }, 8000);
        </script>
    @endif

    <form action="{{ route('update.setting') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <table style="background-color: transparent;">
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        Nama
                    </h5>
                </td>
                <td id="namaPengguna">
                    {{ $data->name }}
                </td>
            </tr>
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        @if (array_intersect(['mahasiswa'], $userRole))
                            NIM
                        @else
                            NIP/NIDN
                        @endif
                    </h5>
                </td>
                <td id="nim_nip_nidn">
                    {{ $data->nim_nip_nidn }}
                </td>
            </tr>
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        Email
                    </h5>
                </td>
                <td id="email">
                    {{ $data->email }}
                </td>
            </tr>
            @if (array_intersect(['mahasiswa'], $userRole))
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Jalur Masuk
                        </h5>
                    </td>
                    <td>
                        @php
                            $jalurMasuk = [
                                'Seleksi Nasional Berdasarkan Prestasi (SNBP)',
                                'Seleksi Nasional Berdasarkan Tes (SNBT)',
                                'Seleksi Prestasi Akademik Nasional Perguruan Tinggi Keagamaan Islam Negeri (SPAN-PTKIN)',
                                'Ujian Masuk Perguruan Tinggi Keagamaan Islam Negeri (UM-PTKIN)',
                                'Seleksi Penerimaan Mahasiswa Baru (SPMB)',
                                'Jalur Mandiri',
                                'Talent Scouting'
                            ];
                        @endphp
                        <select id="jalurMasuk" name="jalurMasuk" class="form-control form-control-sm" style="max-width: 500px;">
                            @if ($data->jalur_masuk)
                                @foreach ($jalurMasuk as $jalur)
                                    @if ($data->jalur_masuk === $jalur)
                                        <option value="{{ $jalur }}" selected>{{ $jalur }}</option>
                                    @else
                                        <option value="{{ $jalur }}" {{ $data->jalur_masuk === $jalur ? 'selected' : '' }}>{{ $jalur }}</option>
                                    @endif
                                @endforeach
                            @else
                                <option value="" selected disabled hidden>Pilih jalur masuk</option>
                                @foreach ($jalurMasuk as $jalur)
                                    <option value="{{ $jalur }}">{{ $jalur }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                </tr>
            @endif
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        Nomor Telepon
                    </h5>
                </td>
                <td id="noHp">
                    <div class="d-flex flex-row">
                        <div class="d-flex flex-row" style="width: 30%;">
                            <div class="form-control form-control-sm px-1" style="width: 15%;">+62</div>
                            <input type="text" name="noHp" class="form-control form-control-sm" style="width: 85%;"
                                value="{{ old('noHp', substr($data->no_hp, 2)) }}">
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        Email Alternatif
                    </h5>
                </td>
                <td>
                    <div class="d-flex flex-row">
                        <input type="email" name="altEmail" class="form-control form-control-sm" style="width: 30%;" value="{{ old('altEmail', $data->alt_email) }}">
                    </div>
                </td>
            </tr>
            @if (array_intersect(['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'kaprodi', 'sekprodi', 'dosen'], $userRole))
                <tr>
                    <td>
                        <h5 class="font-weight-bold">
                            Tanda Tangan
                        </h5>
                    </td>
                    <td>
                        <div class="d-flex flex-row">
                            @if ($data->ttd)
                                @php
                                    $imageUrl = str_replace('public/images/ttd/', '', $data->ttd);
                                @endphp
                                <img
                                    src="{{ route('image.show', ['filename' => $imageUrl]) }}"
                                    alt="Tanda Tangan {{ $data->name }}"
                                    class="img-thumbnail"
                                    style="width: 200px; height: 200px;"
                                >
                                <x-adminlte-button icon="fas fa-pencil-alt" data-toggle="modal" data-target="#inputTTD"/>
                            @else
                                <div class="d-flex flex-row align-items-center">
                                    <div class="btn btn-warning text-white text-bold mr-3">
                                        Anda belum mengunggah tanda tangan.
                                    </div>
                                    <x-adminlte-button id="btnInputTTD" label="Input Tanda Tangan" theme="primary" data-toggle="modal" data-target="#inputTTD"/>
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @endif
            <tr>
                <td>
                    <h5 class="font-weight-bold">
                        Kata Sandi
                    </h5>
                </td>
                <td>
                    <div>
                        <x-adminlte-button id="btnSandi" label="Ubah Kata Sandi" theme="primary" data-toggle="modal" data-target="#ubahKataSandi"/>
                    </div>
                </td>
            </tr>
        </table>
        <div class="d-flex flex-row mt-4">
            <a href="{{ route('setting') }}" class="btn btn-danger ml-2 text-white">Batal</a>
            <button type="submit" class="btn btn-primary ml-4">Submit</button>
        </div>
    </form>

    <!-- Modal untuk Edit Kata Sandi -->
    <div class="modal fade" id="ubahKataSandi" tabindex="-1" role="dialog" aria-labelledby="ubahKataSandiLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubah Kata Sandi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('update.password') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="current_password">Password Saat Ini</label>
                            <br>
                            <input type="password" name="current_password" id="current_password" data-validation-rules="requiredCurrent">
                        </div>

                        <div class="mb-3">
                            <label for="new_password">Password Baru</label>
                            <br>
                            <input type="password" name="new_password" id="new_password" data-validation-rules="requiredNew|string|min:8|regex:/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*\\W)(?!.*\\s).{8,}$/">
                            <br>
                            <span class="error-message"></span>
                        </div>

                        <div>
                            <label for="confirm_password">Konfirmasi Password Baru</label>
                            <br>
                            <input type="password" name="confirm_password" id="confirm_password" data-validation-rules="requiredConfirm|same:new_password">
                            <br>
                            <span class="error-message"></span>
                        </div>

                        <br>
                        <button type="button" id="btnTutupForm" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" id="btnSimpanSandi" class="btn btn-primary">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Edit Tanda Tangan -->
    <div class="modal fade" id="inputTTD" tabindex="-1" role="dialog" aria-labelledby="inputTTDlabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Tanda Tangan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="uploadForm" action="{{ route('input.ttd') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="upload_dropZone text-center mb-3 p-4">
                            <legend class="visually-hidden pt-3">Unggah Gambar</legend>
                            <svg class="upload_svg" width="60" height="60" aria-hidden="true">
                                <use href="#icon-imageUpload"></use>
                            </svg>
                            <input id="upload_image_background" class="position-absolute invisible" name="ttd" type="file" accept="image/jpeg, image/png" />
                            <label class="btn btn-upload mb-3" for="upload_image_background">Pilih gambar</label>
                            <div class="upload_gallery d-flex flex-wrap justify-content-center gap-3 mb-0"></div>
                        </div>
                        <svg style="display:none">
                            <defs>
                                <symbol id="icon-imageUpload" clip-rule="evenodd" viewBox="0 0 96 96">
                                <path d="M47 6a21 21 0 0 0-12.3 3.8c-2.7 2.1-4.4 5-4.7 7.1-5.8 1.2-10.3 5.6-10.3 10.6 0 6 5.8 11 13 11h12.6V22.7l-7.1 6.8c-.4.3-.9.5-1.4.5-1 0-2-.8-2-1.7 0-.4.3-.9.6-1.2l10.3-8.8c.3-.4.8-.6 1.3-.6.6 0 1 .2 1.4.6l10.2 8.8c.4.3.6.8.6 1.2 0 1-.9 1.7-2 1.7-.5 0-1-.2-1.3-.5l-7.2-6.8v15.6h14.4c6.1 0 11.2-4.1 11.2-9.4 0-5-4-8.8-9.5-9.4C63.8 11.8 56 5.8 47 6Zm-1.7 42.7V38.4h3.4v10.3c0 .8-.7 1.5-1.7 1.5s-1.7-.7-1.7-1.5Z M27 49c-4 0-7 2-7 6v29c0 3 3 6 6 6h42c3 0 6-3 6-6V55c0-4-3-6-7-6H28Zm41 3c1 0 3 1 3 3v19l-13-6a2 2 0 0 0-2 0L44 79l-10-5a2 2 0 0 0-2 0l-9 7V55c0-2 2-3 4-3h41Z M40 62c0 2-2 4-5 4s-5-2-5-4 2-4 5-4 5 2 5 4Z"/>
                                </symbol>
                            </defs>
                        </svg>

                        <button type="button" id="btnTutupForm" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="submit" id="btnInputTTD" class="btn btn-primary">Unggah</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function() {
                var $submitButton = $('#btnSimpanSandi');

                $('input[data-validation-rules]').on('input', function() {
                    var $input = $(this);
                    var rules = $input.data('validation-rules').split('|');
                    var errorMessage = '';

                    rules.forEach(function(rule) {
                        var ruleParts = rule.split(':');
                        var ruleName = ruleParts[0];
                        var ruleParams = ruleParts[1] ? ruleParts[1].split(',') : [];

                        switch (ruleName) {
                            case 'requiredCurrent':
                                if ($input.val().trim() === '') {
                                    errorMessage = 'Kata sandi lama harus diisi';
                                }
                                break;
                            case 'requiredNew':
                                if ($input.val().trim() === '') {
                                    errorMessage = 'Kata sandi baru harus diisi';
                                }
                                break;
                            case 'requiredConfirm':
                                if ($input.val().trim() === '') {
                                    errorMessage = 'Kata sandi konfirmasi harus diisi';
                                }
                                break;
                            case 'string':
                                if (typeof $input.val() !== 'string') {
                                    errorMessage = 'Kata sandi harus berupa string';
                                }
                                break;
                            case 'min':
                                var min = parseInt(ruleParams[0]);
                                if ($input.val().length < min) {
                                    errorMessage = 'Kata sandi minimal 8 karakter';
                                }
                                break;
                            case 'regex':
                                var regexPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).*$/;
                                if (!regexPattern.test($input.val())) {
                                    errorMessage = 'Kata sandi harus mengandung setidaknya satu angka, satu huruf kecil, satu huruf besar, dan satu simbol';
                                }
                                break;
                            case 'same':
                                var new_password = ruleParams[0];
                                var confirm_password = $('#' + new_password);
                                if ($input.val() !== confirm_password.val()) {
                                    errorMessage = 'Kata sandi tidak cocok';
                                }
                                break;
                        }
                    });

                    var $errorElement = $input.siblings('.error-message');
                    if (errorMessage !== '') {
                        $errorElement.text(errorMessage);
                    } else {
                        $errorElement.remove();
                    }

                    var $errorElements = $('input[data-validation-rules]').siblings('.error-message');
                    var hasError = $errorElements.length > 0;

                    $submitButton.prop('disabled', hasError);
                });
            });

        </script>
    @endpush

    @push('js')
    <script>
        console.clear();
        ('use strict');


        // Drag and drop - single image file
        // https://www.smashingmagazine.com/2018/01/drag-drop-file-uploader-vanilla-js/
        // https://codepen.io/joezimjs/pen/yPWQbd?editors=1000
        (function () {

        'use strict';

        // Four objects of interest: drop zones, input elements, gallery elements, and the files.
        // dataRefs = {files: [image files], input: element ref, gallery: element ref}

        const preventDefaults = event => {
            event.preventDefault();
            event.stopPropagation();
        };

        const highlight = event =>
            event.target.classList.add('highlight');

        const unhighlight = event =>
            event.target.classList.remove('highlight');

        const getInputAndGalleryRefs = element => {
            const zone = element.closest('.upload_dropZone') || false;
            const gallery = zone.querySelector('.upload_gallery') || false;
            const input = zone.querySelector('input[type="file"]') || false;
            return {input: input, gallery: gallery};
        }

        const handleDrop = event => {
            const dataRefs = getInputAndGalleryRefs(event.target);
            dataRefs.files = event.dataTransfer.files;
            handleFiles(dataRefs);
        }


        const eventHandlers = zone => {

            const dataRefs = getInputAndGalleryRefs(zone);
            if (!dataRefs.input) return;

            // // Prevent default drag behaviors
            // ;['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
            //     zone.addEventListener(event, preventDefaults, false);
            //     document.body.addEventListener(event, preventDefaults, false);
            // });

            // // Highlighting drop area when item is dragged over it
            // ;['dragenter', 'dragover'].forEach(event => {
            //     zone.addEventListener(event, highlight, false);
            // });
            // ;['dragleave', 'drop'].forEach(event => {
            //     zone.addEventListener(event, unhighlight, false);
            // });

            // // Handle dropped files
            //     zone.addEventListener('drop', handleDrop, false);

            // Handle browse selected files
            dataRefs.input.addEventListener('change', event => {
                dataRefs.files = event.target.files;
                handleFiles(dataRefs);
            }, false);

            // Handle form submit
            document.getElementById('uploadForm').addEventListener('submit', event => {
                imageUpload(dataRefs);
            });

        }


        // Initialise ALL dropzones
        const dropZones = document.querySelectorAll('.upload_dropZone');
        for (const zone of dropZones) {
            eventHandlers(zone);
        }


        // No 'image/gif' or PDF or webp allowed here, but it's up to your use case.
        // Double checks the input "accept" attribute
        const isImageFile = file =>
            ['image/jpeg', 'image/png'].includes(file.type);


        function previewFile(dataRefs) {
            if (!dataRefs.gallery) return;

            const existingImage = dataRefs.gallery.querySelector('.upload_img');
            if (existingImage) {
                existingImage.remove();
            }

            const file = dataRefs.files[0];
            let reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onloadend = function() {
                let img = document.createElement('img');
                img.className = 'upload_img mt-2';
                img.setAttribute('alt', file.name);
                img.src = reader.result;
                dataRefs.gallery.appendChild(img);
            }
        }

        // Based on: https://flaviocopes.com/how-to-upload-files-fetch/
        const imageUpload = dataRefs => {

            // Single source route, so double check validity
            if (!dataRefs.files || !dataRefs.input) return;

            const formData = new FormData();
            formData.append('image', dataRefs.files[0]);

            fetch(dataRefs.input.getAttribute('action'), {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                console.log('posted: ', data);
                    if (data.success === true) {
                        previewFile(dataRefs);
                    } else {
                        console.log('Error: ', dataError)
                    }
                })
                .catch(error => {
                    console.error('errored: ', error);
            });
        }


        // Handle both selected and dropped files
        const handleFiles = dataRefs => {

            let files = [...dataRefs.files];

            // Remove unaccepted file types
            files = files.filter(item => {
            if (!isImageFile(item)) {
                console.log('File harus gambar, ', item.type);
            }
            return isImageFile(item) ? item : null;
            });

            if (!files.length) return;
            dataRefs.files = files;

            previewFile(dataRefs);
        }

        })();
    </script>
    @endpush
@stop
