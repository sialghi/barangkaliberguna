<?php

namespace App\Http\Controllers;

use App\Models\NilaiSempro;

use Illuminate\Http\Request;
use App\Models\CatatanNilaiSempro;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CatatanNilaiSemproController extends Controller
{
    public function index($id)
    {
        $nilaiSemproPenguji = NilaiSempro::where('id', $id)
            ->select('id_penguji_1', 'id_penguji_2', 'id_penguji_3', 'id_penguji_4')
            ->first()
            ->toArray();

        $catatanNilaiSempro = CatatanNilaiSempro::where('id_nilai_sempro', $id)
            ->whereIn('id_penguji', $nilaiSemproPenguji)
            ->with('nilaiSempro', 'penguji')
            ->get();

        return response()->json($catatanNilaiSempro);
    }

    public function show($id)
    {
        $userId = Auth::user()->id;

        $catatanNilaiSempro = CatatanNilaiSempro::where('id_nilai_sempro', $id)
            ->where('id_penguji', $userId)
            ->with('nilaiSempro', 'penguji')
            ->first();

        return response()->json($catatanNilaiSempro);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $catatanNilaiSempro = CatatanNilaiSempro::where('id_nilai_sempro', $id)
                                        ->where('id_penguji', Auth::user()->id)
                                        ->first();

            $catatanNilaiSempro->judul = $request->catatanJudul;
            $catatanNilaiSempro->latar_belakang = $request->catatanLatarBelakang;
            $catatanNilaiSempro->identifikasi_masalah = $request->catatanIdentifikasiMasalah;
            $catatanNilaiSempro->pembatasan_masalah = $request->catatanPembatasanMasalah;
            $catatanNilaiSempro->perumusan_masalah = $request->catatanPerumusanMasalah;
            $catatanNilaiSempro->penelitian_terdahulu = $request->catatanPenelitianTerdahulu;
            $catatanNilaiSempro->metodologi_penelitian = $request->catatanMetodologiPenelitian;
            $catatanNilaiSempro->referensi = $request->catatanReferensi;

            $catatanNilaiSempro->save();
            DB::commit();

            return redirect()->route('nilai.seminar.proposal')->with('message', 'Catatan berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error($e->getMessage());
            return redirect()->route('nilai.seminar.proposal')->with('error', 'Gagal melakukan update');
        }
    }
}
