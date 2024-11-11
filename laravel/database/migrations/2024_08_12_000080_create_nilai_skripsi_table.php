<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNilaiSkripsiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nilai_skripsi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_pembimbing_1');
            $table->unsignedBigInteger('id_pembimbing_2');
            $table->unsignedBigInteger('id_penguji_1');
            $table->unsignedBigInteger('id_penguji_2');
            $table->string('judul_skripsi');
            $table->date('tanggal_ujian');
            $table->string('jam_ujian')->nullable();
            $table->string('ruangan_ujian')->nullable();
            $table->string('link_ujian')->nullable();
            $table->integer('nilai_pembimbing_1')->nullable();
            $table->integer('nilai_pembimbing_2')->nullable();
            $table->integer('nilai_penguji_1')->nullable();
            $table->integer('nilai_penguji_2')->nullable();
            $table->unsignedBigInteger('id_pendaftaran_skripsi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pembimbing_1')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pembimbing_2')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_penguji_1')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_penguji_2')->references('id')->on('users')->onDelete('no action');
            $table->foreign('id_pendaftaran_skripsi')->references('id')->on('pendaftaran_skripsi')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nilai_skripsi');
    }
}
