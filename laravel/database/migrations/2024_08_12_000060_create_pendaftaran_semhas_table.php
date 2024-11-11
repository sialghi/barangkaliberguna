<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendaftaranSemhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftaran_semhas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->string('judul_skripsi');
            $table->dateTime('waktu_seminar');
            $table->unsignedBigInteger('id_dosen_pembimbing_akademik');
            $table->unsignedBigInteger('id_dosen_pembimbing_1');
            $table->unsignedBigInteger('id_dosen_pembimbing_2');
            $table->unsignedBigInteger('id_calon_penguji_1')->nullable();
            $table->unsignedBigInteger('id_calon_penguji_2')->nullable();
            $table->string('calon_penguji_3_name')->nullable();
            $table->string('file_transkrip_nilai');
            $table->string('file_pernyataan_karya_sendiri');
            $table->string('file_pengesahan_skripsi');
            $table->string('file_sertifikat_toafl_1');
            $table->string('file_sertifikat_toafl_2')->nullable();
            $table->string('file_sertifikat_toafl_3')->nullable();
            $table->string('file_sertifikat_toefl_1');
            $table->string('file_sertifikat_toefl_2')->nullable();
            $table->string('file_sertifikat_toefl_3')->nullable();
            $table->string('file_naskah_skripsi');
            $table->enum('status', ['Sedang Diproses', 'Diterima', 'Ditolak', 'Revisi', 'Revisi Diajukan'])->default('Sedang Diproses');
            $table->string('alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users');
            $table->foreign('id_dosen_pembimbing_akademik')->references('id')->on('users');
            $table->foreign('id_dosen_pembimbing_1')->references('id')->on('users');
            $table->foreign('id_dosen_pembimbing_2')->references('id')->on('users');
            $table->foreign('id_calon_penguji_1')->references('id')->on('users');
            $table->foreign('id_calon_penguji_2')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendaftaran_semhas');
    }
}
