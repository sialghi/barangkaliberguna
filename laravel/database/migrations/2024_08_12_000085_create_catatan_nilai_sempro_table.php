<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatatanNilaiSemproTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catatan_nilai_sempro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_nilai_sempro');
            $table->unsignedBigInteger('id_penguji');
            $table->text('judul')->nullable();
            $table->text('latar_belakang')->nullable();
            $table->text('identifikasi_masalah')->nullable();
            $table->text('pembatasan_masalah')->nullable();
            $table->text('perumusan_masalah')->nullable();
            $table->text('penelitian_terdahulu')->nullable();
            $table->text('metodologi_penelitian')->nullable();
            $table->text('referensi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_nilai_sempro')->references('id')->on('nilai_sempro')->onDelete('cascade');
            $table->foreign('id_penguji')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catatan_nilai_sempro');
    }
}
