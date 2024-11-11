<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNilaiSemproTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nilai_sempro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pendaftaran_sempro')->nullable();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_penguji_1');
            $table->unsignedBigInteger('id_penguji_2')->nullable();
            $table->unsignedBigInteger('id_penguji_3')->nullable();
            $table->unsignedBigInteger('id_penguji_4')->nullable();
            $table->unsignedBigInteger('id_pembimbing_1')->nullable();
            $table->unsignedBigInteger('id_pembimbing_2')->nullable();
            $table->string('judul_proposal');
            $table->unsignedBigInteger('id_periode_sempro');
            $table->enum('status', ['Sedang Diproses', 'Diterima', 'Ditolak', 'Revisi'])->default('Sedang Diproses');
            $table->string('alasan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_pendaftaran_sempro')->references('id')->on('pendaftaran_sempro');
            $table->foreign('id_mahasiswa')->references('id')->on('users');
            $table->foreign('id_penguji_1')->references('id')->on('users');
            $table->foreign('id_penguji_2')->references('id')->on('users');
            $table->foreign('id_penguji_3')->references('id')->on('users');
            $table->foreign('id_penguji_4')->references('id')->on('users');
            $table->foreign('id_pembimbing_1')->references('id')->on('users');
            $table->foreign('id_pembimbing_2')->references('id')->on('users');
            $table->foreign('id_periode_sempro')->references('id')->on('periode_sempro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nilai_sempro');
    }
}
