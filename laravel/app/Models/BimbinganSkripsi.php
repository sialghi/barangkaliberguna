<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BimbinganSkripsi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bimbingan_skripsi';

    protected $fillable = [
        'id_mahasiswa',
        'id_pembimbing',
        'id_nilai_sempro',
        'judul_skripsi',
        'sesi',
        'tanggal',
        'jenis',
        'catatan'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa');
    }

    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'id_pembimbing');
    }

    public function nilaiSempro()
    {
        return $this->belongsTo(NilaiSempro::class, 'id_nilai_sempro');
    }
}
