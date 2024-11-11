<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeriodeSemproTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periode_sempro', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_program_studi');
            $table->unsignedBigInteger('id_fakultas');
            $table->string('periode');
            $table->date('tanggal')->nullable();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('periode_sempro');
    }
}
