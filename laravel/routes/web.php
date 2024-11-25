<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);

// Redirect root URL based on user authentication status
Route::get('/', function () {
    return Auth::check() ? redirect('/home') : redirect('/login');
});

// Only guests can access /register
Route::middleware(['guest'])->group(function () {
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.submit');
});

// Email verification routes
Route::prefix('email')->group(function () {
    Route::get('/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/verify/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed'])->name('verification.verify');
    Route::post('/resend', [VerificationController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Email verified middleware group
    Route::middleware(['auth', 'email_verified'])->group(function () {
        // Profil Pengguna
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
        Route::get('/setting', [App\Http\Controllers\SettingController::class, 'index'])->name('setting');
        Route::get('/setting/edit', [App\Http\Controllers\SettingController::class, 'edit'])->name('setting.edit');
        Route::put('/setting', [App\Http\Controllers\SettingController::class, 'update'])->name('update.setting');
        Route::put('/update/nohp', [App\Http\Controllers\SettingController::class, 'updateNoHp'])->name('update.nohp');
        Route::post('/update/password', [App\Http\Controllers\SettingController::class, 'updatePassword'])->name('update.password');
        Route::post('/input/ttd', [App\Http\Controllers\SettingController::class, 'inputTtd'])->name('input.ttd');
        Route::put('/edit/secondary_email/', [App\Http\Controllers\SettingController::class, 'updateSecondaryEmail'])->name('update.secondary.email');
        Route::put('/edit/jalur_masuk/', [App\Http\Controllers\SettingController::class, 'updateJalurMasuk'])->name('update.jalur.masuk');

        // JENIS LAYANAN - JADWAL UJIAN
        // PAGES
        Route::get('/pages/jadwal/seminar_hasil', [App\Http\Controllers\JadwalController::class, 'indexSemhas'])->name('jadwal.seminar.hasil');
        Route::get('/pages/jadwal/sidang_skripsi', [App\Http\Controllers\JadwalController::class, 'indexSkripsi'])->name('jadwal.sidang.skripsi');

        // JENIS LAYANAN - PERMOHONAN TTD KAPRODI
        // PAGES
        Route::prefix('pages/persuratan/ttd_kaprodi')->group(function(){
            Route::middleware('role:dekanat, prodi, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\LetterController::class, 'index'])->name('ttd.kaprodi');
                Route::get('/add', [App\Http\Controllers\LetterController::class, 'add'])->name('add.ttd.kaprodi');
                Route::post('/add/submit', [App\Http\Controllers\LetterController::class, 'store'])->name('submit.ttd.kaprodi');
                Route::get('/download_file/{encryptedId}', [App\Http\Controllers\LetterController::class, 'downloadFile'])->name('download.file.ttd.kaprodi');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::put('/reject_service', [App\Http\Controllers\LetterController::class, 'rejectService'])->name('reject.ttd.kaprodi');
                Route::put('/upload_new_file', [App\Http\Controllers\LetterController::class, 'uploadNewFile'])->name('new.file.ttd.kaprodi');
                Route::delete('/{id}', [App\Http\Controllers\LetterController::class, 'delete'])->name('delete.ttd.kaprodi');
            });
        });

        // JENIS LAYANAN - PERMOHONAN SURAT TUGAS
        // PAGES
        Route::prefix('pages/persuratan/permohonan_tugas')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen')->group(function(){
                Route::get('/', [App\Http\Controllers\PermohonanTugasController::class, 'index'])->name('surat.tugas');
                Route::get('/add', [App\Http\Controllers\PermohonanTugasController::class, 'add'])->name('add.surat.tugas');
                Route::post('/add/submit', [App\Http\Controllers\PermohonanTugasController::class, 'store'])->name('store.surat.tugas');
                Route::get('/download_file_input/{encryptedId}', [App\Http\Controllers\PermohonanTugasController::class, 'downloadInputFile'])->name('download.file.input.surat.tugas');
                Route::get('/download_file_output/{encryptedId}', [App\Http\Controllers\PermohonanTugasController::class, 'downloadOutputFile'])->name('download.file.output.surat.tugas');
                Route::delete('/{id}', [App\Http\Controllers\PermohonanTugasController::class, 'delete'])->name('delete.surat.tugas');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::put('/reject_service', [App\Http\Controllers\PermohonanTugasController::class, 'rejectService'])->name('reject.surat.tugas');
                Route::put('/upload_new_file', [App\Http\Controllers\PermohonanTugasController::class, 'uploadNewFile'])->name('new.file.surat.tugas');
            });
        });

        // PENDAFTARAN SEMINAR PROPOSAL
        // APIs
        Route::prefix('api/seminar_proposal/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\DaftarSemproController::class, 'show']);
                Route::get('/berkas/{id}', [App\Http\Controllers\DaftarSemproController::class, 'viewPdf']);
            });
            Route::middleware('role:dekanat, prodi')->group(function () {
                Route::get('/all-prodi', [App\Http\Controllers\ApiController::class, 'getAllProgramStudiByFakultas']);
                Route::put('/periode_sempro', [App\Http\Controllers\PeriodeSemproController::class, 'update'])->name('update.periode.sempro');
            });
        });
        // PAGES
        Route::prefix('pages/seminar_proposal/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\DaftarSemproController::class, 'index'])->name('daftar.seminar.proposal');
            });
            Route::middleware('role:dekanat, prodi, mahasiswa')->group(function(){
                Route::get('/add', [App\Http\Controllers\DaftarSemproController::class, 'add'])->name('add.daftar.seminar.proposal');
                Route::post('/add/submit', [App\Http\Controllers\DaftarSemproController::class, 'store'])->name('store.daftar.seminar.proposal');
                Route::put('/{id}', [App\Http\Controllers\DaftarSemproController::class, 'update'])->name('update.daftar.seminar.proposal');
                Route::delete('/{id}', [App\Http\Controllers\DaftarSemproController::class, 'delete'])->name('delete.daftar.seminar.proposal');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::post('/periode_sempro/add/submit', [App\Http\Controllers\PeriodeSemproController::class, 'store'])->name('store.periode.sempro');
                Route::put('/approve/{id}', [App\Http\Controllers\DaftarSemproController::class, 'approve'])->name('approve.daftar.seminar.proposal');
                Route::put('/reject/{id}', [App\Http\Controllers\DaftarSemproController::class, 'reject'])->name('reject.daftar.seminar.proposal');
                Route::put('/revise/{id}', [App\Http\Controllers\DaftarSemproController::class, 'revise'])->name('revise.daftar.seminar.proposal');
            });
        });

        // PENDAFTARAN SEMINAR HASIL
        // APIs
        Route::prefix('api/seminar_hasil/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'show']);
                Route::get('/berkas/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'viewPdf']);
            });
        });

        // PAGES
        Route::prefix('pages/seminar_hasil/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\DaftarSemhasController::class, 'index'])->name('daftar.seminar.hasil');
            });
            Route::middleware('role:dekanat, prodi, mahasiswa')->group(function(){
                Route::get('/add', [App\Http\Controllers\DaftarSemhasController::class, 'add'])->name('add.daftar.seminar.hasil');
                Route::post('/add/submit', [App\Http\Controllers\DaftarSemhasController::class, 'store'])->name('store.daftar.seminar.hasil');
                Route::get('/edit/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'edit'])->name('edit.daftar.seminar.hasil');
                Route::put('/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'update'])->name('update.daftar.seminar.hasil');
                Route::delete('/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'delete'])->name('delete.daftar.seminar.hasil');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::put('/approve/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'approve'])->name('approve.daftar.seminar.hasil');
                Route::put('/reject/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'reject'])->name('reject.daftar.seminar.hasil');
                Route::put('/revise/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'revise'])->name('revise.daftar.seminar.hasil');
            });
        });

        // PENDAFTARAN SIDANG SKRIPSI
        // APIs
        Route::prefix('api/sidang_skripsi/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'show']);
                Route::get('/berkas/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'viewPdf']);
            });
        });
        Route::prefix('api/seminar_hasil/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'show']);
                Route::get('/berkas/{id}', [App\Http\Controllers\DaftarSemhasController::class, 'viewPdf']);
            });
        });
        // PAGES
        Route::prefix('pages/sidang_skripsi/daftar')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\DaftarSkripsiController::class, 'index'])->name('daftar.sidang.skripsi');
            });
            Route::middleware('role:dekanat, prodi, mahasiswa')->group(function(){
                Route::get('/add', [App\Http\Controllers\DaftarSkripsiController::class, 'add'])->name('add.daftar.sidang.skripsi');
                Route::post('/add/submit', [App\Http\Controllers\DaftarSkripsiController::class, 'store'])->name('store.daftar.sidang.skripsi');
                Route::get('/edit/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'edit'])->name('edit.daftar.sidang.skripsi');
                Route::put('/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'update'])->name('update.daftar.sidang.skripsi');
                Route::delete('/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'delete'])->name('delete.daftar.sidang.skripsi');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::put('/approve/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'approve'])->name('approve.daftar.sidang.skripsi');
                Route::put('/reject/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'reject'])->name('reject.daftar.sidang.skripsi');
                Route::put('/revise/{id}', [App\Http\Controllers\DaftarSkripsiController::class, 'revise'])->name('revise.daftar.sidang.skripsi');
            });
        });

        // MONITORING BIMBINGAN SKRIPSI
        // APIs
        Route::prefix('api/monitoring/bimbingan_skripsi')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\BimbinganSkripsiController::class, 'show']);
            });
            Route::middleware('role:dekanat, prodi, dosen')->group(function(){
                Route::get('/add/{id}', [App\Http\Controllers\BimbinganSkripsiController::class, 'listMahasiswaBimbingan']);
            });
        });
        // PAGES
        Route::prefix('pages/monitoring/bimbingan_skripsi')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\BimbinganSkripsiController::class, 'index'])->name('monitoring.bimbingan.skripsi');
                Route::get('/add', [App\Http\Controllers\BimbinganSkripsiController::class, 'add'])->name('add.monitoring.bimbingan.skripsi');
                Route::post('/add/submit', [App\Http\Controllers\BimbinganSkripsiController::class, 'store'])->name('store.monitoring.bimbingan.skripsi');
                Route::put('/edit/{id}', [App\Http\Controllers\BimbinganSkripsiController::class, 'update'])->name('update.monitoring.bimbingan.skripsi');
                Route::delete('/{id}', [App\Http\Controllers\BimbinganSkripsiController::class, 'delete'])->name('delete.monitoring.bimbingan.skripsi');
            });
        });

        // PENILAIAN SEMINAR PROPOSAL
        // APIs
        Route::prefix('api/seminar_proposal/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\NilaiSemproController::class, 'show']);
            });
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail_catatan/{id}', [App\Http\Controllers\CatatanNilaiSemproController::class, 'show']);
                Route::get('/detail_catatan_all/{id}', [App\Http\Controllers\CatatanNilaiSemproController::class, 'index']);
            });
        });
        // PAGES
        Route::prefix('pages/seminar_proposal/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\NilaiSemproController::class, 'index'])->name('nilai.seminar.proposal');
            });
            Route::middleware('role:dekanat, prodi, dosen')->group(function(){
                Route::get('/detail_catatan/{id}', [App\Http\Controllers\CatatanNilaiSemproController::class, 'show']);
                Route::get('/detail_catatan_all/{id}' , [App\Http\Controllers\CatatanNilaiSemproController::class, 'index']);
                Route::put('/edit_catatan/{id}', [App\Http\Controllers\CatatanNilaiSemproController::class, 'update'])->name('update.catatan.nilai.seminar.proposal');
                Route::put('/approve/{id}', [App\Http\Controllers\NilaiSemproController::class, 'approve'])->name('approve.nilai.seminar.proposal');
                Route::put('/reject/{id}', [App\Http\Controllers\NilaiSemproController::class, 'reject'])->name('reject.nilai.seminar.proposal');
                Route::put('/revise/{id}', [App\Http\Controllers\NilaiSemproController::class, 'revise'])->name('revise.nilai.seminar.proposal');
                Route::get('/generate_docx/{encryptedId}', [App\Http\Controllers\NilaiSemproController::class, 'generateDocx'])->name('generate.docx.seminar.proposal');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::get('/add', [App\Http\Controllers\NilaiSemproController::class, 'add'])->name('add.nilai.seminar.proposal');
                Route::post('/add/submit', [App\Http\Controllers\NilaiSemproController::class, 'store'])->name('store.nilai.seminar.proposal');
                Route::put('/edit/{id}', [App\Http\Controllers\NilaiSemproController::class, 'update'])->name('update.nilai.seminar.proposal');
                Route::delete('/{id}', [App\Http\Controllers\NilaiSemproController::class, 'delete'])->name('delete.nilai.seminar.proposal');
            });
        });

        // PENILAIAN SEMINAR HASIL
        // APIs
        Route::prefix('api/seminar_hasil/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\NilaiSemhasController::class, 'show']);
            });
        });
        // PAGES
        Route::prefix('pages/seminar_hasil/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\NilaiSemhasController::class, 'index'])->name('nilai.seminar.hasil');
            });
            Route::middleware('role:dekanat, prodi, dosen')->group(function(){
                Route::get('/generate_docx/{encryptedId}', [App\Http\Controllers\NilaiSemhasController::class, 'generateDocx'])->name('generate.docx.nilai.seminar.hasil');
                Route::put('/save/{id}', [App\Http\Controllers\NilaiSemhasController::class, 'simpanNilai'])->name('simpan.penilaian.nilai.seminar.hasil');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::get('/add', [App\Http\Controllers\NilaiSemhasController::class, 'add'])->name('add.nilai.seminar.hasil');
                Route::post('/add/submit', [App\Http\Controllers\NilaiSemhasController::class, 'store'])->name('store.nilai.seminar.hasil');
                Route::get('/kirim_email/{encryptedId}', [App\Http\Controllers\NilaiSemhasController::class, 'kirimEmail'])->name('send.email.nilai.seminar.hasil');
                Route::delete('/{id}', [App\Http\Controllers\NilaiSemhasController::class, 'delete'])->name('delete.nilai.seminar.hasil');
            });
        });

        // PENILAIAN SIDANG SKRIPSI
        // APIs
        Route::prefix('api/sidang_skripsi/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/detail/{id}', [App\Http\Controllers\NilaiSkripsiController::class, 'show']);
            });
        });
        // PAGES
        Route::prefix('pages/sidang_skripsi/penilaian')->group(function(){
            Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
                Route::get('/', [App\Http\Controllers\NilaiSkripsiController::class, 'index'])->name('nilai.sidang.skripsi');
            });
            Route::middleware('role:dekanat, prodi, dosen')->group(function(){
                Route::get('/generate_docx/{encryptedId}', [App\Http\Controllers\NilaiSkripsiController::class, 'generateDocx'])->name('generate.docx.nilai.sidang.skripsi');
                Route::put('/{id}', [App\Http\Controllers\NilaiSkripsiController::class, 'simpanNilai'])->name('simpan.penilaian.nilai.sidang.skripsi');
            });
            Route::middleware('role:dekanat, prodi')->group(function(){
                Route::get('/add', [App\Http\Controllers\NilaiSkripsiController::class, 'add'])->name('add.nilai.sidang.skripsi');
                Route::post('/add/submit', [App\Http\Controllers\NilaiSkripsiController::class, 'store'])->name('store.nilai.sidang.skripsi');
                Route::get('/kirim_email/{encryptedId}', [App\Http\Controllers\NilaiSkripsiController::class, 'kirimEmail'])->name('send.email.nilai.sidang.skripsi');
                Route::delete('/{id}', [App\Http\Controllers\NilaiSkripsiController::class, 'delete'])->name('delete.nilai.sidang.skripsi');
            });
        });

        // DAFTAR MBKM
        // APIs
        // Route::prefix('api/mbkm/daftar')->group(function(){
        //     Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
        //         Route::get('/detail/{id}', [App\Http\Controllers\DaftarMbkmController::class, 'show']);
        //         Route::get('/berkas/{id}', [App\Http\Controllers\DaftarMbkmController::class, 'viewPdf']);
        //     });
        // });
        // PAGES
        // Route::prefix('pages/mbkm/daftar')->group(function(){
        //     Route::middleware('role:dekanat, prodi, dosen, mahasiswa')->group(function(){
        //         Route::get('/', [App\Http\Controllers\DaftarMbkmController::class, 'index'])->name('daftar.mbkm');
        //         Route::get('/add', [App\Http\Controllers\DaftarMbkmController::class, 'add'])->name('add.daftar.mbkm');
        //         Route::post('/add/submit', [App\Http\Controllers\DaftarMbkmController::class, 'store'])->name('store.daftar.mbkm');
        //         Route::get('/berkas_rekomendasi/{encryptedId}', [App\Http\Controllers\DaftarMbkmController::class, 'viewRekomendasi'])->name('view.file.rekomendasi.daftar.mbkm');
        //     });
        //     Route::middleware('role:dekanat, prodi')->group(function(){
        //         Route::put('/approve/{id}', [App\Http\Controllers\DaftarMbkmController::class, 'approve'])->name('approve.daftar.mbkm');
        //         Route::put('/reject/{id}', [App\Http\Controllers\DaftarMbkmController::class, 'reject'])->name('reject.daftar.mbkm');
        //         Route::delete('/{id}', [App\Http\Controllers\DaftarMbkmController::class, 'delete'])->name('delete.daftar.mbkm');
        //     });
        // });

        Route::middleware('role:dekanat, prodi, dosen')->group(function(){
            Route::get('/mahasiswa/{id}', [App\Http\Controllers\HomeController::class, 'showMahasiswa'])->name('home.mahasiswa.show');
        });

        Route::middleware('role:dekanat, prodi')->group(function() {
            Route::prefix('/pages/user')->group(function(){
                Route::get('/', [App\Http\Controllers\UserController::class, 'index'])->name('user.index');
                Route::get('/add', [App\Http\Controllers\UserController::class, 'add'])->name('user.add');
                Route::get('/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('user.show');
                Route::post('/add', [App\Http\Controllers\UserController::class, 'store'])->name('user.store');
                Route::get('/edit/{id}', [App\Http\Controllers\UserController::class, 'edit'])->name('user.edit');
                Route::put('/edit/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('user.update');
                Route::put('/verify/{id}', [App\Http\Controllers\UserController::class, 'verifyEmail'])->name('user.verify');
                Route::put('/unverify/{id}', [App\Http\Controllers\UserController::class, 'unverifyEmail'])->name('user.unverify');
                // Route::put('/edit/pass/{id}', [App\Http\Controllers\UserController::class, 'updatePassword'])->name('user.update.password');
                Route::delete('/delete/{id}', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.delete');
                Route::put('/restore/{id}', [App\Http\Controllers\UserController::class, 'restore'])->name('user.restore');
            });

            Route::prefix('/pages/dosen/statistik')->group(function(){
                Route::get('/', [App\Http\Controllers\StatistikDosenController::class, 'index'])->name('dosen.statistik.index');
            });
        });

        Route::get('/image/{filename}', [FileController::class, 'showImage'])->name('image.show');
    });
});

// Password reset routes
Route::middleware(['guest', 'throttle:6,1'])->group(function () {
    Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout route
// Route::get('logout', function () {
//     auth()->logout();
//     Session()->flush();
//     return redirect('/');
// })->name('logout');

// Fallback route
Route::fallback(function () {
    abort(404);
});
