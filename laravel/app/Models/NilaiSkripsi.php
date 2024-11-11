<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NilaiSkripsi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nilai_skripsi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_pembimbing_1', 'nilai_pembimbing_1',
        'id_pembimbing_2', 'nilai_pembimbing_2',
        'id_penguji_1', 'nilai_penguji_1',
        'id_penguji_2', 'nilai_penguji_2',
        'id_mahasiswa',
        'judul_skripsi', 'id_pendaftaran_skripsi',
        'tanggal_ujian', 'jam_ujian', 'ruangan_ujian', 'link_ujian'
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa')->withTrashed();
    }

    public function pembimbing1()
    {
        return $this->belongsTo(User::class, 'id_pembimbing_1');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(User::class, 'id_pembimbing_2');
    }

    public function penguji1()
    {
        return $this->belongsTo(User::class, 'id_penguji_1');
    }

    public function penguji2()
    {
        return $this->belongsTo(User::class, 'id_penguji_2');
    }

    public function pendaftaranSkripsi()
    {
        return $this->belongsTo(PendaftaranSkripsi::class, 'id_pendaftaran_skripsi');
    }
}
