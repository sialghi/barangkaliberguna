<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendaftaranSemproTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pendaftaran_sempro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_periode_sempro');
            $table->string('judul_proposal');
            $table->unsignedBigInteger('id_calon_dospem_1');
            $table->unsignedBigInteger('id_calon_dospem_2')->nullable();
            $table->string('file_proposal_skripsi');
            $table->string('file_transkrip_nilai');
            $table->enum('status', ['Sedang Diproses', 'Diterima', 'Ditolak', 'Revisi', 'Revisi Diajukan'])->default('Sedang Diproses');
            $table->string('alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users');
            $table->foreign('id_periode_sempro')->references('id')->on('periode_sempro');
            $table->foreign('id_calon_dospem_1')->references('id')->on('users');
            $table->foreign('id_calon_dospem_2')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pendaftaran_sempro');
    }
}
