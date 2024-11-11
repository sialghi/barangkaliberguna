<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasaJabatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'masa_jabatan';

    protected $fillable = [
        'id_user', 'id_role', 'id_program_studi', 'id_fakultas', 'awal', 'akhir',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_role', 'id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi', 'id');
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas', 'id');
    }
}
