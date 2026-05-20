<?php

namespace App\Http\Controllers;

use App\Models\InformasiUmum;
use App\Models\Knmp;
use App\Models\ProfileKnmp;
use App\Models\ProgresKnmp;
use App\Models\ProgresKnmpDetail;
use App\Models\BuktiUpload;
use App\Models\TanggapanMasyarakat;
use App\Models\TingkatKebahagiaanNelayan;
use App\Models\TahapKonstruksi;
use Illuminate\Http\Request;

class InformasiUmumController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Filter KNMP list based on user access, only select needed columns and relationships for dropdown
        $knmpQuery = Knmp::select('id', 'nama', 'provinsi', 'kabupaten');

        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $knmpQuery->whereIn('batch_id', $batchIds);
        }

        // If user is a village user (not admin/super_admin), only show their assigned KNMP
        if ($user->isVillageUser() && !$user->isAdmin() && !$user->isSuperAdmin()) {
            $knmpQuery->where('id', $user->knmp_id);
        }

        $knmpList = $knmpQuery->orderBy('nama')->get();

        // Determine default KNMP ID
        if ($user->isVillageUser() && !$user->isAdmin() && !$user->isSuperAdmin()) {
             // Village user is locked to their KNMP
            $selectedKnmpId = $request->get('knmp_id', $user->knmp_id);
            if ($selectedKnmpId != $user->knmp_id) {
                $selectedKnmpId = $user->knmp_id;
            }
        } else {
            // Admin: Check if there is a 'last_active_knmp' or simply pick one with recent uploads if no specific ID requested
            if (!$request->has('knmp_id')) {
                // Try to find KNMP with most recent upload or update
                $latestUpload = BuktiUpload::latest()->first();
                $defaultId = $latestUpload ? $latestUpload->knmp_id : $knmpList->first()?->id;
                $selectedKnmpId = $defaultId;
            } else {
                $selectedKnmpId = $request->get('knmp_id');
            }
        }

        $selectedKnmp = Knmp::find($selectedKnmpId);

        // Initialize statistics
        $stats = [
            'profile' => null,
            'progres' => null,
            'progresDetails' => collect(),
            'jmlKepalaKeluarga' => 0,
            'totalNelayan' => 0,
            'jumlahKapal' => 0,
            'serapanTenagaKerja' => 0,
            // Komoditas 1
            'komoditas1' => '-',
            'volumeKomoditas1' => 0,
            'nilaiKomoditas1' => 0,
            'hargaKomoditas1' => 0,
            // Komoditas 2
            'komoditas2' => '-',
            'volumeKomoditas2' => 0,
            'nilaiKomoditas2' => 0,
            'hargaKomoditas2' => 0,
            // Other stats
            'pendapatanNelayan' => 0,
            'koperasiDesaMerahPutih' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        // Initialize monitoring stats for Section C-J
        $monitoringStats = [
            // D. Tanggapan Masyarakat
            'tanggapan' => [
                'total' => 0,
                'sesuai' => 0,
                'tidakSesuai' => 0,
                'persenSesuai' => 0,
                'avgKesenangan' => 0,
                'distribusiKesenangan' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            ],
            // E. Tingkat Kebahagiaan
            'kebahagiaan' => [
                'avgSkor' => 0,
                'totalResponden' => 0,
                'kategoriSkor' => [],
            ],
            // J. Bukti Pendukung
            'bukti' => [
                'totalFiles' => 0,
                'totalSize' => 0,
                'files' => collect(),
            ],
        ];

        if ($selectedKnmp) {
            // Get coordinates from KNMP
            $stats['latitude'] = $selectedKnmp->latitude;
            $stats['longitude'] = $selectedKnmp->longitude;

            // Profile KNMP
            $stats['profile'] = ProfileKnmp::where('knmp_id', $selectedKnmp->id)->first();

            if ($stats['profile']) {
                $stats['jmlKepalaKeluarga'] = $stats['profile']->jml_penduduk_des ?? 0;
                $stats['totalNelayan'] = $stats['profile']->jml_nelayan ?? 0;
                $stats['jumlahKapal'] = $stats['profile']->jumlah_kapal ?? 0;
                $stats['pendapatanNelayan'] = $stats['profile']->pendapatan_rata_rata_nelayan ?? 0;

                // Komoditas Utama 1
                $stats['komoditas1'] = $stats['profile']->komoditas_utama_1 ?? '-';
                $stats['hargaKomoditas1'] = $stats['profile']->harga_rata_komoditas_1 ?? 0;
                // Calculate volume and value for komoditas 1
                $stats['volumeKomoditas1'] = $stats['profile']->volume_produksi_ton ?? 0;
                $stats['nilaiKomoditas1'] = $stats['profile']->nilai_produksi ?? 0;

                // Komoditas Utama 2
                $stats['komoditas2'] = $stats['profile']->komoditas_utama_2 ?? '-';
                $stats['hargaKomoditas2'] = $stats['profile']->harga_rata_komoditas_2 ?? 0;

                // Koperasi info
                $stats['koperasiDesaMerahPutih'] = [
                    'nama' => $stats['profile']->calon_koperasi ?? 'Belum Ada',
                    'ketua' => $stats['profile']->nama_ketua ?? '-',
                    'sk' => $stats['profile']->sk_kopdeskel ?? '-',
                    'nomorInduk' => $stats['profile']->nomor_induk_kopdeskel ?? '-',
                    'anggotaLaki' => $stats['profile']->jumlah_anggota_laki ?? 0,
                    'anggotaPerempuan' => $stats['profile']->jumlah_anggota_perempuan ?? 0,
                ];
            }

            // Progres KNMP
            $stats['progres'] = ProgresKnmp::where('knmp_id', $selectedKnmp->id)->first();

            if ($stats['progres']) {
                // Serapan Tenaga Kerja
                $stats['serapanTenagaKerja'] = $stats['progres']->tk_total ?? 0;

                // Get progres details for each component
                $stats['progresDetails'] = ProgresKnmpDetail::where('progres_id', $stats['progres']->id)
                    ->orderBy('kode')
                    ->get();
                // Calculate Overall Progress (Average of 4 Components: A, B, C, D)
                $components = ['A', 'B', 'C', 'D'];
                $componentAverages = [];

                foreach ($components as $code) {
                    $details = $stats['progresDetails']->filter(function ($item) use ($code) {
                        return str_starts_with($item->kode, $code);
                    });

                    if ($details->count() > 0) {
                        $componentAverages[] = $details->avg('persen');
                    } else {
                        $componentAverages[] = 0;
                    }
                }

                $stats['progresNasional'] = count($componentAverages) > 0 ? array_sum($componentAverages) / count($componentAverages) : 0;
            } else {
                $stats['progresNasional'] = 0;
            }

            // --- KPI Calculations (Filtered by KNMP) ---

            // 1. Indeks Kesesuaian Kebutuhan
            // Formula: (Count(kesesuaian_kebutuhan=1) / Total) * 100
            // Filter by KNMP via Responden -> InformasiResponden -> knmp_id (Need to check relationships)
            // Assuming TanggapanMasyarakat belongs to Responden, and Responden belongs to KNMP
            // OR checks if TanggapanMasyarakat has direct knmp_id or via responden.
            // Let's check models first. 
            // For now, I will assume we need to join with responden table which has knmp_id.
            
            // Checking TanggapanMasyarakat model...
            // It usually has responden_id. InformasiResponden has knmp_id.
            
            // OPTIMASI: Ambil ID responden sekali saja untuk menghindari subquery berulang
            $respondenIds = \App\Models\InformasiResponden::where('knmp_id', $selectedKnmp->id)->pluck('id');

            // 1. Indeks Kesesuaian Kebutuhan
            $totalTanggapan = \App\Models\TanggapanMasyarakat::whereIn('responden_id', $respondenIds)->count();
            $sesuaiKebutuhan = \App\Models\TanggapanMasyarakat::whereIn('responden_id', $respondenIds)->where('kesesuaian_kebutuhan', 1)->count();
            
            $stats['indeksKesesuaianKebutuhan'] = $totalTanggapan > 0 ? round(($sesuaiKebutuhan / $totalTanggapan) * 100, 2) : 0;

            // 2. Indeks Kesejahteraan Nelayan (Rata-rata Skor Kebahagiaan)
            $stats['indeksKesejahteraan'] = \App\Models\TingkatKebahagiaanNelayan::whereIn('responden_id', $respondenIds)->avg('skor_nilai') ?? 0;
            $stats['indeksKesejahteraan'] = round($stats['indeksKesejahteraan'], 2);

            // 3. Tingkat Kelembagaan Nelayan
            $totalSosial = \App\Models\SosialKelembagaan::whereIn('responden_id', $respondenIds)->count();
            $anggotaAktif = \App\Models\SosialKelembagaan::whereIn('responden_id', $respondenIds)->where(function ($q) {
                $q->where('anggota_kelompok', '>=', 3)
                  ->orWhere('anggota_koperasi', '>=', 3);
            })->count();

            $stats['tingkatKelembagaan'] = $totalSosial > 0 ? round(($anggotaAktif / $totalSosial) * 100, 2) : 0;


            // J. Bukti Pendukung
            // Use DB aggregates instead of loading all models into memory
            $monitoringStats['bukti']['totalFiles'] = BuktiUpload::where('knmp_id', $selectedKnmp->id)->count();
            $monitoringStats['bukti']['totalSize'] = BuktiUpload::where('knmp_id', $selectedKnmp->id)->sum('ukuran_file');
            $monitoringStats['bukti']['files'] = BuktiUpload::where('knmp_id', $selectedKnmp->id)->latest()->take(6)->get();
        }

        // Fetch timeline data for S-Curve chart
        $timelineData = collect();
        $latestDeviasiData = null;
        $progresFisikMingguan = [];
        if ($selectedKnmp) {
            $timelineData = $selectedKnmp->timeline()
                ->whereNotNull('periode_mingguan')
                ->orderBy('periode_mingguan', 'asc')
                ->get();
            
            // Ambil tanggal_mulai dari relasi konstruksiKnmp
            $konstruksi = $selectedKnmp->konstruksiKnmp;
            $startDate = $konstruksi ? $konstruksi->tanggal_mulai : null;
            
            // Dapatkan semua progres harian untuk KNMP ini
            $allProgresFisik = $selectedKnmp->progresHarian()
                ->whereNotNull('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();
            
            if ($startDate && $allProgresFisik->count() > 0 && $timelineData->count() > 0) {
                // === LOGIKA BARU: Hitung bobot_realisasi_kumulatif dari progres_harian ===
                // Kelompokkan progres harian ke dalam periode mingguan berdasarkan tanggal_mulai
                $realisasiPerMinggu = [];
                foreach ($allProgresFisik as $p) {
                    $tgl = \Carbon\Carbon::parse($p->tanggal);
                    $diffDays = $startDate->diffInDays($tgl, false);
                    if ($diffDays < 0) continue; // Abaikan data sebelum tanggal_mulai
                    $week = (int) floor($diffDays / 7) + 1;
                    // Ambil progres terakhir (terbesar tanggalnya) per minggu sebagai kumulatif
                    $realisasiPerMinggu[$week] = (float)$p->progres;
                }
                
                // Update bobot_realisasi_kumulatif pada setiap baris timeline
                $totalPeriode = $timelineData->count();
                $lastVal = null;
                foreach ($timelineData as $tl) {
                    $minggu = $tl->periode_mingguan;
                    if (isset($realisasiPerMinggu[$minggu])) {
                        $tl->bobot_realisasi_kumulatif = $realisasiPerMinggu[$minggu];
                        $lastVal = $realisasiPerMinggu[$minggu];
                    } else {
                        // Carry over nilai dari minggu sebelumnya jika minggu ini belum ada input baru
                        // tapi minggu tersebut sudah lewat
                        $weekDate = $startDate->copy()->addWeeks($minggu);
                        if ($lastVal !== null && $weekDate->isPast()) {
                            $tl->bobot_realisasi_kumulatif = $lastVal;
                        } else {
                            $tl->bobot_realisasi_kumulatif = null;
                        }
                    }
                }
                
                // Simpan mapping untuk grafik
                $progresFisikMingguan = $realisasiPerMinggu;
                
                // Hitung deviasi real-time dari progres terakhir
                $latestProgres = $allProgresFisik->last();
                $realisasi = (float)$latestProgres->progres;
                
                // Hitung minggu saat ini berdasarkan tanggal progres terakhir
                $diffDaysLatest = $startDate->diffInDays(\Carbon\Carbon::parse($latestProgres->tanggal), false);
                $currentWeek = max(1, (int) floor($diffDaysLatest / 7) + 1);
                
                // Cari rencana untuk minggu saat ini
                $tlRencana = $timelineData->where('periode_mingguan', $currentWeek)->first();
                if (!$tlRencana) {
                    // Jika minggu melampaui timeline, gunakan minggu terakhir
                    $tlRencana = $timelineData->sortByDesc('periode_mingguan')->first();
                }
                $rencanaRaw = $tlRencana ? $tlRencana->bobot_rencana_kumulatif : 0;
                
                // Normalisasi jika data rencana menggunakan skala permil (max > 100)
                $maxPlan = $timelineData->max('bobot_rencana_kumulatif');
                $scale = $maxPlan > 100 ? $maxPlan / 100 : 1;
                
                $rencanaPersen = round($rencanaRaw / $scale, 2);
                
                $latestDeviasiData = [
                    'rencana' => $rencanaPersen,
                    'realisasi' => $realisasi,
                    'deviasi' => round($realisasi - $rencanaPersen, 2),
                    'minggu' => $currentWeek,
                    'total_minggu' => $totalPeriode,
                ];
            } elseif ($allProgresFisik->count() > 0 && $timelineData->count() > 0) {
                // Fallback jika tanggal_mulai belum diisi: tetap hitung deviasi dari data yang ada
                $latestProgres = $allProgresFisik->last();
                $realisasi = (float)$latestProgres->progres;
                
                // Ambil minggu terakhir yang memiliki realisasi di timeline
                $lastReportedTl = $timelineData->whereNotNull('bobot_realisasi_kumulatif')
                    ->filter(fn($tl) => $tl->bobot_realisasi_kumulatif > 0)
                    ->sortByDesc('periode_mingguan')
                    ->first();
                
                $week = $lastReportedTl ? $lastReportedTl->periode_mingguan : $timelineData->count();
                $rencanaRaw = $lastReportedTl ? $lastReportedTl->bobot_rencana_kumulatif : 0;
                
                // Jika tidak ada realisasi, gunakan rencana terakhir
                if (!$lastReportedTl) {
                    $tlLast = $timelineData->sortByDesc('periode_mingguan')->first();
                    $rencanaRaw = $tlLast ? $tlLast->bobot_rencana_kumulatif : 0;
                }
                
                $maxPlan = $timelineData->max('bobot_rencana_kumulatif');
                $scale = $maxPlan > 100 ? $maxPlan / 100 : 1;
                $rencanaPersen = round($rencanaRaw / $scale, 2);
                
                $latestDeviasiData = [
                    'rencana' => $rencanaPersen,
                    'realisasi' => $realisasi,
                    'deviasi' => round($realisasi - $rencanaPersen, 2),
                    'minggu' => $week,
                    'total_minggu' => $timelineData->count(),
                ];
            }
        }

        return view('informasi_umum.index', compact(
            'knmpList', 'selectedKnmp', 'selectedKnmpId', 'stats', 
            'monitoringStats', 'timelineData', 'latestDeviasiData', 'progresFisikMingguan'
        ));
    }

    public function create()
    {
        return view('dashboard.informasi_umum');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_responden' => 'required|string|max:255',
            'desa' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:255',
            'kabupaten' => 'nullable|string|max:255',
            'provinsi' => 'nullable|string|max:255',
            'status_responden' => 'nullable|string|max:255',
            'jenis_program' => 'nullable|string|max:255',
        ]);

        InformasiUmum::create($validated);

        return redirect()->route('informasi_umum.create')->with('success', 'Data Informasi Umum berhasil disimpan.');
    }
}
