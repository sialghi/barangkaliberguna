<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNilaiSemhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nilai_semhas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_pembimbing_1');
            $table->unsignedBigInteger('id_pembimbing_2');
            $table->unsignedBigInteger('id_penguji_1');
            $table->unsignedBigInteger('id_penguji_2');
            $table->string('judul_skripsi');
            $table->date('tanggal_seminar');
            $table->string('jam_seminar')->nullable();
            $table->string('ruangan_seminar')->nullable();
            $table->string('link_seminar')->nullable();
            $table->integer('nilai_pembimbing_1')->nullable();
            $table->integer('nilai_pembimbing_2')->nullable();
            $table->integer('nilai_penguji_1')->nullable();
            $table->integer('nilai_penguji_2')->nullable();
            $table->unsignedBigInteger('id_pendaftaran_semhas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pembimbing_1')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pembimbing_2')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_penguji_1')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_penguji_2')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pendaftaran_semhas')->references('id')->on('pendaftaran_semhas')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nilai_semhas');
    }
}
