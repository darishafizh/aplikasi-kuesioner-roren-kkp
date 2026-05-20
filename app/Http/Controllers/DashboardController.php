<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\InformasiResponden;
use App\Models\TingkatKebahagiaanNelayan;
use App\Models\TargetRealisasi;
use App\Models\InformasiPendapatanRumahTangga;
use App\Models\SosialKelembagaan;
use App\Models\ProfileKnmp;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Models\Knmp;
use App\Models\ProgresHarian;
use App\Models\TanggapanMasyarakat;
use App\Models\TahapKonstruksi;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter periode waktu
        $period = $request->get('period', 'all');
        $periodLabel = match ($period) {
            'week' => 'Minggu Ini',
            'month' => 'Bulan Ini',
            'year' => 'Tahun Ini',
            default => 'Semua Waktu',
        };

        // Tentukan tanggal mulai berdasarkan filter
        $startDate = match ($period) {
            'week' => Carbon::now('Asia/Jakarta')->startOfWeek(),
            'month' => Carbon::now('Asia/Jakarta')->startOfMonth(),
            'year' => Carbon::now('Asia/Jakarta')->startOfYear(),
            default => null,
        };

        // Ucapan selamat berdasarkan waktu Asia/Jakarta
        $currentHour = Carbon::now('Asia/Jakarta')->hour;
        if ($currentHour >= 5 && $currentHour < 11) {
            $greeting = 'Selamat Pagi';
            $greetingIcon = 'mdi-weather-sunny';
        } elseif ($currentHour >= 11 && $currentHour < 15) {
            $greeting = 'Selamat Siang';
            $greetingIcon = 'mdi-white-balance-sunny';
        } elseif ($currentHour >= 15 && $currentHour < 18) {
            $greeting = 'Selamat Sore';
            $greetingIcon = 'mdi-weather-sunset';
        } else {
            $greeting = 'Selamat Malam';
            $greetingIcon = 'mdi-weather-night';
        }

        // Filter tahap (sekarang menggunakan batch_id)
        $tahapReq = $request->get('tahap');
        $tahunReq = $request->get('tahun', session('selected_tahun'));

        // Scope availableTahap
        $availableTahapQuery = \App\Models\Batch::orderBy('id');
        if ($tahunReq) {
            $availableTahapQuery->where('tahun', $tahunReq);
        }
        $availableTahap = $availableTahapQuery->get();

        $tahap = $tahapReq !== null ? $tahapReq : 'all';

        $tahapLabel = 'Semua Tahap' . ($tahunReq ? ' Tahun ' . $tahunReq : '');
        if ($tahap !== 'all') {
            $batchObj = \App\Models\Batch::find($tahap);
            $tahapLabel = $batchObj ? $batchObj->nama_tahap . ' - ' . $batchObj->tahun : 'Tahap ' . $tahap;
        }

        $desa_knmp_query = Knmp::with([
            'profileKnmp',
            'progresKnmp',
            'latestProgresNasional',
        ])
            ->withCount('informasiResponden');

        if ($tahap !== 'all') {
            $desa_knmp_query->where('batch_id', $tahap);
        } elseif ($tahunReq) {
            $batchIds = $availableTahap->pluck('id');
            $desa_knmp_query->whereIn('batch_id', $batchIds);
        }

        $desa_knmp = $desa_knmp_query->get();
        foreach ($desa_knmp as $item) {
            if ($item->tahap_saat_ini === 'serah_terima') {
                if (!$item->relationLoaded('latestProgresNasional') || !$item->latestProgresNasional) {
                    $item->setRelation('latestProgresNasional', new \App\Models\ProgresHarian(['progres' => 100.0]));
                } else {
                    $item->latestProgresNasional->progres = 100.0;
                }
            }
        }
        $knmpIds = $desa_knmp->pluck('id')->toArray();

        // --- GET AVAILABLE DATES EARLY ---
        $availableProgressDates = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
            ->whereIn('kk.knmp_id', $knmpIds)
            ->selectRaw('DISTINCT progres_harian.tanggal')
            ->whereNotNull('progres_harian.tanggal')
            ->orderBy('progres_harian.tanggal', 'desc')
            ->pluck('tanggal')
            ->toArray();

        $selectedProgresDate = $request->get('progres_date');
        if (!$selectedProgresDate && count($availableProgressDates) > 0) {
            $selectedProgresDate = $availableProgressDates[0];
        }

        // 1. Hitung responden yang telah mengisi survey
        $surveyQuery = InformasiResponden::query()
            ->whereIn('knmp_id', $knmpIds)
            ->whereExists(function ($query) use ($selectedProgresDate) {
                $query->select(DB::raw(1))
                    ->from('tingkat_kebahagiaan_nelayan')
                    ->whereColumn('tingkat_kebahagiaan_nelayan.responden_id', 'informasi_responden.id');
                if ($selectedProgresDate) {
                    $query->whereDate('tingkat_kebahagiaan_nelayan.created_at', '<=', $selectedProgresDate);
                }
            });

        if ($startDate) {
            $surveyQuery->where('created_at', '>=', $startDate);
        }
        if ($selectedProgresDate) {
            $surveyQuery->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $totalSurveyTerisi = $surveyQuery->distinct('id')->count();

        // 2. Hitung Tingkat Kelengkapan Data
        $totalRespondenQuery = InformasiResponden::whereIn('knmp_id', $knmpIds);
        if ($selectedProgresDate) {
            $totalRespondenQuery->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $totalResponden = $totalRespondenQuery->count();

        $responden_dengan_data_query = InformasiResponden::whereIn('knmp_id', $knmpIds)->where(function ($query) use ($selectedProgresDate) {
            $query->whereHas('tingkatKebahagiaan', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('tanggapanMasyarakat', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('informasiUsaha', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('informasiPemasaran', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('pendapatanRt', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('sosialKelembagaan', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            });
        });
        if ($selectedProgresDate) {
            $responden_dengan_data_query->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $responden_dengan_data = $responden_dengan_data_query->count();

        $tingkatKelengkapanData = $totalResponden > 0
            ? round(($responden_dengan_data / $totalResponden) * 100, 2)
            : 0;

        // 3. Hitung Rata-rata Capaian Indikator
        $capaianIndikatorQuery = DB::table('progres_knmp_details')
            ->whereIn('progres_id', function ($q) use ($knmpIds, $selectedProgresDate) {
                $q->select('id')->from('progres_knmp')->whereIn('knmp_id', $knmpIds);
                if ($selectedProgresDate) {
                    $q->whereDate('created_at', '<=', $selectedProgresDate);
                }
            });
        if ($selectedProgresDate) {
            $capaianIndikatorQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $capaianIndikator = $capaianIndikatorQuery->avg('persen') ?? 0;

        // 4. Hitung Rata-rata Indeks Kebahagiaan Nelayan
        $rataRataKebahagiaanQuery = TingkatKebahagiaanNelayan::whereHas('responden', function ($q) use ($knmpIds, $selectedProgresDate) {
            $q->whereIn('knmp_id', $knmpIds);
            if ($selectedProgresDate) {
                $q->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
            }
        });
        if ($selectedProgresDate) {
            $rataRataKebahagiaanQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $rataRataKebahagiaan = $rataRataKebahagiaanQuery->avg('skor_nilai') ?? 0;

        // ===================================
        // DATA UNTUK STATISTIK NASIONAL
        // ===================================

        $deltaPeriod = $request->get('delta_period', 'latest');
        
        // Determine previous date for delta calculation
        $previousDate = null;
        if ($deltaPeriod === 'weekly') {
            $targetWeeklyDate = \Carbon\Carbon::parse($selectedProgresDate)->subDays(7)->format('Y-m-d');
            foreach ($availableProgressDates as $date) {
                if ($date <= $targetWeeklyDate) {
                    $previousDate = $date;
                    break;
                }
            }
        } else {
            $currentIndex = array_search($selectedProgresDate, $availableProgressDates);
            if ($currentIndex !== false && isset($availableProgressDates[$currentIndex + 1])) {
                $previousDate = $availableProgressDates[$currentIndex + 1];
            }
        }

        // Data for Trend Line Chart
        $trendDataQuery = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
            ->whereIn('kk.knmp_id', $knmpIds);
        
        if ($selectedProgresDate) {
            $trendDataQuery->where('progres_harian.tanggal', '<=', $selectedProgresDate);
        }

        $trendDataQuery = $trendDataQuery->select('progres_harian.tanggal', DB::raw('AVG(progres_harian.progres) as avg_progres'))
            ->groupBy('progres_harian.tanggal')
            ->orderBy('progres_harian.tanggal', 'asc')
            ->get();

        $trendDates = $trendDataQuery->pluck('tanggal')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d M y'))->toArray();
        $trendAverages = $trendDataQuery->pluck('avg_progres')->map(fn($val) => round($val, 2))->toArray();

        // Query progres data stage-aware (including completed/handed-over stages like Tahap I)
        $progresNasional = DB::table('knmp as k')
            ->leftJoin('konstruksi_knmp as kk', 'kk.knmp_id', '=', 'k.id')
            ->leftJoin('batch as b', 'k.batch_id', '=', 'b.id')
            ->leftJoin('penyedia_jasa_konstruksi as pj', 'kk.jasa_konstruksi_id', '=', 'pj.id')
            ->whereIn('k.id', $knmpIds)
            ->select(
                'kk.id as knmp_konstruksi_id',
                'k.id as knmp_id',
                'k.nama as knmp_nama',
                'k.kabupaten',
                'k.provinsi',
                'k.kecamatan',
                'k.tahap_saat_ini',
                'k.batch_id',
                'b.nama_tahap as batch_nama',
                'kk.tanggal_mulai',
                'pj.nama as nama_jasa_konstruksi'
            )
            ->get();

        $latestProgres = [];
        if ($progresNasional->count() > 0) {
            $validKonstruksiIds = $progresNasional->pluck('knmp_konstruksi_id')->filter()->toArray();
            if (count($validKonstruksiIds) > 0) {
                $latestPhSub = DB::table('progres_harian as ph2')
                    ->select('ph2.knmp_konstruksi_id', DB::raw('MAX(ph2.id) as max_id'))
                    ->whereIn('ph2.knmp_konstruksi_id', $validKonstruksiIds);
                if ($selectedProgresDate) {
                    $latestPhSub->where('ph2.tanggal', '<=', $selectedProgresDate);
                }
                $latestPhSub->groupBy('ph2.knmp_konstruksi_id');

                $latestProgres = DB::table('progres_harian as ph')
                    ->joinSub($latestPhSub, 'max_ids', function ($join) {
                        $join->on('ph.id', '=', 'max_ids.max_id');
                    })
                    ->select('ph.*')
                    ->get()
                    ->keyBy('knmp_konstruksi_id');
            }
        }

        $previousProgresData = [];
        if ($previousDate && $progresNasional->count() > 0) {
            $validKonstruksiIds = $progresNasional->pluck('knmp_konstruksi_id')->filter()->toArray();
            if (count($validKonstruksiIds) > 0) {
                $prevPhSub = DB::table('progres_harian as ph2')
                    ->select('ph2.knmp_konstruksi_id', DB::raw('MAX(ph2.id) as max_id'))
                    ->whereIn('ph2.knmp_konstruksi_id', $validKonstruksiIds)
                    ->where('ph2.tanggal', '<=', $previousDate)
                    ->groupBy('ph2.knmp_konstruksi_id');

                $previousProgresData = DB::table('progres_harian as ph')
                    ->joinSub($prevPhSub, 'max_ids', function ($join) {
                        $join->on('ph.id', '=', 'max_ids.max_id');
                    })
                    ->pluck('ph.progres', 'ph.knmp_konstruksi_id')
                    ->toArray();
            }
        }

        foreach ($progresNasional as $item) {
            $ph = null;
            if ($item->knmp_konstruksi_id) {
                $ph = $latestProgres[$item->knmp_konstruksi_id] ?? null;
            }
            $item->id = $ph ? $ph->id : null;
            $item->tanggal = $ph ? $ph->tanggal : null;
            $item->keterangan = $ph ? $ph->keterangan : null;
            $item->created_at = $ph ? $ph->created_at : null;
            $item->updated_at = $ph ? $ph->updated_at : null;

            $progresVal = $ph ? (float)$ph->progres : 0.0;
            if ($item->tahap_saat_ini === 'serah_terima') {
                $progresVal = 100.0;
                $item->keterangan = $item->keterangan ?? 'Selesai';
            }
            $item->progres = $progresVal;

            $prevProgres = 0.0;
            if ($item->tahap_saat_ini === 'serah_terima') {
                $prevProgres = 100.0;
            } else {
                $prevProgres = ($item->knmp_konstruksi_id && isset($previousProgresData[$item->knmp_konstruksi_id]))
                    ? (float)$previousProgresData[$item->knmp_konstruksi_id]
                    : $progresVal;
            }
            $item->delta = $progresVal - $prevProgres;
        }

        $progresNasional = $progresNasional->sortByDesc('progres')->values();
        $progresNasionalAvg = $progresNasional->avg('progres') ?? 0;

        // ===================================
        // ANALISIS PROGRES KNMP
        // ===================================

        // 1. Sebaran persentase progres (5 kriteria)
        $sebaranProgres = [
            '0-20%' => 0,
            '21-40%' => 0,
            '41-60%' => 0,
            '61-80%' => 0,
            '81-100%' => 0,
        ];
        foreach ($progresNasional as $item) {
            $p = $item->progres;
            if ($p <= 20) $sebaranProgres['0-20%']++;
            elseif ($p <= 40) $sebaranProgres['21-40%']++;
            elseif ($p <= 60) $sebaranProgres['41-60%']++;
            elseif ($p <= 80) $sebaranProgres['61-80%']++;
            else $sebaranProgres['81-100%']++;
        }

        // Hitung deviasi secara dinamis berdasarkan tanggal_mulai
        $deviasiData = [];
        if (!$progresNasional->isEmpty()) {
            // Ambil semua data konstruksi dan timeline
            $allKonstruksi = DB::table('konstruksi_knmp')
                ->whereIn('knmp_id', $knmpIds)
                ->get()
                ->keyBy('knmp_id');

            $allTimelines = DB::table('tahap_konstruksi')
                ->whereIn('knmp_konstruksi_id', $allKonstruksi->pluck('id'))
                ->orderBy('periode_mingguan')
                ->get()
                ->groupBy('knmp_konstruksi_id');

            // Tanggal acuan: gunakan tanggal saat ini untuk menentukan minggu keberapa
            $todayDate = $selectedProgresDate ? \Carbon\Carbon::parse($selectedProgresDate) : \Carbon\Carbon::now();

            foreach ($progresNasional as $item) {
                $kId = $item->knmp_id;
                $progresAktual = (float)$item->progres;
                
                $konstruksi = $allKonstruksi->get($kId);
                if (!$konstruksi) {
                    $deviasiData[$kId] = 0;
                    continue;
                }

                $timelines = $allTimelines->get($konstruksi->id) ?? collect();
                $startDate = $konstruksi->tanggal_mulai ? \Carbon\Carbon::parse($konstruksi->tanggal_mulai) : null;

                if (!$startDate || $timelines->isEmpty()) {
                    $rencanaRaw = 0;
                } else {
                    // Hitung minggu saat ini: dari tanggal_mulai ke tanggal hari ini
                    $diffDays = $startDate->diffInDays($todayDate, false);
                    $currentWeek = max(1, (int) floor($diffDays / 7) + 1);
                    
                    // Cari bobot rencana kumulatif untuk minggu tersebut
                    $tlRencana = $timelines->where('periode_mingguan', $currentWeek)->first();
                    if (!$tlRencana) {
                        // Jika sudah lewat total minggu, gunakan minggu terakhir
                        $tlRencana = $timelines->sortByDesc('periode_mingguan')->first();
                    }
                    $rencanaRaw = $tlRencana ? $tlRencana->bobot_rencana_kumulatif : 0;
                }
                
                // Simpan data rencana pada item
                $item->rencana_kumulatif = $rencanaRaw;

                // Normalisasi jika data rencana menggunakan skala permil (max > 100)
                $maxPlan = $timelines->max('bobot_rencana_kumulatif') ?? 100;
                $scale = $maxPlan > 100 ? $maxPlan / 100 : 1;
                $rencanaPersen = round((float)$rencanaRaw / $scale, 2);
                
                // Deviasi = progres terbaru - rencana di minggu saat ini
                $deviasiData[$kId] = round($progresAktual - $rencanaPersen, 2);
            }
        }

        foreach ($progresNasional as $item) {
            $item->deviasi = $deviasiData[$item->knmp_id] ?? 0;
        }

        // Filter hanya yang progresnya < 100 untuk list performa
        $activeProgresNasional = $progresNasional->filter(function($item) {
            return (float)$item->progres < 100;
        });

        // 2. Performa 10 KNMP tertinggi deviasinya (deviasi >= 0)
        $top10Knmp = $activeProgresNasional
            ->filter(function($item) {
                return (float)$item->deviasi >= 0;
            })
            ->sortByDesc('deviasi')
            ->take(10)
            ->values();

        // 3. Performa 10 KNMP terendah deviasinya (deviasi < 0)
        $bottom10Knmp = $activeProgresNasional
            ->filter(function($item) {
                return (float)$item->deviasi < 0;
            })
            ->sortBy('deviasi')
            ->take(10)
            ->values();

        // Cek status penyelesaian per tahap (apakah semua 100%)
        $tahapSelesaiStatus = $progresNasional->groupBy('batch_id')->map(function ($items) {
            return $items->count() > 0 && $items->every(function ($item) {
                return (float)$item->progres >= 100;
            });
        });

        // Critical alert (stagnan 5 hari) — hanya berlaku untuk progres < 100
        $fiveDaysAgo = \Carbon\Carbon::parse($selectedProgresDate)->subDays(5)->format('Y-m-d');

        $top10Ids = $top10Knmp->pluck('knmp_id')->toArray();
        $bottom10Ids = $bottom10Knmp->pluck('knmp_id')->toArray();
        $allCheckIds = array_unique(array_merge($top10Ids, $bottom10Ids));

        $pastProgresData = collect();
        if (count($allCheckIds) > 0) {
            $pastProgresData = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
                ->whereIn('kk.knmp_id', $allCheckIds)
                ->where('progres_harian.tanggal', '<=', $fiveDaysAgo)
                ->orderBy('progres_harian.tanggal', 'desc')
                ->get(['progres_harian.*', 'kk.knmp_id'])
                ->groupBy('knmp_id')
                ->map(function ($items) {
                    return $items->first();
                });

        }

        $applyStagnanFlag = function ($collection) use ($pastProgresData) {
            foreach ($collection as $item) {
                $item->is_stagnan = false;
                $item->past_progres = null;
                $item->past_progres_date = null;
                $item->is_complete = ((float) $item->progres) >= 100;

                $past = $pastProgresData[$item->knmp_id] ?? null;
                if ($past !== null) {
                    $item->past_progres = $past->progres;
                    $item->past_progres_date = $past->tanggal;
                    if (! $item->is_complete && (float) $item->progres == (float) $past->progres) {
                        $item->is_stagnan = true;
                    }
                }
            }
        };
        $applyStagnanFlag($top10Knmp);
        $applyStagnanFlag($bottom10Knmp);

        // ===================================
        // NEW KPI CALCULATIONS
        // ===================================

        // 1. Total KNMP (sudah ada: count($desa_knmp))
        $totalKnmp = count($desa_knmp);

        // Progress Nasional
        $totalKnmpNasional = count($desa_knmp);
        $targetKnmp = 100; // Target KNMP Nasional (dapat disesuaikan)
        $progressNasional = $targetKnmp > 0 ? min(round(($totalKnmpNasional / $targetKnmp) * 100, 1), 100) : 0;

        // Total Tenaga Kerja Terserap
        $totalTenagaKerjaQuery = DB::table('progres_knmp')->whereIn('knmp_id', $knmpIds);
        if ($selectedProgresDate) {
            $totalTenagaKerjaQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $totalTenagaKerja = $totalTenagaKerjaQuery->sum('tk_total') ?? 0;


        // Available tahap values for filter (already scoped at the top)


        return view('dashboard.index', compact(
            'greeting',
            'greetingIcon',
            'period',
            'periodLabel',
            'tahap',
            'tahapLabel',
            'availableTahap',
            'desa_knmp',
            'totalSurveyTerisi',
            'tingkatKelengkapanData',
            'capaianIndikator',
            'rataRataKebahagiaan',
            // Data statistik nasional
            'progressNasional',
            'totalTenagaKerja',
            'totalKnmpNasional',
            'targetKnmp',
            // Data progres nasional dan kopdeskel
            'progresNasional',
            'progresNasionalAvg',
            'availableProgressDates',
            'selectedProgresDate',
            'deltaPeriod',
            'trendDates',
            'trendAverages',
            // New KPI data
            'totalKnmp',
            // Analisis Progres
            'sebaranProgres',
            'top10Knmp',
            'bottom10Knmp',
            'tahapSelesaiStatus',
        ));
    }

    /**
     * API endpoint for real-time dashboard data (AJAX polling)
     */
    public function apiData(Request $request)
    {
        $period = $request->get('period', 'all');
        $startDate = match ($period) {
            'week' => Carbon::now('Asia/Jakarta')->startOfWeek(),
            'month' => Carbon::now('Asia/Jakarta')->startOfMonth(),
            'year' => Carbon::now('Asia/Jakarta')->startOfYear(),
            default => null,
        };

        $tahapReq = $request->get('tahap');
        $tahunReq = $request->get('tahun', session('selected_tahun'));

        $tahap = $tahapReq !== null ? $tahapReq : 'all';

        $desa_knmp_query = Knmp::with([
            'profileKnmp',
            'progresKnmp',
            'latestProgresNasional',
        ])->withCount('informasiResponden');

        if ($tahap !== 'all') {
            $desa_knmp_query->where('batch_id', $tahap);
        } elseif ($tahunReq) {
            $batchIds = \App\Models\Batch::where('tahun', $tahunReq)->pluck('id');
            $desa_knmp_query->whereIn('batch_id', $batchIds);
        }

        $desa_knmp = $desa_knmp_query->get();
        $knmpIds = $desa_knmp->pluck('id')->toArray();
        $respondenIds = InformasiResponden::whereIn('knmp_id', $knmpIds)->pluck('id')->toArray();

        // Get available dates early
        $availableProgressDates = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
            ->whereIn('kk.knmp_id', $knmpIds)
            ->selectRaw('DISTINCT progres_harian.tanggal')
            ->whereNotNull('progres_harian.tanggal')
            ->orderBy('progres_harian.tanggal', 'desc')
            ->pluck('tanggal')
            ->toArray();

        // Progres KNMP Nasional
        $selectedProgresDate = $request->get('progres_date');
        if (!$selectedProgresDate && count($availableProgressDates) > 0) {
            $selectedProgresDate = $availableProgressDates[0];
        }

        // 1. Responden yang telah mengisi survey
        $surveyQuery = InformasiResponden::query()
            ->whereIn('knmp_id', $knmpIds)
            ->whereExists(function ($query) use ($selectedProgresDate) {
                $query->select(DB::raw(1))
                    ->from('tingkat_kebahagiaan_nelayan')
                    ->whereColumn('tingkat_kebahagiaan_nelayan.responden_id', 'informasi_responden.id');
                if ($selectedProgresDate) {
                    $query->whereDate('tingkat_kebahagiaan_nelayan.created_at', '<=', $selectedProgresDate);
                }
            });

        if ($startDate) {
            $surveyQuery->where('created_at', '>=', $startDate);
        }
        if ($selectedProgresDate) {
            $surveyQuery->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $totalSurveyTerisi = $surveyQuery->distinct('id')->count();

        // 2. Hitung Tingkat Kelengkapan Data
        $totalRespondenQuery = InformasiResponden::whereIn('knmp_id', $knmpIds);
        if ($selectedProgresDate) {
            $totalRespondenQuery->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $totalResponden = $totalRespondenQuery->count();

        $responden_dengan_data_query = InformasiResponden::whereIn('knmp_id', $knmpIds)->where(function ($query) use ($selectedProgresDate) {
            $query->whereHas('tingkatKebahagiaan', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('tanggapanMasyarakat', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('informasiUsaha', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('informasiPemasaran', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('pendapatanRt', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            })
            ->orWhereHas('sosialKelembagaan', function($q) use ($selectedProgresDate) {
                if ($selectedProgresDate) $q->whereDate('created_at', '<=', $selectedProgresDate);
            });
        });
        if ($selectedProgresDate) {
            $responden_dengan_data_query->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
        }
        $responden_dengan_data = $responden_dengan_data_query->count();

        $tingkatKelengkapanData = $totalResponden > 0
            ? round(($responden_dengan_data / $totalResponden) * 100, 2) : 0;

        // 3. Rata-rata Capaian Indikator
        $capaianIndikatorQuery = DB::table('progres_knmp_details')
            ->whereIn('progres_id', function ($q) use ($knmpIds, $selectedProgresDate) {
                $q->select('id')->from('progres_knmp')->whereIn('knmp_id', $knmpIds);
                if ($selectedProgresDate) {
                    $q->whereDate('created_at', '<=', $selectedProgresDate);
                }
            });
        if ($selectedProgresDate) {
            $capaianIndikatorQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $capaianIndikator = $capaianIndikatorQuery->avg('persen') ?? 0;

        // 4. Rata-rata Indeks Kebahagiaan
        $rataRataKebahagiaanQuery = TingkatKebahagiaanNelayan::whereHas('responden', function ($q) use ($knmpIds, $selectedProgresDate) {
            $q->whereIn('knmp_id', $knmpIds);
            if ($selectedProgresDate) {
                $q->whereDate('tanggal_wawancara', '<=', $selectedProgresDate);
            }
        });
        if ($selectedProgresDate) {
            $rataRataKebahagiaanQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $rataRataKebahagiaan = $rataRataKebahagiaanQuery->avg('skor_nilai') ?? 0;


        // Query progres data stage-aware (including completed/handed-over stages like Tahap I)
        // Query progres data stage-aware (including completed/handed-over stages like Tahap I)
        $progresNasional = DB::table('knmp as k')
            ->leftJoin('konstruksi_knmp as kk', 'kk.knmp_id', '=', 'k.id')
            ->leftJoin('batch as b', 'k.batch_id', '=', 'b.id')
            ->leftJoin('penyedia_jasa_konstruksi as pj', 'kk.jasa_konstruksi_id', '=', 'pj.id')
            ->whereIn('k.id', $knmpIds)
            ->select(
                'kk.id as knmp_konstruksi_id',
                'k.id as knmp_id',
                'k.nama as knmp_nama',
                'k.kabupaten',
                'k.provinsi',
                'k.kecamatan',
                'k.tahap_saat_ini',
                'k.batch_id',
                'b.nama_tahap as batch_nama',
                'kk.tanggal_mulai',
                'pj.nama as nama_jasa_konstruksi'
            )
            ->get();

        $latestProgres = [];
        if ($progresNasional->count() > 0) {
            $validKonstruksiIds = $progresNasional->pluck('knmp_konstruksi_id')->filter()->toArray();
            if (count($validKonstruksiIds) > 0) {
                $latestPhSub = DB::table('progres_harian as ph2')
                    ->select('ph2.knmp_konstruksi_id', DB::raw('MAX(ph2.id) as max_id'))
                    ->whereIn('ph2.knmp_konstruksi_id', $validKonstruksiIds);
                if ($selectedProgresDate) {
                    $latestPhSub->where('ph2.tanggal', '<=', $selectedProgresDate);
                }
                $latestPhSub->groupBy('ph2.knmp_konstruksi_id');

                $latestProgres = DB::table('progres_harian as ph')
                    ->joinSub($latestPhSub, 'max_ids', function ($join) {
                        $join->on('ph.id', '=', 'max_ids.max_id');
                    })
                    ->select('ph.*')
                    ->get()
                    ->keyBy('knmp_konstruksi_id');
            }
        }

        $availableProgressDates = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
            ->whereIn('kk.knmp_id', $knmpIds)
            ->selectRaw('DISTINCT progres_harian.tanggal')
            ->whereNotNull('progres_harian.tanggal')
            ->orderBy('progres_harian.tanggal', 'desc')
            ->pluck('tanggal')
            ->toArray();

        $previousDate = null;
        $currentIndex = array_search($selectedProgresDate, $availableProgressDates);
        if ($currentIndex !== false && isset($availableProgressDates[$currentIndex + 1])) {
            $previousDate = $availableProgressDates[$currentIndex + 1];
        }

        $previousProgresData = [];
        if ($previousDate && $progresNasional->count() > 0) {
            $validKonstruksiIds = $progresNasional->pluck('knmp_konstruksi_id')->filter()->toArray();
            if (count($validKonstruksiIds) > 0) {
                $prevPhSub = DB::table('progres_harian as ph2')
                    ->select('ph2.knmp_konstruksi_id', DB::raw('MAX(ph2.id) as max_id'))
                    ->whereIn('ph2.knmp_konstruksi_id', $validKonstruksiIds)
                    ->where('ph2.tanggal', '<=', $previousDate)
                    ->groupBy('ph2.knmp_konstruksi_id');

                $previousProgresData = DB::table('progres_harian as ph')
                    ->joinSub($prevPhSub, 'max_ids', function ($join) {
                        $join->on('ph.id', '=', 'max_ids.max_id');
                    })
                    ->pluck('ph.progres', 'ph.knmp_konstruksi_id')
                    ->toArray();
            }
        }

        foreach ($progresNasional as $item) {
            $ph = null;
            if ($item->knmp_konstruksi_id) {
                $ph = $latestProgres[$item->knmp_konstruksi_id] ?? null;
            }
            $item->id = $ph ? $ph->id : null;
            $item->tanggal = $ph ? $ph->tanggal : null;
            $item->keterangan = $ph ? $ph->keterangan : null;
            $item->created_at = $ph ? $ph->created_at : null;
            $item->updated_at = $ph ? $ph->updated_at : null;

            $progresVal = $ph ? (float)$ph->progres : 0.0;
            if ($item->tahap_saat_ini === 'serah_terima') {
                $progresVal = 100.0;
                $item->keterangan = $item->keterangan ?? 'Selesai';
            }
            $item->progres = $progresVal;

            $prevProgres = 0.0;
            if ($item->tahap_saat_ini === 'serah_terima') {
                $prevProgres = 100.0;
            } else {
                $prevProgres = ($item->knmp_konstruksi_id && isset($previousProgresData[$item->knmp_konstruksi_id]))
                    ? (float)$previousProgresData[$item->knmp_konstruksi_id]
                    : $progresVal;
            }
            $item->delta = $progresVal - $prevProgres;
        }

        $progresNasional = $progresNasional->sortByDesc('progres')->values();
        $progresNasionalAvg = count($progresNasional) > 0 ? $progresNasional->avg('progres') : 0;

        // KPI Calculations
        $totalKnmp = count($desa_knmp);        // Hitung deviasi secara dinamis berdasarkan tanggal_mulai
        $deviasiData = [];
        if (!$progresNasional->isEmpty()) {
            // Ambil semua data konstruksi dan timeline
            $allKonstruksi = DB::table('konstruksi_knmp')
                ->whereIn('knmp_id', $knmpIds)
                ->get()
                ->keyBy('knmp_id');

            $allTimelines = DB::table('tahap_konstruksi')
                ->whereIn('knmp_konstruksi_id', $allKonstruksi->pluck('id'))
                ->orderBy('periode_mingguan')
                ->get()
                ->groupBy('knmp_konstruksi_id');

            $progresDateObj = $selectedProgresDate ? \Carbon\Carbon::parse($selectedProgresDate) : \Carbon\Carbon::now();

            foreach ($progresNasional as $item) {
                $kId = $item->knmp_id;
                $progresAktual = (float)$item->progres;
                
                $konstruksi = $allKonstruksi->get($kId);
                if (!$konstruksi) {
                    $deviasiData[$kId] = 0;
                    continue;
                }

                $timelines = $allTimelines->get($konstruksi->id) ?? collect();
                $startDate = $konstruksi->tanggal_mulai ? \Carbon\Carbon::parse($konstruksi->tanggal_mulai) : null;

                if (!$startDate || $timelines->isEmpty()) {
                    $rencanaRaw = 0;
                } else {
                    $diffDays = $startDate->diffInDays($progresDateObj, false);
                    $currentWeek = $diffDays < 0 ? 1 : (int) floor($diffDays / 7) + 1;
                    $tlRencana = $timelines->where('periode_mingguan', $currentWeek)->first();
                    if (!$tlRencana) {
                        $tlRencana = $timelines->sortByDesc('periode_mingguan')->first();
                    }
                    $rencanaRaw = $tlRencana ? $tlRencana->bobot_rencana_kumulatif : 0;
                }
                
                $item->rencana_kumulatif = $rencanaRaw;

                $maxPlan = $timelines->max('bobot_rencana_kumulatif') ?? 100;
                $scale = $maxPlan > 100 ? $maxPlan / 100 : 1;
                $rencanaPersen = (float)$rencanaRaw / $scale;
                
                $deviasiData[$kId] = round($progresAktual - $rencanaPersen, 2);
            }
        }

        foreach ($progresNasional as $item) {
            $item->deviasi = $deviasiData[$item->knmp_id] ?? 0;
        }

        // Available tahap values for filter
        $availableTahap = \App\Models\Batch::orderBy('id')->get();

        // Ketersediaan Infrastruktur
        $infraColumns = [
            'infra_jalan_akses', 'infra_listrik', 'infra_air_bersih', 'infra_internet',
            'infra_ipal', 'infra_dermaga_tambat', 'infra_tpi', 'infra_cold_storage',
            'infra_pabrik_es', 'infra_kantor_koperasi', 'infra_bengkel_nelayan', 'infra_waserda'
        ];
        $profilesQuery = ProfileKnmp::select($infraColumns)->whereIn('knmp_id', $knmpIds);
        if ($selectedProgresDate) {
            $profilesQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $profiles = $profilesQuery->get();
        $totalPercentage = 0;
        $countProfiles = $profiles->count();
        foreach ($profiles as $profile) {
            $filledCount = 0;
            foreach ($infraColumns as $col) {
                if ($profile->$col) $filledCount++;
            }
            $totalPercentage += ($filledCount / 12) * 100;
        }
        $ketersediaanInfrastruktur = $countProfiles > 0 ? round($totalPercentage / $countProfiles, 2) : 0;
 
        // Indeks Kesesuaian Kebutuhan
        $totalTanggapanQuery = TanggapanMasyarakat::whereIn('responden_id', $respondenIds);
        $sesuaiKebutuhanQuery = TanggapanMasyarakat::whereIn('responden_id', $respondenIds)->where('kesesuaian_kebutuhan', 1);
        if ($selectedProgresDate) {
            $totalTanggapanQuery->whereDate('created_at', '<=', $selectedProgresDate);
            $sesuaiKebutuhanQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $totalTanggapan = $totalTanggapanQuery->count();
        $sesuaiKebutuhan = $sesuaiKebutuhanQuery->count();
        $indeksKesesuaianKebutuhan = $totalTanggapan > 0 ? round(($sesuaiKebutuhan / $totalTanggapan) * 100, 2) : 0;

        // Pendapatan RT Nelayan
        $pendapatanRtQuery = InformasiPendapatanRumahTangga::whereIn('responden_id', $respondenIds);
        if ($selectedProgresDate) {
            $pendapatanRtQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $pendapatanRtNelayan = $pendapatanRtQuery->avg('pendapatan_total') ?? 0;

        // Indeks Kesejahteraan
        $indeksKesejahteraan = round($rataRataKebahagiaan, 2);

        // Tingkat Kelembagaan
        $totalSosialQuery = SosialKelembagaan::whereIn('responden_id', $respondenIds);
        $anggotaKelompokKoperasiQuery = SosialKelembagaan::whereIn('responden_id', $respondenIds)->where(function ($q) {
            $q->where('anggota_kelompok', '>=', 3)->orWhere('anggota_koperasi', '>=', 3);
        });
        if ($selectedProgresDate) {
            $totalSosialQuery->whereDate('created_at', '<=', $selectedProgresDate);
            $anggotaKelompokKoperasiQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $totalSosial = $totalSosialQuery->count();
        $anggotaKelompokKoperasi = $anggotaKelompokKoperasiQuery->count();
        $tingkatKelembagaan = $totalSosial > 0
            ? round(($anggotaKelompokKoperasi / $totalSosial) * 100, 2) : 0;

        // Total Tenaga Kerja
        $totalTenagaKerjaQuery = DB::table('progres_knmp')->whereIn('knmp_id', $knmpIds);
        if ($selectedProgresDate) {
            $totalTenagaKerjaQuery->whereDate('created_at', '<=', $selectedProgresDate);
        }
        $totalTenagaKerja = $totalTenagaKerjaQuery->sum('tk_total') ?? 0;

        // Progres Nasional table data
        $progresNasionalData = $progresNasional->map(function ($item) {
            return [
                'id' => $item->id,
                'knmp_id' => $item->knmp_id,
                'nama' => $item->knmp_nama ?? 'KNMP #' . $item->knmp_id,
                'progres' => round($item->progres, 2),
                'nama_jasa_konstruksi' => $item->nama_jasa_konstruksi ?? '-',
                'delta' => $item->delta ?? 0,
                'keterangan' => $item->keterangan,
            ];
        })->values();

        return response()->json([
            'totalKnmp' => number_format($totalKnmp, 0, ',', '.'),
            'ketersediaanInfrastruktur' => number_format($ketersediaanInfrastruktur, 2, ',', '.') . '%',
            'pendapatanRtNelayan' => 'Rp ' . number_format($pendapatanRtNelayan, 0, ',', '.'),
            'indeksKesesuaianKebutuhan' => number_format($indeksKesesuaianKebutuhan, 2, ',', '.') . '%',
            'indeksKesejahteraan' => number_format($indeksKesejahteraan, 2, ',', '.'),
            'tingkatKelembagaan' => number_format($tingkatKelembagaan, 2, ',', '.') . '%',
            'progresNasionalAvg' => number_format($progresNasionalAvg, 2),
            'progresNasionalCount' => count($progresNasional),
            'progresNasionalSelesai' => $progresNasional->where('progres', 100)->count(),
            'progresNasionalData' => $progresNasionalData,
            'totalSurveyTerisi' => $totalSurveyTerisi,
            'tingkatKelengkapanData' => $tingkatKelengkapanData,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Export Dashboard to PDF
     */
    public function exportPdf(Request $request)
    {
        $tahapReq = $request->get('tahap');
        $tahunReq = $request->get('tahun', session('selected_tahun'));

        $tahap = $tahapReq !== null ? $tahapReq : 'all';

        $selectedProgresDate = $request->get('progres_date');

        $desa_knmp_query = Knmp::query();

        if ($tahap !== 'all') {
            $desa_knmp_query->where('batch_id', $tahap);
        } elseif ($tahunReq) {
            $batchIds = \App\Models\Batch::where('tahun', $tahunReq)->pluck('id');
            $desa_knmp_query->whereIn('batch_id', $batchIds);
        }

        $knmpIds = $desa_knmp_query->pluck('id')->toArray();

        // Mendapatkan ID progres terbaru per KNMP
        if (!$selectedProgresDate) {
            $selectedProgresDate = ProgresHarian::join('konstruksi_knmp as kk', 'progres_harian.knmp_konstruksi_id', '=', 'kk.id')
                ->whereIn('kk.knmp_id', $knmpIds)
                ->whereNotNull('progres_harian.tanggal')->orderBy('progres_harian.tanggal', 'desc')->value('progres_harian.tanggal');
        }


        $latestIds = DB::table('progres_harian as ph')
            ->join('konstruksi_knmp as kk', 'ph.knmp_konstruksi_id', '=', 'kk.id')
            ->selectRaw('MAX(ph.id) as id')
            ->joinSub(function ($query) use ($knmpIds, $selectedProgresDate) {
                $query->from('progres_harian as ph2')
                    ->join('konstruksi_knmp as kk2', 'ph2.knmp_konstruksi_id', '=', 'kk2.id')
                    ->select('kk2.knmp_id', DB::raw('MAX(ph2.tanggal) as max_tanggal'))
                    ->whereIn('kk2.knmp_id', $knmpIds);
                if ($selectedProgresDate) {
                    $query->where('ph2.tanggal', '<=', $selectedProgresDate);
                }
                $query->groupBy('kk2.knmp_id');
            }, 'max_dates', function ($join) {
                $join->on('kk.knmp_id', '=', 'max_dates.knmp_id')
                     ->on('ph.tanggal', '=', 'max_dates.max_tanggal');
            })
            ->groupBy('kk.knmp_id', 'ph.tanggal')
            ->pluck('id');


        // Mengambil data utama menggunakan JOIN (5 Table: knmp, progres_harian, batch, tahap_konstruksi, penyedia_jasa_konstruksi)
        $desa_knmp_data = DB::table('progres_harian as ph')
            ->join('konstruksi_knmp as kk', 'ph.knmp_konstruksi_id', '=', 'kk.id')
            ->join('knmp as k', 'kk.knmp_id', '=', 'k.id')
            ->leftJoin('batch as b', 'k.batch_id', '=', 'b.id')
            ->leftJoin('penyedia_jasa_konstruksi as pj', 'kk.jasa_konstruksi_id', '=', 'pj.id')
            ->whereIn('ph.id', $latestIds)

            ->select(
                'ph.*',
                'kk.knmp_id',
                'k.nama as knmp_nama',
                'k.kabupaten',
                'k.provinsi',
                'k.kecamatan',
                'k.tahap_saat_ini',
                'k.batch_id',
                'b.nama_tahap as batch_nama',
                'kk.tanggal_mulai',
                'pj.nama as nama_jasa_konstruksi'
            )

            ->get();

        $avgProgres = !$desa_knmp_data->isEmpty() ? round($desa_knmp_data->avg('progres'), 2) : 0;

        // Hitung deviasi secara dinamis berdasarkan tanggal_mulai
        $deviasiData = [];
        if (!$desa_knmp_data->isEmpty()) {
            // Ambil semua data konstruksi dan timeline
            $allKonstruksi = DB::table('konstruksi_knmp')
                ->whereIn('knmp_id', $knmpIds)
                ->get()
                ->keyBy('knmp_id');

            $allTimelines = DB::table('tahap_konstruksi')
                ->whereIn('knmp_konstruksi_id', $allKonstruksi->pluck('id'))
                ->orderBy('periode_mingguan')
                ->get()
                ->groupBy('knmp_konstruksi_id');

            // Tanggal acuan: gunakan tanggal saat ini untuk menentukan minggu keberapa
            $todayDate = $selectedProgresDate ? \Carbon\Carbon::parse($selectedProgresDate) : \Carbon\Carbon::now();

            foreach ($desa_knmp_data as $item) {
                $kId = $item->knmp_id;
                $progresAktual = (float)$item->progres;
                
                $konstruksi = $allKonstruksi->get($kId);
                if (!$konstruksi) {
                    $deviasiData[$kId] = 0;
                    continue;
                }

                $timelines = $allTimelines->get($konstruksi->id) ?? collect();
                $startDate = $konstruksi->tanggal_mulai ? \Carbon\Carbon::parse($konstruksi->tanggal_mulai) : null;

                if (!$startDate || $timelines->isEmpty()) {
                    $rencanaRaw = 0;
                } else {
                    // Hitung minggu saat ini: dari tanggal_mulai ke tanggal hari ini
                    $diffDays = $startDate->diffInDays($todayDate, false);
                    $currentWeek = max(1, (int) floor($diffDays / 7) + 1);

                    // Cari bobot rencana kumulatif untuk minggu tersebut
                    $tlRencana = $timelines->where('periode_mingguan', $currentWeek)->first();
                    if (!$tlRencana) {
                        $tlRencana = $timelines->sortByDesc('periode_mingguan')->first();
                    }
                    $rencanaRaw = $tlRencana ? $tlRencana->bobot_rencana_kumulatif : 0;
                }
                
                $item->rencana_kumulatif = $rencanaRaw;

                // Normalisasi jika data rencana menggunakan skala permil (max > 100)
                $maxPlan = $timelines->max('bobot_rencana_kumulatif') ?? 100;
                $scale = $maxPlan > 100 ? $maxPlan / 100 : 1;
                $rencanaPersen = round((float)$rencanaRaw / $scale, 2);
                
                // Deviasi = progres terbaru - rencana di minggu saat ini
                $deviasiData[$kId] = round($progresAktual - $rencanaPersen, 2);
                
                // Assign rencana_persen to item so we can pass it to view
                $item->rencana_persen = $rencanaPersen;
            }
        }

        // Build table data
        $tableData = [];
        foreach ($desa_knmp_data as $knmp) {
            $progres = $knmp->progres;
            
            // Kolom lokasi baris 1 (nama knmp) dan baris 2 (Kec, Kab, Prov)
            $lokasi_baris_1 = $knmp->knmp_nama;
            
            // Format baris 2: Kecamatan, Kabupaten, Provinsi
            $lokasi_parts = [];
            if ($knmp->kecamatan) $lokasi_parts[] = 'Kec. ' . ucwords(strtolower($knmp->kecamatan));
            if ($knmp->kabupaten) $lokasi_parts[] = ucwords(strtolower($knmp->kabupaten));
            if ($knmp->provinsi) $lokasi_parts[] = ucwords(strtolower($knmp->provinsi));
            $lokasi_baris_2 = implode(', ', $lokasi_parts);
 
            $deviasi = $deviasiData[$knmp->knmp_id] ?? null;
 
            $status_text = 'On Progres';
            $status_color = '#64748b'; // Gray
            $deviasi_formatted = null;
            $deviasi_color = '#64748b';
 
            if ($progres >= 100) {
                $status_text = 'Selesai';
                $status_color = '#3b82f6'; // Blue
            } elseif ($deviasi !== null) {
                if ($deviasi >= 0) {
                    $status_text = 'On Track';
                    $status_color = '#10b981'; // Green
                } else {
                    $status_text = 'Underperform';
                    $status_color = '#ef4444'; // Red
                }
            }
 
            if ($deviasi !== null) {
                $deviasi_formatted = ($deviasi >= 0 ? '+' : '-') . number_format(abs($deviasi), 2, '.', ',') . '%';
                $deviasi_color = $deviasi >= 0 ? '#10b981' : '#ef4444';
            }
 
            $tableData[] = [
                'nama_knmp' => $knmp->knmp_nama,
                'lokasi_1' => $lokasi_baris_1,
                'lokasi_2' => $lokasi_baris_2,
                'nama_penyedia' => $knmp->nama_jasa_konstruksi ?? '-',
                'rencana_persen' => $knmp->rencana_persen ?? 0,
                'progres' => round($progres, 2),
                'status_text' => $status_text,
                'status_color' => $status_color,
                'deviasi_formatted' => $deviasi_formatted,
                'deviasi_color' => $deviasi_color,
                'tahap' => $knmp->tahap_saat_ini,
                'keterangan' => $knmp->keterangan ?: '-',
            ];
        }

        // Urutkan berdasarkan progres terbesar ke terkecil
        usort($tableData, function ($a, $b) {
            return $b['progres'] <=> $a['progres'];
        });

        // Group by tahap
        $tableDataByTahap = [];
        foreach ($tableData as $row) {
            $tahapKey = $row['tahap'] ?: 'Lainnya';
            if (!isset($tableDataByTahap[$tahapKey])) {
                $tableDataByTahap[$tahapKey] = [];
            }
            $tableDataByTahap[$tahapKey][] = $row;
        }
        ksort($tableDataByTahap);

        // Ambil data KNMP dengan relasi buktiUploads untuk pengelompokan foto
        $desa_knmp = \App\Models\Knmp::with(['buktiUploads'])->whereIn('id', $knmpIds)->get();

        // Group photos by tahap then province (1 location = 1 photo, condition "after")
        $photosByProvince = [];
        $photosByTahap = [];
        foreach ($desa_knmp as $knmp) {
            if ($knmp->buktiUploads->isEmpty()) {
                continue;
            }
            $photos = $knmp->buktiUploads->filter(function ($b) {
                return str_contains(strtolower($b->tipe_file ?? ''), 'image/')
                    && strtolower($b->kondisi ?? '') === 'after';
            })->take(1);
            if ($photos->isEmpty()) {
                continue;
            }

            $provinceName = ucwords(strtolower($knmp->provinsi ?? 'Lainnya'));
            $tahapKey = $knmp->tahap_saat_ini ?: 'Lainnya';

            $lokasi_parts = [];
            if ($knmp->kecamatan) {
                $lokasi_parts[] = 'Kec. ' . ucwords(strtolower($knmp->kecamatan));
            }
            if ($knmp->kabupaten) {
                $lokasi_parts[] = ucwords(strtolower($knmp->kabupaten));
            }
            $lokasi = implode(', ', $lokasi_parts);

            $photoItem = [
                'nama' => $knmp->nama,
                'lokasi' => $lokasi,
                'photos' => $photos,
            ];

            $photosByProvince[$provinceName][] = $photoItem;
            $photosByTahap[$tahapKey][$provinceName][] = $photoItem;
        }
        ksort($photosByProvince);
        ksort($photosByTahap);
        foreach ($photosByTahap as &$provinces) {
            ksort($provinces);
        }
        unset($provinces);

        if ($tahap !== 'all') {
            $tahapLabel = $tahap == 1 ? 'I' : ($tahap == 2 ? 'II' : ($tahap == 3 ? 'III' : $tahap));
        } else {
            $availableTahaps = \App\Models\Knmp::whereNotNull('tahap_saat_ini')->distinct()->orderBy('tahap_saat_ini')->pluck('tahap_saat_ini')->toArray();
            $romanTahaps = array_map(function($t) {
                return $t == 1 ? 'I' : ($t == 2 ? 'II' : ($t == 3 ? 'III' : $t));
            }, $availableTahaps);
            
            if (count($romanTahaps) == 0) {
                $tahapLabel = 'Semua';
            } elseif (count($romanTahaps) == 1) {
                $tahapLabel = $romanTahaps[0];
            } elseif (count($romanTahaps) == 2) {
                $tahapLabel = $romanTahaps[0] . ' dan ' . $romanTahaps[1];
            } else {
                $tahapLabel = implode(', ', $romanTahaps);
            }
        }

        $exportDate = Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('d F Y');
        $totalLokasi = count($desa_knmp);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.pdf', compact(
            'tahap', 'tahapLabel', 'exportDate', 'totalLokasi', 'avgProgres',
            'tableData', 'tableDataByTahap', 'photosByProvince', 'photosByTahap', 'selectedProgresDate'
        ))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = 'Progres_KNMP_Tahap_' . $tahapLabel . '_' . date('Y-m-d') . '.pdf';
        
        // Set cookie so the frontend can hide the loader
        return $pdf->stream($filename)->withCookie(cookie('fileDownload', 'true', 1, null, null, false, false));
    }

    /**
     * Update Keterangan for a ProgresHarian record
     */
    public function updateKeterangan(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'nullable|array',
        ]);

        $progres = ProgresHarian::findOrFail($id);

        // Join multiple choices with comma
        $keterangan = $request->keterangan ? implode(', ', $request->keterangan) : null;

        $progres->update([
            'keterangan' => $keterangan
        ]);

        return redirect()->back()->with('success', 'Keterangan berhasil diperbarui.');
    }
}
