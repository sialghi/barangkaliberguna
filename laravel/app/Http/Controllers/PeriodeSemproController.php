<?php

namespace App\Http\Controllers;

use App\Models\PeriodeSempro;
use App\Models\UsersPivot;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Carbon\Carbon;


class PeriodeSemproController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = Auth::user();
            $userPivot = UsersPivot::where('id_user', $user->id)->with('role', 'programStudi', 'fakultas')->orderBy('id_role', 'desc')->get();

            foreach ($userPivot as $pivot) {
                $role = $pivot->role->nama;
                $programStudiId = $pivot->id_program_studi;
                $fakultasId = $pivot->id_fakultas;

                // If user has the role of Kaprodi, Sekprodi, and Admin_Prodi
                if (in_array($role, ['kaprodi', 'sekprodi', 'admin_prodi'])) {
                    $tanggalSempro = $request->tanggalSempro ? Carbon::parse($request->tanggalSempro)->format('Y-m-d') : null;

                    PeriodeSempro::create([
                        'periode' => $request->periodeSempro,
                        'tanggal' => $tanggalSempro,
                        'id_program_studi' => $programStudiId,
                        'id_fakultas' => $fakultasId,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('daftar.seminar.proposal')->with('message', 'Data periode sempro baru berhasil ditambahkan.');
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return redirect()->route('daftar.seminar.proposal')->with('error', 'Data periode sempro baru gagal ditambahkan.');
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            // dd($request->all());
            // return response()->json([
            //     'request' => $request->all(),
            // ]);
            $index = $request->indexDate;
            $tanggal = $request->input;

            $tanggalSempro = Carbon::parse($tanggal)->format('Y-m-d');
            // dd($tanggalSempro);

            // Find the record by index (or use any other identifier)
            $periodeSempro = PeriodeSempro::findOrFail($index);

            $periodeSempro->update([
                'tanggal' => $request->tanggalPeriodeUpdate,
            ]);
            // return response()->json([
            //     'request' => $request->all(),
            //     'periode sempro' => $periodeSempro,
            // ]);

            DB::commit();

            return response()->json([
                'message' => 'Tanggal berhasil diubah.'
            ]);
        } catch (QueryException $e) {
            DB::rollback();

            Log::error($e);
            return response()->json([
                'message'=> 'Tanggal gagal diubah.'
            ], 404);
        }
    }
}
