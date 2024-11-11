<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendaftaranMbkmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftaran_mbkm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_dosen_pembimbing');
            $table->string('jenis_mbkm');
            $table->string('mitra');
            $table->string('learning_path')->nullable();
            $table->integer('jumlah_sks');
            $table->string('mk_konversi');
            $table->string('file_pernyataan_komitmen');
            $table->string('file_surat_rekomendasi')->nullable();
            $table->enum('status', ['Sedang Diproses', 'Diterima', 'Ditolak', 'Revisi'])->default('Sedang Diproses');
            $table->string('alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users');
            $table->foreign('id_dosen_pembimbing')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendaftaran_mbkm');
    }
}
