<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasaJabatanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('masa_jabatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_role');
            $table->unsignedBigInteger('id_program_studi')->nullable();
            $table->unsignedBigInteger('id_fakultas')->nullable();
            $table->date('awal');
            $table->date('akhir');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_role')->references('id')->on('roles');
            $table->foreign('id_program_studi')->references('id')->on('program_studi');
            $table->foreign('id_fakultas')->references('id')->on('fakultas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('masa_jabatan');
    }
}
