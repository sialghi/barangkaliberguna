<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NilaiSempro extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nilai_sempro';

    protected $fillable = [
        'id_pendaftaran_sempro',
        'id_mahasiswa',
        'id_penguji_1',
        'id_penguji_2',
        'id_penguji_3',
        'id_penguji_4',
        'id_pembimbing_1',
        'id_pembimbing_2',
        'judul_proposal',
        'id_periode_sempro',
        'status'
    ];

    public function pendaftaranSempro()
    {
        return $this->belongsTo(PendaftaranSempro::class, 'id_pendaftaran_sempro');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'id_mahasiswa')->withTrashed();
    }

    public function penguji1()
    {
        return $this->belongsTo(User::class, 'id_penguji_1');
    }

    public function penguji2()
    {
        return $this->belongsTo(User::class, 'id_penguji_2');
    }

    public function penguji3()
    {
        return $this->belongsTo(User::class, 'id_penguji_3');
    }

    public function penguji4()
    {
        return $this->belongsTo(User::class, 'id_penguji_4');
    }

    public function pembimbing1()
    {
        return $this->belongsTo(User::class, 'id_pembimbing_1');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(User::class, 'id_pembimbing_2');
    }

    public function periodeSempro()
    {
        return $this->belongsTo(PeriodeSempro::class, 'id_periode_sempro');
    }

    public function catatan()
    {
        return $this->hasMany(CatatanNilaiSempro::class, 'id_nilai_sempro');
    }
}
