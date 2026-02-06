<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UsersPivot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringDosenExportController extends Controller
{
    public function exportMahasiswa(int $dosen)
    {
        $user = Auth::user();

        $pivot = UsersPivot::where('id_user', $user->id)
            ->orderBy('id_role', 'desc')
            ->first();

        $programStudiId = $pivot?->id_program_studi;
        if (!$programStudiId) {
            abort(403);
        }

        $dosenModel = User::where('id', $dosen)
            ->whereHas('pivot', function ($q) use ($programStudiId) {
                $q->where('id_program_studi', $programStudiId)
                    ->whereHas('role', function ($rq) {
                        $rq->where('nama', 'dosen');
                    });
            })
            ->firstOrFail();

        $tabelNilaiSkripsi = 'nilai_skripsi';
        $tabelSempro = 'nilai_sempro';

        $rows = DB::table($tabelSempro)
            ->join('users as mhs', "$tabelSempro.id_mahasiswa", '=', 'mhs.id')
            ->join('users as dsn', "$tabelSempro.id_pembimbing_1", '=', 'dsn.id')
            ->leftJoin('bimbingan_skripsi', "$tabelSempro.id_mahasiswa", '=', 'bimbingan_skripsi.id_mahasiswa')
            ->leftJoin($tabelNilaiSkripsi, "$tabelSempro.id_mahasiswa", '=', "$tabelNilaiSkripsi.id_mahasiswa")
            ->where("$tabelSempro.status", 'Diterima')
            ->where("$tabelSempro.id_pembimbing_1", $dosenModel->id)
            ->select(
                DB::raw("COALESCE(bimbingan_skripsi.judul_skripsi, $tabelSempro.judul_proposal) as judul_skripsi"),
                'bimbingan_skripsi.sesi',
                'mhs.name as nama_mahasiswa',
                'mhs.nim_nip_nidn as nim_mahasiswa',
                DB::raw("CASE WHEN 
                    $tabelNilaiSkripsi.nilai_pembimbing_1 IS NOT NULL AND 
                    $tabelNilaiSkripsi.nilai_pembimbing_2 IS NOT NULL AND 
                    $tabelNilaiSkripsi.nilai_penguji_1 IS NOT NULL AND 
                    $tabelNilaiSkripsi.nilai_penguji_2 IS NOT NULL 
                    THEN 1 ELSE 0 END as is_finished")
            )
            ->get()
            ->unique('nama_mahasiswa')
            ->values();

        $filename = $this->makeFilename($dosenModel->name);

        return response()->streamDownload(function () use ($rows) {
            // UTF-8 BOM for Excel
            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'w');

            fputcsv($out, ['No', 'Nama Mahasiswa', 'NIM', 'Judul', 'Bimbingan', 'Status']);

            $i = 1;
            foreach ($rows as $row) {
                $status = ((int) ($row->is_finished ?? 0)) === 1 ? 'Selesai' : 'Ongoing';
                fputcsv($out, [
                    $i++,
                    (string) ($row->nama_mahasiswa ?? ''),
                    (string) ($row->nim_mahasiswa ?? ''),
                    (string) ($row->judul_skripsi ?? ''),
                    (string) ($row->sesi ?? 0),
                    $status,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function makeFilename(string $namaDosen): string
    {
        $safe = preg_replace('/[^A-Za-z0-9 _\-\.]/', '', $namaDosen);
        $safe = trim(preg_replace('/\s+/', ' ', $safe));
        $date = now()->format('Y-m-d');

        return "Monitoring-Dosen-{$safe}-{$date}.csv";
    }
}
