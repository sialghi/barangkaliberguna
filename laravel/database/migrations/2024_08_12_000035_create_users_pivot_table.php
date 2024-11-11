<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_pivot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_role');
            $table->unsignedBigInteger('id_program_studi');
            $table->unsignedBigInteger('id_fakultas');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('users_pivot');
    }
}
