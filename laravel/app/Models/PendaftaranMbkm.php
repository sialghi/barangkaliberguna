<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PendaftaranMbkm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pendaftaran_mbkm';

    protected $fillable = [
        'id_mahasiswa',
        'id_dosen_pembimbing',
        'jenis_mbkm',
        'mitra',
        'learning_path',
        'jumlah_sks',
        'mk_konversi',
        'file_pernyataan_komitmen',
        'file_surat_rekomendasi',
        'status',
        'alasan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa');
    }

    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'id_dosen_pembimbing');
    }
}
