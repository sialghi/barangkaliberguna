<?php

namespace App\Http\Controllers;

use App\Models\UsersPivot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalisisPerformaAkademikController extends Controller
{
    public function data(Request $request)
    {
        $year = (int) ($request->query('year') ?? now()->year);
        $periode = $request->query('periode');
        $metric = $request->query('metric');

        if (!in_array($periode, ['jan-jun', 'jul-des'], true)) {
            return response()->json(['message' => 'Invalid periode'], 422);
        }
        if (!in_array($metric, ['lama_skripsi', 'intensitas_bimbingan'], true)) {
            return response()->json(['message' => 'Invalid metric'], 422);
        }

        [$startMonth, $endMonth, $labels] = $this->getPeriodeDefinition($periode);
        [$fakultasId, $programStudiId] = $this->getScopeForUser();

        $valuesByMonth = $metric === 'lama_skripsi'
            ? $this->getLamaSkripsiSeries($year, $startMonth, $endMonth, $fakultasId, $programStudiId)
            : $this->getIntensitasBimbinganSeries($year, $startMonth, $endMonth, $fakultasId, $programStudiId);

        $values = [];
        for ($m = $startMonth; $m <= $endMonth; $m++) {
            $values[] = $valuesByMonth[$m] ?? 0;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    private function getPeriodeDefinition(string $periode): array
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        if ($periode === 'jan-jun') {
            $labels = array_values(array_intersect_key($bulan, array_flip(range(1, 6))));
            return [1, 6, $labels];
        }

        $labels = array_values(array_intersect_key($bulan, array_flip(range(7, 12))));
        return [7, 12, $labels];
    }

    private function getScopeForUser(): array
    {
        $user = Auth::user();
        $userRoles = $user?->roles?->pluck('nama')->toArray() ?? [];

        $pivot = UsersPivot::where('id_user', $user->id)
            ->orderBy('id_role', 'desc')
            ->first();

        $dekanatRoles = ['dekan', 'wadek_satu', 'wadek_dua', 'wadek_tiga', 'admin_dekanat'];
        $prodiRoles = ['kaprodi', 'sekprodi', 'admin_prodi'];

        if (array_intersect($dekanatRoles, $userRoles)) {
            return [$pivot?->id_fakultas, null];
        }

        if (array_intersect($prodiRoles, $userRoles)) {
            return [null, $pivot?->id_program_studi];
        }

        return [null, null];
    }

    private function getIntensitasBimbinganSeries(int $year, int $startMonth, int $endMonth, $fakultasId, $programStudiId): array
    {
        // Rerata intensitas bimbingan per mahasiswa aktif (aktif = memiliki minimal 1 sesi bimbingan di bulan tsb)
        $rows = DB::table('bimbingan_skripsi as bs')
            ->join('users_pivot as up', 'bs.id_mahasiswa', '=', 'up.id_user')
            ->whereNull('bs.deleted_at')
            ->whereNull('up.deleted_at')
            ->when($fakultasId, function ($q) use ($fakultasId) {
                return $q->where('up.id_fakultas', $fakultasId);
            })
            ->when($programStudiId, function ($q) use ($programStudiId) {
                return $q->where('up.id_program_studi', $programStudiId);
            })
            ->whereYear('bs.tanggal', $year)
            ->whereMonth('bs.tanggal', '>=', $startMonth)
            ->whereMonth('bs.tanggal', '<=', $endMonth)
            ->selectRaw('MONTH(bs.tanggal) as bulan')
            ->selectRaw('ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT bs.id_mahasiswa), 0), 1) as rata')
            ->groupBy('bulan')
            ->pluck('rata', 'bulan');

        return $rows->mapWithKeys(function ($value, $key) {
            return [(int) $key => (float) $value];
        })->toArray();
    }

    private function getLamaSkripsiSeries(int $year, int $startMonth, int $endMonth, $fakultasId, $programStudiId): array
    {
        $minBimbingan = DB::table('bimbingan_skripsi')
            ->whereNull('deleted_at')
            ->selectRaw('id_mahasiswa, MIN(tanggal) as mulai_bimbingan')
            ->groupBy('id_mahasiswa');

        $rows = DB::table('nilai_skripsi as ns')
            ->join('users_pivot as up', 'ns.id_mahasiswa', '=', 'up.id_user')
            ->leftJoinSub($minBimbingan, 'mb', function ($join) {
                $join->on('ns.id_mahasiswa', '=', 'mb.id_mahasiswa');
            })
            ->leftJoin('pendaftaran_skripsi as ps', 'ns.id_pendaftaran_skripsi', '=', 'ps.id')
            ->whereNull('ns.deleted_at')
            ->whereNull('up.deleted_at')
            ->when($fakultasId, function ($q) use ($fakultasId) {
                return $q->where('up.id_fakultas', $fakultasId);
            })
            ->when($programStudiId, function ($q) use ($programStudiId) {
                return $q->where('up.id_program_studi', $programStudiId);
            })
            ->whereYear('ns.tanggal_ujian', $year)
            ->whereMonth('ns.tanggal_ujian', '>=', $startMonth)
            ->whereMonth('ns.tanggal_ujian', '<=', $endMonth)
            // Lama skripsi (bulan) dihitung dari mulai bimbingan pertama; fallback ke created_at pendaftaran; fallback ke created_at nilai
            ->selectRaw("MONTH(ns.tanggal_ujian) as bulan")
            ->selectRaw("ROUND(AVG(GREATEST(0, DATEDIFF(ns.tanggal_ujian, COALESCE(mb.mulai_bimbingan, DATE(ps.created_at), DATE(ns.created_at)))) / 30), 1) as rata_bulan")
            ->groupBy('bulan')
            ->pluck('rata_bulan', 'bulan');

        return $rows->mapWithKeys(function ($value, $key) {
            return [(int) $key => (float) $value];
        })->toArray();
    }
}
