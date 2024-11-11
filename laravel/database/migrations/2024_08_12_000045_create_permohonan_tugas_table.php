<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermohonanTugasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permohonan_tugas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->date('tanggal_ttd')->nullable();
            $table->string('deskripsi_surat');
            $table->string('alasan_penolakan')->nullable();
            $table->enum('status', ['Sedang Diproses', 'Diterima', 'Ditolak'])->default('Sedang Diproses');
            $table->string('file_1');
            $table->string('file_2')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permohonan_tugas');
    }
}
