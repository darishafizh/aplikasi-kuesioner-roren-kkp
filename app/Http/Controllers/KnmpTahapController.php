<?php

namespace App\Http\Controllers;

use App\Models\Knmp;
use App\Models\PenyediaJasaKonstruksi;
use App\Models\RiwayatTahap;
use App\Models\TahapUsulan;
use App\Models\TahapSurvey;
use App\Models\TahapDed;
use App\Models\TahapLelang;
use App\Models\TahapSerahTerima;
use App\Models\TahapKonstruksi;
use App\Models\ProgresHarian;
use App\Services\KnmpStageService;
use App\Imports\UsulanImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KnmpTahapController extends Controller
{
    protected KnmpStageService $stageService;

    public function __construct(KnmpStageService $stageService)
    {
        $this->stageService = $stageService;
    }

    // =========================================================
    //  DAFTAR KNMP PER TAHAP
    // =========================================================

    /**
     * Halaman Master KNMP (Seluruh Data tanpa filter tahap).
     */
    public function masterIndex()
    {
        $query = Knmp::query();
        $availableTahapQuery = \App\Models\Batch::orderBy('id');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
            $availableTahapQuery->where('tahun', $selectedTahun);
        }
        
        $knmps = $query->get();
        $availableTahap = $availableTahapQuery->get();
        return view('knmp.tahap.master_index', compact('knmps', 'availableTahap'));
    }

    /**
     * Halaman daftar KNMP pada tahap Usulan.
     */
    public function usulanIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'usulan')->with('tahapUsulan');
        $batchQuery = \App\Models\Batch::orderBy('id');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
            $batchQuery->where('tahun', $selectedTahun);
        }
        
        $knmps = $query->get();
        $batches = $batchQuery->get();
        return view('knmp.tahap.usulan_index', compact('knmps', 'batches'));
    }

    /**
     * Detail data tahap usulan untuk satu KNMP.
     */
    public function usulanShow(Knmp $knmp)
    {
        $data = TahapUsulan::where('knmp_id', $knmp->id)->first();
        return view('knmp.tahap.usulan_show', compact('knmp', 'data'));
    }

    /**
     * Simpan / update data tahap usulan.
     */
    public function usulanStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        TahapUsulan::updateOrCreate(
            ['knmp_id' => $knmp->id],
            $validated
        );

        return redirect()->back()->with('success', 'Data tahap usulan berhasil disimpan.');
    }

    /**
     * Update data KNMP dan Tahap Usulan dari modal edit.
     */
    public function usulanUpdate(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'desa'      => 'required|string|max:255',
            'kecamatan' => 'required|string|max:255',
            'kabupaten' => 'required|string|max:255',
            'provinsi'  => 'required|string|max:255',
            'batch_id'  => 'nullable|exists:batch,id',
            'catatan'   => 'nullable|string',
        ]);

        DB::transaction(function () use ($knmp, $validated) {
            $knmp->update([
                'nama'      => $validated['nama'],
                'desa'      => $validated['desa'],
                'kecamatan' => $validated['kecamatan'],
                'kabupaten' => $validated['kabupaten'],
                'provinsi'  => $validated['provinsi'],
                'batch_id'  => $validated['batch_id'] ?? null,
            ]);

            TahapUsulan::updateOrCreate(
                ['knmp_id' => $knmp->id],
                ['catatan' => $validated['catatan'] ?? null]
            );
        });

        return redirect()->back()->with('success', 'Data KNMP Usulan berhasil diperbarui.');
    }

    /**
     * Import data usulan dari Excel.
     */
    public function usulanImport(Request $request)
    {
        $request->validate([
            'file'     => 'required|mimes:xlsx,xls,csv',
            'batch_id' => 'nullable|exists:batch,id',
            'tanggal'  => 'nullable|date',
        ]);

        $batchId  = $request->filled('batch_id') ? (int) $request->batch_id : null;
        $tanggal  = $request->filled('tanggal') ? $request->tanggal : null;

        try {
            Excel::import(new UsulanImport($batchId, $tanggal, 'usulan'), $request->file('file'));
            return redirect()->back()->with('success', 'Data usulan berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    // =========================================================

    /**
     * Halaman daftar KNMP pada tahap Survey.
     */
    public function surveyTahapIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'survey')->with('batch')->withCount('informasiResponden');
        $availableTahapQuery = \App\Models\Batch::orderBy('id');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
            $availableTahapQuery->where('tahun', $selectedTahun);
        }
        
        $knmps = $query->get();
        $availableTahap = $availableTahapQuery->get();
        return view('knmp.tahap.survey_index', compact('knmps', 'availableTahap'));
    }

    /**
     * Detail data tahap survey untuk satu KNMP.
     */
    public function surveyTahapShow(Knmp $knmp)
    {
        $data = TahapSurvey::where('knmp_id', $knmp->id)->first();
        return view('knmp.tahap.survey_show', compact('knmp', 'data'));
    }

    /**
     * Simpan / update data tahap survey.
     */
    public function surveyTahapStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tanggal' => 'nullable|date',
            'catatan' => 'nullable|string',
        ]);

        TahapSurvey::updateOrCreate(
            ['knmp_id' => $knmp->id],
            $validated
        );

        return redirect()->back()->with('success', 'Data tahap survey berhasil disimpan.');
    }

    // =========================================================

    /**
     * Halaman daftar KNMP pada tahap DED.
     */
    public function dedIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'ded');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        }
        
        $knmps = $query->get();
        return view('knmp.tahap.ded_index', compact('knmps'));
    }

    /**
     * Detail data tahap DED untuk satu KNMP.
     */
    public function dedShow(Knmp $knmp)
    {
        $data = TahapDed::where('knmp_id', $knmp->id)->first();
        return view('knmp.tahap.ded_show', compact('knmp', 'data'));
    }

    /**
     * Simpan / update data tahap DED.
     */
    public function dedStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'nomor_document'     => 'required|string|max:255',
            'tanggal_pengesahan'=> 'nullable|date',
            'file_url'          => 'nullable|string',
            'catatan'           => 'nullable|string',
        ]);

        TahapDed::updateOrCreate(
            ['knmp_id' => $knmp->id],
            $validated
        );

        return redirect()->back()->with('success', 'Data tahap DED berhasil disimpan.');
    }

    // =========================================================

    /**
     * Halaman daftar KNMP pada tahap Lelang.
     */
    public function lelangIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'lelang');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        }
        
        $knmps = $query->get();
        return view('knmp.tahap.lelang_index', compact('knmps'));
    }

    /**
     * Detail data tahap Lelang untuk satu KNMP.
     */
    public function lelangShow(Knmp $knmp)
    {
        $data = TahapLelang::where('knmp_id', $knmp->id)->first();
        $penyediaList = PenyediaJasaKonstruksi::orderBy('nama')->get();
        return view('knmp.tahap.lelang_show', compact('knmp', 'data', 'penyediaList'));
    }

    /**
     * Simpan / update data tahap Lelang.
     */
    public function lelangStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tanggal_penetapan' => 'nullable|date',
            'catatan'           => 'nullable|string',
        ]);

        TahapLelang::updateOrCreate(
            ['knmp_id' => $knmp->id],
            $validated
        );

        return redirect()->back()->with('success', 'Data tahap lelang berhasil disimpan.');
    }

    // =========================================================

    /**
     * Halaman daftar KNMP pada tahap Konstruksi.
     */
    public function konstruksiIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'konstruksi')
            ->with(['konstruksiKnmp.penyediaJasa', 'latestProgresNasional']);
        $availableTahapQuery = \App\Models\Batch::orderBy('id');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
            $availableTahapQuery->where('tahun', $selectedTahun);
        }
        
        $knmps = $query->get();
        $availableTahap = $availableTahapQuery->get();
        
        return view('knmp.tahap.konstruksi_index', compact('knmps', 'availableTahap'));
    }

    /**
     * Detail data tahap Konstruksi — berisi timeline, progres harian, tahap konstruksi.
     */
    public function konstruksiShow(Knmp $knmp)
    {
        // Ambil data konstruksi utama
        $konstruksi = $knmp->konstruksiKnmp;
        
        // Ambil timeline (tahap_konstruksi) dari relasi konstruksi
        $tahapKonstruksi = $konstruksi ? $konstruksi->timeline()->orderBy('periode_mingguan')->get() : collect();

        $progresHarian = $knmp->konstruksiKnmp ? $knmp->konstruksiKnmp->progresHarian()->orderByDesc('tanggal')->get() : collect();


        return view('knmp.tahap.konstruksi_show', [
            'knmp'            => $knmp,
            'timeline'        => $tahapKonstruksi,
            'progresHarian'   => $progresHarian,
            'tahapKonstruksi' => $tahapKonstruksi,
            'konstruksi'      => $konstruksi,
            'dokumentasi'     => $knmp->dokumentasiKonstruksi
        ]);
    }

    /**
     * Simpan timeline mingguan (Kurva S).
     */
    public function timelineStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'periode_mingguan'          => 'required|integer|min:1',
            'bobot_rencana_kumulatif'  => 'nullable|numeric|min:0|max:100',
            'bobot_realisasi_kumulatif'=> 'nullable|numeric|min:0|max:100',
            'status'                   => 'nullable|in:on_track,terlambat,selesai',
        ]);

        $konstruksi = $knmp->konstruksiKnmp()->firstOrCreate(['knmp_id' => $knmp->id]);
        $konstruksi->timeline()->create($validated);

        return redirect()->back()->with('success', 'Data timeline berhasil disimpan.');
    }

    /**
     * Simpan progres harian.
     */
    public function progresHarianStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'progres' => 'nullable|numeric|min:0|max:100',
        ]);

        $konstruksi = $knmp->konstruksiKnmp()->firstOrCreate(['knmp_id' => $knmp->id]);
        $konstruksi->progresHarian()->create($validated);


        return redirect()->back()->with('success', 'Progres harian berhasil disimpan.');
    }

    /**
     * Update pengaturan konstruksi (tanggal_mulai) dan sinkronisasi realisasi dari progres harian.
     */
    public function updateKonstruksiSettings(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tanggal_mulai' => 'required|date',
        ]);

        $tanggalMulai = $validated['tanggal_mulai'];

        // 1. Update atau buat record konstruksi_knmp
        $knmp->konstruksiKnmp()->updateOrCreate(
            ['knmp_id' => $knmp->id],
            ['tanggal_mulai' => $tanggalMulai]
        );

        // 2. Jalankan logika sinkronisasi realisasi dari progres_harian
        $this->syncRealisasi($knmp, $tanggalMulai);

        return redirect()->back()->with('success', 'Pengaturan konstruksi dan sinkronisasi realisasi berhasil diperbarui.');
    }

    /**
     * Logika untuk menghitung periode mingguan bobot realisasi kumulatif dari progres harian.
     */
    private function syncRealisasi(Knmp $knmp, $tanggalMulai)
    {
        $startDate = \Carbon\Carbon::parse($tanggalMulai);
        
        // Ambil semua progres harian untuk KNMP ini via konstruksi
        $allProgresFisik = $knmp->konstruksiKnmp ? $knmp->konstruksiKnmp->progresHarian()
            ->whereNotNull('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get() : collect();

            
        if ($allProgresFisik->isEmpty()) {
            return;
        }

        // Kelompokkan progres harian ke dalam periode mingguan berdasarkan tanggal_mulai
        $realisasiPerMinggu = [];
        foreach ($allProgresFisik as $p) {
            $tgl = \Carbon\Carbon::parse($p->tanggal);
            $diffDays = $startDate->diffInDays($tgl, false);
            
            // Abaikan data sebelum tanggal_mulai
            if ($diffDays < 0) continue; 
            
            $week = (int) floor($diffDays / 7) + 1;
            
            // Ambil progres terakhir (terbesar tanggalnya) per minggu sebagai kumulatif
            // Karena ini kumulatif, nilai terakhir di minggu tersebut adalah capaian sampai minggu itu
            $realisasiPerMinggu[$week] = (float)$p->progres;
        }

        // Update bobot_realisasi_kumulatif pada setiap baris timeline (tahap_konstruksi)
        $konstruksi = $knmp->konstruksiKnmp;
        if (!$konstruksi) return;

        $timeline = $konstruksi->timeline;
        
        foreach ($timeline as $tl) {
            $minggu = $tl->periode_mingguan;
            
            // Jika ada data realisasi untuk minggu ini, update
            if (isset($realisasiPerMinggu[$minggu])) {
                $tl->update([
                    'bobot_realisasi_kumulatif' => $realisasiPerMinggu[$minggu]
                ]);
            } else {
                $prevMaxWeek = 0;
                $prevValue = null;
                foreach($realisasiPerMinggu as $w => $val) {
                    if ($w < $minggu && $w > $prevMaxWeek) {
                        $prevMaxWeek = $w;
                        $prevValue = $val;
                    }
                }
                
                if ($prevValue !== null && $startDate->copy()->addWeeks($minggu)->isPast()) {
                    $tl->update([
                        'bobot_realisasi_kumulatif' => $prevValue
                    ]);
                }
            }
        }
    }

    public function syncRealisasiAction(Knmp $knmp)
    {
        $konstruksi = $knmp->konstruksiKnmp;
            
        if (!$konstruksi || !$konstruksi->tanggal_mulai) {
            return redirect()->back()->with('error', 'Tanggal mulai belum diatur. Silakan atur tanggal mulai terlebih dahulu.');
        }
        
        $this->syncRealisasi($knmp, $konstruksi->tanggal_mulai);
        
        return redirect()->back()->with('success', 'Sinkronisasi realisasi berhasil dilakukan.');
    }

    // =========================================================

    /**
     * Halaman daftar KNMP pada tahap Serah Terima.
     */
    public function serahTerimaIndex()
    {
        $query = Knmp::where('tahap_saat_ini', 'serah_terima');
        
        $selectedTahun = session('selected_tahun');
        if ($selectedTahun) {
            $batchIds = \App\Models\Batch::where('tahun', $selectedTahun)->pluck('id');
            $query->whereIn('batch_id', $batchIds);
        }
        
        $knmps = $query->get();
        return view('knmp.tahap.serah_terima_index', compact('knmps'));
    }

    /**
     * Detail data tahap Serah Terima.
     */
    public function serahTerimaShow(Knmp $knmp)
    {
        $data = TahapSerahTerima::where('knmp_id', $knmp->id)->first();
        $lelang = TahapLelang::where('knmp_id', $knmp->id)->first();
        return view('knmp.tahap.serah_terima_show', compact('knmp', 'data', 'lelang'));
    }

    /**
     * Simpan / update data tahap Serah Terima.
     */
    public function serahTerimaStore(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'nomor_kontrak' => 'required|string|max:255',
            'tanggal_serah' => 'nullable|date',
            'catatan'       => 'nullable|string',
        ]);

        TahapSerahTerima::updateOrCreate(
            ['knmp_id' => $knmp->id],
            $validated
        );

        return redirect()->back()->with('success', 'Data serah terima berhasil disimpan.');
    }

    // =========================================================
    //  RIWAYAT TAHAP
    // =========================================================

    /**
     * Halaman riwayat perpindahan tahap seluruh KNMP.
     */
    public function riwayatIndex(Request $request)
    {
        $query = RiwayatTahap::with('knmp')->orderByDesc('created_at');

        $riwayat = $query->paginate(25);
        return view('knmp.tahap.riwayat_index', compact('riwayat'));
    }

    // =========================================================
    //  PERPINDAHAN TAHAP (Single & Batch)
    // =========================================================

    /**
     * Pindahkan satu KNMP ke tahap baru.
     */
    public function moveStage(Request $request, Knmp $knmp)
    {
        $validated = $request->validate([
            'tahap_baru' => 'required|in:' . implode(',', KnmpStageService::STAGES),
            'keterangan' => 'nullable|string',
        ]);

        $this->stageService->moveToStage($knmp, $validated['tahap_baru'], $validated['keterangan'] ?? null);

        return redirect()->back()->with('success', 'Tahap KNMP berhasil dipindahkan ke ' . strtoupper($validated['tahap_baru']) . '.');
    }

    /**
     * Pindahkan beberapa KNMP sekaligus (batch).
     */
    public function batchMoveStage(Request $request)
    {
        if ($request->has('knmp_ids_string') && !empty($request->input('knmp_ids_string'))) {
            $request->merge(['knmp_ids' => explode(',', $request->input('knmp_ids_string'))]);
        }

        $validated = $request->validate([
            'knmp_ids'   => 'required|array|min:1',
            'knmp_ids.*' => 'exists:knmp,id',
            'tahap_baru' => 'required|in:' . implode(',', KnmpStageService::STAGES),
            'keterangan' => 'nullable|string',
        ]);

        $this->stageService->batchMoveToStage(
            $validated['knmp_ids'],
            $validated['tahap_baru'],
            $validated['keterangan'] ?? null
        );

        return redirect()->back()->with('success', 'Batch perpindahan tahap berhasil.');
    }
    /**
     * Hapus KNMP.
     */
    public function destroy(Knmp $knmp)
    {
        $knmp->delete();
        return redirect()->back()->with('success', 'KNMP berhasil dihapus.');
    }
    /**
     * Hapus beberapa KNMP sekaligus (batch).
     */
    public function batchDestroy(Request $request)
    {
        if ($request->has('knmp_ids_string') && !empty($request->input('knmp_ids_string'))) {
            $request->merge(['knmp_ids' => explode(',', $request->input('knmp_ids_string'))]);
        }

        $validated = $request->validate([
            'knmp_ids'   => 'required|array|min:1',
            'knmp_ids.*' => 'exists:knmp,id',
        ]);

        Knmp::whereIn('id', $validated['knmp_ids'])->delete();

        return redirect()->back()->with('success', 'Berhasil menghapus ' . count($validated['knmp_ids']) . ' data KNMP.');
    }
}
