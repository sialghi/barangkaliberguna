<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_skripsi', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kategori_ta')->nullable();
            $table->foreign('id_kategori_ta')->references('id')->on('kategori_ta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftaran_skripsi', function (Blueprint $table) {
            //
        });
    }
};
