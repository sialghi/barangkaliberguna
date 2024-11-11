<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getAllProgramStudiByFakultas($id)
    {
        $prodi = ProgramStudi::where('id_fakultas', $id)->get();
        return response()->json($prodi);
    }
}
