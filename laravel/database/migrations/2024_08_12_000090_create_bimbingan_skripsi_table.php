<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBimbinganSkripsiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bimbingan_skripsi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_mahasiswa');
            $table->unsignedBigInteger('id_pembimbing');
            $table->unsignedBigInteger('id_nilai_sempro')->nullable();
            $table->text('judul_skripsi');
            $table->integer('sesi');
            $table->date('tanggal');
            $table->enum('jenis', ['online', 'offline'])->default('offline');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_mahasiswa')->references('id')->on('users');
            $table->foreign('id_pembimbing')->references('id')->on('users');
            $table->foreign('id_nilai_sempro')->references('id')->on('nilai_sempro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bimbingan_skripsi');
    }
}
