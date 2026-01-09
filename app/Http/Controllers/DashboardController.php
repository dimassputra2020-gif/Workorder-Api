<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengajuan;
use App\Models\Spk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Konstanta global untuk status
    const PENGAJUAN_STATUS_LIST = ['pending', 'approved', 'rejected'];

    const SPK_STATUS_MAP = [
        'Selesai'        => 1,
        'Belum Selesai'  => 2,
        'Tidak Selesai'  => 3,
        'menunggu'       => 4,
        'Proses'         => 5,
    ];

    const SPK_STATUS_LIST = ['menunggu', 'Proses', 'Selesai', 'Belum Selesai', 'Tidak Selesai'];

    public function index(Request $request)
    {
        $startDate = null;
        $endDate = null;
        $preset = $request->preset;

        if ($preset) {
            switch ($preset) {
                case 'today':
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek()->startOfDay();
                    $endDate = Carbon::now()->endOfWeek()->endOfDay();
                    break;
                case 'last_week':
                    $startDate = Carbon::now()->subWeek()->startOfWeek()->startOfDay();
                    $endDate = Carbon::now()->subWeek()->endOfWeek()->endOfDay();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth()->startOfDay();
                    $endDate = Carbon::now()->endOfMonth()->endOfDay();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth()->startOfDay();
                    $endDate = Carbon::now()->subMonth()->endOfMonth()->endOfDay();
                    break;
                case 'this_quarter':
                    $startDate = Carbon::now()->firstOfQuarter()->startOfDay();
                    $endDate = Carbon::now()->lastOfQuarter()->endOfDay();
                    break;
                case 'last_quarter':
                    $startDate = Carbon::now()->subQuarter()->firstOfQuarter()->startOfDay();
                    $endDate = Carbon::now()->subQuarter()->lastOfQuarter()->endOfDay();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear()->startOfDay();
                    $endDate = Carbon::now()->endOfYear()->endOfDay();
                    break;
                case 'last_year':
                    $startDate = Carbon::now()->subYear()->startOfYear()->startOfDay();
                    $endDate = Carbon::now()->subYear()->endOfYear()->endOfDay();
                    break;
            }
        } else {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)->startOfDay()
                : null;

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)->endOfDay()
                : null;
        }

        $pengajuanQuery = Pengajuan::where('is_deleted', 0);
        $spkQuery       = Spk::where('is_deleted', 0);

        if ($startDate && $endDate) {
            $pengajuanQuery->whereBetween('created_at', [$startDate, $endDate]);
            $spkQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalPengajuan = $pengajuanQuery->count();
        $totalSpk       = $spkQuery->count();
        $totalSemua     = $totalPengajuan + $totalSpk;

        $pengajuanStatusSummary = [];

        foreach (self::PENGAJUAN_STATUS_LIST as $status) {
            $p = clone $pengajuanQuery;
            $pengajuanStatusSummary[$status] = [
                'total' => $p->where('status', $status)->count(),
            ];
        }

        $spkStatusSummary = [];

        foreach (self::SPK_STATUS_LIST as $status) {
            $s = clone $spkQuery;
            $spkStatusSummary[$status] = [
                'total' => $s->where('status_id', self::SPK_STATUS_MAP[$status])->count(),
            ];
        }

        $grafikTahunan = Pengajuan::select(
            DB::raw('YEAR(created_at) as tahun'),
            DB::raw('COUNT(*) as total')
        )
            ->where('is_deleted', 0)
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        $grafikTahunanSpk = Spk::select(
            DB::raw('YEAR(created_at) as tahun'),
            DB::raw('COUNT(*) as total')
        )
            ->where('is_deleted', 0)
            ->groupBy('tahun')
            ->orderBy('tahun')
            ->get();

        $grafikBulanan = Pengajuan::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('COUNT(*) as total')
        )
            ->where('is_deleted', 0)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('bulan')
            ->get();

        $grafikBulananSpk = Spk::select(
            DB::raw('MONTH(created_at) as bulan'),
            DB::raw('COUNT(*) as total')
        )
            ->where('is_deleted', 0)
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('bulan')
            ->get();

        $bulanIni  = Carbon::now()->month;
        $bulanLalu = Carbon::now()->subMonth()->month;

        $compareDefault = [
            'pengajuan' => [
                'bulan_ini'  => Pengajuan::where('is_deleted', 0)->whereMonth('created_at', $bulanIni)->count(),
                'bulan_lalu' => Pengajuan::where('is_deleted', 0)->whereMonth('created_at', $bulanLalu)->count(),
            ],
            'spk' => [
                'bulan_ini'  => Spk::where('is_deleted', 0)->whereMonth('created_at', $bulanIni)->count(),
                'bulan_lalu' => Spk::where('is_deleted', 0)->whereMonth('created_at', $bulanLalu)->count(),
            ],
        ];

        $compareDefault['pengajuan']['selisih'] =
            $compareDefault['pengajuan']['bulan_ini'] -
            $compareDefault['pengajuan']['bulan_lalu'];

        $compareDefault['spk']['selisih'] =
            $compareDefault['spk']['bulan_ini'] -
            $compareDefault['spk']['bulan_lalu'];

        $compareCustom = null;

        if ($startDate && $endDate) {
            $selisihHari = $startDate->diffInDays($endDate) + 1;
            $prevStart = $startDate->copy()->subDays($selisihHari);
            $prevEnd   = $startDate->copy()->subDay();

            $currentPengajuan = Pengajuan::where('is_deleted', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $prevPengajuan = Pengajuan::where('is_deleted', 0)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();

            $currentSpk = Spk::where('is_deleted', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $prevSpk = Spk::where('is_deleted', 0)
                ->whereBetween('created_at', [$prevStart, $prevEnd])
                ->count();

            $pengajuanStatusCurrent = [];
            $pengajuanStatusPrev    = [];

            foreach (self::PENGAJUAN_STATUS_LIST as $status) {
                $pengajuanStatusCurrent[$status] = Pengajuan::where('is_deleted', 0)
                    ->where('status', $status)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $pengajuanStatusPrev[$status] = Pengajuan::where('is_deleted', 0)
                    ->where('status', $status)
                    ->whereBetween('created_at', [$prevStart, $prevEnd])
                    ->count();
            }

            $spkStatusCurrent = [];
            $spkStatusPrev    = [];

            foreach (self::SPK_STATUS_LIST as $status) {
                $statusId = self::SPK_STATUS_MAP[$status];

                $spkStatusCurrent[$status] = Spk::where('is_deleted', 0)
                    ->where('status_id', $statusId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $spkStatusPrev[$status] = Spk::where('is_deleted', 0)
                    ->where('status_id', $statusId)
                    ->whereBetween('created_at', [$prevStart, $prevEnd])
                    ->count();
            }

            $compareCustom = [
                'rentang_sekarang' => [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ],
                'rentang_sebelumnya' => [
                    'start_date' => $prevStart,
                    'end_date'   => $prevEnd,
                ],

                'data_pengajuan' => [
                    'total_sekarang' => $currentPengajuan,
                    'total_sebelumnya' => $prevPengajuan,
                    'selisih' => $currentPengajuan - $prevPengajuan,

                    'status' => [
                        'sekarang' => $pengajuanStatusCurrent,
                        'sebelumnya' => $pengajuanStatusPrev,
                    ],
                ],

                'data_spk' => [
                    'total_sekarang' => $currentSpk,
                    'total_sebelumnya' => $prevSpk,
                    'selisih' => $currentSpk - $prevSpk,

                    'status' => [
                        'sekarang' => $spkStatusCurrent,
                        'sebelumnya' => $spkStatusPrev,
                    ],
                ],
            ];
        }

        return response()->json([
            'success' => true,
            'filter' => [
                'active' => ($startDate && $endDate) ? true : false,
                'start_date' => $startDate ? $startDate->toDateString() : null,
                'end_date' => $endDate ? $endDate->toDateString() : null,
                'preset' => $preset ?? null,
            ],
            'total' => [
                'pengajuan' => $totalPengajuan,
                'spk' => $totalSpk,
                'semua' => $totalSemua
            ],
            'status' => [
                'pengajuan' => $pengajuanStatusSummary,
                'spk'       => $spkStatusSummary,
            ],
            'grafik_tahunan' => [
                'pengajuan' => $grafikTahunan,
                'spk' => $grafikTahunanSpk,
            ],
            'grafik_bulanan' => [
                'pengajuan' => $grafikBulanan,
                'spk' => $grafikBulananSpk,
            ],
            'perbandingan_default' => $compareDefault,
            'perbandingan_custom'  => $compareCustom,
        ]);
    }
}
