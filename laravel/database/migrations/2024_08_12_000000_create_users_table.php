<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nim_nip_nidn', 18)->unique();
            $table->string('email')->unique();
            $table->string('alt_email')->unique()->nullable();
            $table->string('no_hp')->nullable();
            $table->string('jalur_masuk')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('ttd')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
