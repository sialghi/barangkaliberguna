<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ProgramStudi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program_studi';

    // protected $with = ['fakultas'];

    protected $fillable = [
        'id', 'nama', 'id_fakultas'
    ];

    // protected $hidden = ['pivot'];

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas', 'id');
    }

    public function user()
    {
        return $this->hasManyThrough(User::class, UsersPivot::class, 'id_program_studi', 'id_user');
    }
}
