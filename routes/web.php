<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\KeteranganEnumeratorController;
use App\Http\Controllers\InformasiUmumController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\RespondenController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileKnmpController;
use App\Http\Controllers\ProgresController;
use App\Http\Controllers\TanggapanController;
use App\Http\Controllers\KebahagiaanController;
use App\Http\Controllers\UsahaController;
use App\Http\Controllers\PemasaranController;
use App\Http\Controllers\PendapatanRtController;
use App\Http\Controllers\SosialController;
use App\Http\Controllers\KnmpTahapController;


Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


Route::middleware('auth')->group(function () {

    // Change Password Routes
    Route::get('password/change', [App\Http\Controllers\Auth\ChangePasswordController::class, 'edit'])->name('password.change');
    Route::post('password/change', [App\Http\Controllers\Auth\ChangePasswordController::class, 'update'])->name('password.change.update');

    // ==============================
    // DASHBOARD ROUTES (NO PREFIX)
    // ==============================


    // ==============================
    // DASHBOARD ROUTES
    // ==============================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/export-pdf', [DashboardController::class, 'exportPdf'])->name('dashboard.export-pdf');
    Route::get('/dashboard/api/data', [DashboardController::class, 'apiData'])->name('dashboard.api-data');

    // ==============================
    // DASHBOARD IMPORT (Super Admin Only)
    // ==============================
    Route::post('/dashboard/import-progres-nasional', [\App\Http\Controllers\ImportController::class, 'importProgresHarian'])
        ->middleware('role:super_admin')
        ->name('dashboard.import_progres_nasional');
    
    Route::put('/dashboard/progres-nasional/{id}', [DashboardController::class, 'updateKeterangan'])
        ->middleware('role:super_admin')
        ->name('dashboard.update_keterangan');



    // ==============================
    // SURVEY ROUTES
    // ==============================
    Route::group(['prefix' => 'survey'], function () {
        Route::get('/', [SurveyController::class, 'index'])->name('survey.index');

        // Super Admin only: Add and Delete KNMP
        Route::post('/', [SurveyController::class, 'store'])->middleware('role:super_admin')->name('survey.store');
        Route::post('/import', [SurveyController::class, 'import'])->middleware('role:super_admin')->name('survey.import');
        Route::get('/template', [SurveyController::class, 'downloadTemplate'])->middleware('role:super_admin')->name('survey.template');
        Route::put('/{id}', [SurveyController::class, 'update'])->middleware('role:super_admin')->name('survey.update');
        Route::delete('/{id}', [SurveyController::class, 'destroy'])->middleware('role:super_admin')->name('survey.destroy');

        // AJAX endpoints for cascade dropdown
        Route::get('/locations/regencies/{province_id}', [SurveyController::class, 'getRegencies'])->name('survey.regencies');
        Route::get('/locations/districts/{regency_id}', [SurveyController::class, 'getDistricts'])->name('survey.districts');
        Route::get('/locations/villages/{district_id}', [SurveyController::class, 'getVillages'])->name('survey.villages');

        // ==============================
        // FORMS ROUTES
        // ==============================
        Route::group(['prefix' => 'forms', 'middleware' => 'village_access'], function () {
            // Note: /{knmp} routes moved to the bottom of the group to prevent shadowing
            Route::group(['middleware' => 'role:enumerator'], function () {
                Route::delete('/delete-responden', [RespondenController::class, 'delete_responden'])->name('forms.delete_responden');

                // Store routes (using new specialized controllers)
                Route::post('/store_profile_knmp/{knmp}', [ProfileKnmpController::class, 'store'])->name('forms.store_profile_knmp');
                Route::post('/store_progres_knmp/{knmp}', [ProgresController::class, 'store'])->name('forms.store_progres_knmp');
                Route::post('/store_tanggapan_masyarakat/{knmp}', [TanggapanController::class, 'store'])->name('forms.store_tanggapan_masyarakat');
                Route::post('/store_tingkat_kebahagiaan/{knmp}', [KebahagiaanController::class, 'store'])->name('forms.store_tingkat_kebahagiaan');
                Route::post('/store_informasi_responden/{knmp}', [RespondenController::class, 'store_informasi_responden'])->name('forms.store_informasi_responden');
                Route::post('/store_informasi_usaha/{knmp}', [UsahaController::class, 'store'])->name('forms.store_informasi_usaha');
                Route::post('/store_pemasaran_perikanan/{knmp}', [PemasaranController::class, 'store'])->name('forms.store_pemasaran_perikanan');
                Route::post('/store_pendapatan_rt/{knmp}', [PendapatanRtController::class, 'store'])->name('forms.store_pendapatan_rt');
                Route::post('/store_sosial_kelembagaan/{knmp}', [SosialController::class, 'store'])->name('forms.store_sosial_kelembagaan');

                // Edit/Update routes (using new specialized controllers)
                Route::post('/update_profile_knmp/{knmp}', [ProfileKnmpController::class, 'update'])->name('forms.update_profile_knmp');
                Route::post('/update_progres_knmp/{knmp}', [ProgresController::class, 'update'])->name('forms.update_progres_knmp');
                Route::post('/update_tanggapan_masyarakat/{knmp}', [TanggapanController::class, 'update'])->name('forms.update_tanggapan_masyarakat');

                // File upload routes
                Route::post('/bukti-upload', [EvidenceController::class, 'store_bukti_upload'])
                    ->name('forms.store_bukti_upload');
                Route::delete('/bukti-upload', [EvidenceController::class, 'delete_bukti_upload'])
                    ->name('forms.delete_bukti_upload');
                Route::delete('/bukti-upload/{id}', [EvidenceController::class, 'delete_bukti_single'])
                    ->name('forms.delete_bukti_single');

                // ==============================
                // IMPORT EXCEL ROUTES
                // ==============================
                Route::post('/import-responden/{knmp}', [\App\Http\Controllers\ImportController::class, 'importResponden'])
                    ->name('forms.import_responden');
                Route::post('/import-profile-knmp/{knmp}', [\App\Http\Controllers\ImportController::class, 'importProfileKnmp'])
                    ->name('forms.import_profile_knmp');
                Route::post('/import-progres-knmp/{knmp}', [\App\Http\Controllers\ImportController::class, 'importProgresKnmp'])
                    ->name('forms.import_progres_knmp');
                Route::post('/import-tanggapan-masyarakat/{knmp}', [\App\Http\Controllers\ImportController::class, 'importTanggapanMasyarakat'])
                    ->name('forms.import_tanggapan_masyarakat');
                Route::post('/import-tingkat-kebahagiaan/{knmp}', [\App\Http\Controllers\ImportController::class, 'importTingkatKebahagiaan'])
                    ->name('forms.import_tingkat_kebahagiaan');
                Route::post('/import-informasi-usaha/{knmp}', [\App\Http\Controllers\ImportController::class, 'importInformasiUsaha'])
                    ->name('forms.import_informasi_usaha');
                Route::post('/import-informasi-pemasaran/{knmp}', [\App\Http\Controllers\ImportController::class, 'importInformasiPemasaran'])
                    ->name('forms.import_informasi_pemasaran');
                Route::post('/import-pendapatan-rt/{knmp}', [\App\Http\Controllers\ImportController::class, 'importPendapatanRt'])
                    ->name('forms.import_pendapatan_rt');
                Route::post('/import-sosial-kelembagaan/{knmp}', [\App\Http\Controllers\ImportController::class, 'importSosialKelembagaan'])
                    ->name('forms.import_sosial_kelembagaan');
            });

            // Evidence & PDF routes
            Route::get('/evidence/{knmp}', [EvidenceController::class, 'evidence'])
                ->name('survey.evidence');
            Route::get('/questionnaires-pdf/{knmp}', [ReportController::class, 'questionnairesListPdf'])
                ->name('survey.questionnaires-pdf');
            Route::get('/questionnaire-pdf/{knmp}/{responden}', [ReportController::class, 'generateRespondenQuestionnairesPdf'])
                ->name('survey.questionnaire-pdf');

            // Export Excel route
            Route::get('/export-excel/{knmp?}', [RespondenController::class, 'exportExcel'])
                ->name('forms.export-excel');

            Route::get('/download-template/{section}', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])
                ->name('forms.download_template');

            // Route with parameters should be at the bottom to prevent shadowing
            Route::get('/{knmp}', [FormsController::class, 'index'])->name('forms.index');
            Route::get('/{knmp}/edit-responden', [RespondenController::class, 'editRespondenList'])->name('forms.edit-responden');
        });
    });


    // ==============================
    // REPORT ROUTES
    // ==============================
    Route::group(['prefix' => 'informasi-umum'], function () {
        Route::get('/', [InformasiUmumController::class, 'index'])->name('informasi_umum.index');
    });

    // ==============================
    // ACTIVITY LOG ROUTES (Super Admin Only)
    // ==============================
    Route::group(['prefix' => 'activity-log', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-log.index');
    });

    // Note: survey.evidence route already defined above in survey/forms group



    // ==============================
    // USER MANAGEMENT ROUTES (Super Admin Only)
    // ==============================
    Route::group(['prefix' => 'user_management', 'middleware' => 'role:super_admin'], function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('user_management.index');
        Route::post('/', [UserManagementController::class, 'store'])->name('user_management.store');
        Route::put('/{id}', [UserManagementController::class, 'update'])->name('user_management.update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->name('user_management.destroy');
    });


    Route::prefix('forms')->as('forms.')->group(function () {

        // Halaman utama Forms
        Route::get('/', [FormsController::class, 'index'])->name('index');

        // -----------------------------------------
        // INFORMASI UMUM
        // -----------------------------------------
        Route::get('/informasi-umum', [FormsController::class, 'informasiUmum'])
            ->name('informasi_umum');
        Route::get('/informasi-umum/create', [FormsController::class, 'informasiUmumCreate'])
            ->name('informasi_umum.create');
        Route::post('/informasi-umum', [FormsController::class, 'informasiUmumStore'])
            ->name('informasi_umum.store');
        Route::get('/informasi-umum/{id}/edit', [FormsController::class, 'informasiUmumEdit'])
            ->name('informasi_umum.edit');
        Route::put('/informasi-umum/{id}', [FormsController::class, 'informasiUmumUpdate'])
            ->name('informasi_umum.update');
        Route::delete('/informasi-umum/{id}', [FormsController::class, 'informasiUmumDelete'])
            ->name('informasi_umum.delete');

        // -----------------------------------------
        // KETERANGAN ENUMERATOR (RESOURCE)
        // -----------------------------------------
        Route::resource('keterangan-enumerator', KeteranganEnumeratorController::class);

        // -----------------------------------------
        // TARGET REALISASI
        // -----------------------------------------
        Route::get('/target-realisasi', [FormsController::class, 'targetRealisasiIndex'])
            ->name('target_realisasi.index');

        Route::get('/target-realisasi/create', [FormsController::class, 'targetRealisasiCreate'])
            ->name('target_realisasi.create');

        Route::post('/target-realisasi', [FormsController::class, 'targetRealisasiStore'])
            ->name('target_realisasi.store');

        Route::get('/target-realisasi/{id}/edit', [FormsController::class, 'targetRealisasiEdit'])
            ->name('target_realisasi.edit');

        Route::put('/target-realisasi/{id}', [FormsController::class, 'targetRealisasiUpdate'])
            ->name('target_realisasi.update');

        Route::delete('/target-realisasi/{id}', [FormsController::class, 'targetRealisasiDelete'])
            ->name('target_realisasi.delete');

        // -----------------------------------------
        // PROGRES PER KOMPONEN
        // -----------------------------------------
        Route::get('/progres-per-komponen', [FormsController::class, 'progresPerKomponen'])
            ->name('progres_per_komponen');
        Route::get('/progres-per-komponen/create', [FormsController::class, 'progresPerKomponenCreate'])
            ->name('progres_per_komponen.create');

        // -----------------------------------------
        // PROFIL KNMP
        // -----------------------------------------
        Route::get('/profil-knmp', [FormsController::class, 'profilKnmp'])
            ->name('profil_knmp');
        Route::get('/profil-knmp/create', [FormsController::class, 'profilKnmpCreate'])
            ->name('profil_knmp.create');
    });

    // ==============================
    // KNMP TAHAP ROUTES
    // ==============================
    Route::group(['prefix' => 'knmp-tahap'], function () {
        Route::get('/master', [KnmpTahapController::class, 'masterIndex'])->name('knmp.master');
        // --- Usulan ---
        Route::get('/usulan', [KnmpTahapController::class, 'usulanIndex'])->name('usulan.index');
        Route::get('/usulan/{knmp}', [KnmpTahapController::class, 'usulanShow'])->name('usulan.show');
        Route::post('/usulan/{knmp}', [KnmpTahapController::class, 'usulanStore'])->name('usulan.store');
        Route::put('/usulan/{knmp}/update', [KnmpTahapController::class, 'usulanUpdate'])->name('usulan.update');
        Route::post('/usulan-import', [KnmpTahapController::class, 'usulanImport'])->name('usulan.import');

        // --- Survey Tahap ---
        Route::get('/survey-tahap', [KnmpTahapController::class, 'surveyTahapIndex'])->name('survey_tahap.index');
        Route::get('/survey-tahap/{knmp}', [KnmpTahapController::class, 'surveyTahapShow'])->name('survey_tahap.show');
        Route::post('/survey-tahap/{knmp}', [KnmpTahapController::class, 'surveyTahapStore'])->name('survey_tahap.store');

        // --- DED ---
        Route::get('/ded', [KnmpTahapController::class, 'dedIndex'])->name('ded.index');
        Route::get('/ded/{knmp}', [KnmpTahapController::class, 'dedShow'])->name('ded.show');
        Route::post('/ded/{knmp}', [KnmpTahapController::class, 'dedStore'])->name('ded.store');

        // --- Lelang ---
        Route::get('/lelang', [KnmpTahapController::class, 'lelangIndex'])->name('lelang.index');
        Route::get('/lelang/{knmp}', [KnmpTahapController::class, 'lelangShow'])->name('lelang.show');
        Route::post('/lelang/{knmp}', [KnmpTahapController::class, 'lelangStore'])->name('lelang.store');
        Route::post('/lelang/{knmp}/penyedia', [KnmpTahapController::class, 'penyediaStore'])->name('lelang.penyedia.store');

        // --- Konstruksi ---
        Route::get('/konstruksi', [KnmpTahapController::class, 'konstruksiIndex'])->name('konstruksi.index');
        Route::get('/konstruksi/{knmp}', [KnmpTahapController::class, 'konstruksiShow'])->name('konstruksi.show');
        Route::post('/konstruksi/{knmp}/timeline', [KnmpTahapController::class, 'timelineStore'])->name('konstruksi.timeline.store');
        Route::post('/konstruksi/{knmp}/progres-harian', [KnmpTahapController::class, 'progresHarianStore'])->name('konstruksi.progres_harian.store');
        Route::post('/konstruksi/{knmp}/dokumentasi', [KnmpTahapController::class, 'dokumentasiStore'])->name('konstruksi.dokumentasi.store');
        Route::post('/konstruksi/{knmp}/update-settings', [KnmpTahapController::class, 'updateKonstruksiSettings'])->name('konstruksi.update_settings');
        Route::post('/konstruksi/{knmp}/sync-realisasi', [KnmpTahapController::class, 'syncRealisasiAction'])->name('konstruksi.sync_realisasi');

        // --- Serah Terima ---
        Route::get('/serah-terima', [KnmpTahapController::class, 'serahTerimaIndex'])->name('serah_terima.index');
        Route::get('/serah-terima/{knmp}', [KnmpTahapController::class, 'serahTerimaShow'])->name('serah_terima.show');
        Route::post('/serah-terima/{knmp}', [KnmpTahapController::class, 'serahTerimaStore'])->name('serah_terima.store');

        // --- Riwayat Tahap ---
        Route::get('/riwayat', [KnmpTahapController::class, 'riwayatIndex'])->name('riwayat_tahap.index');

        // --- Perpindahan Tahap ---
        Route::post('/{knmp}/move', [KnmpTahapController::class, 'moveStage'])->name('knmp_tahap.move');
        Route::post('/batch-move', [KnmpTahapController::class, 'batchMoveStage'])->name('knmp_tahap.batch_move');
        Route::delete('/batch-delete', [KnmpTahapController::class, 'batchDestroy'])->name('knmp_tahap.batch_destroy');
        Route::delete('/{knmp}', [KnmpTahapController::class, 'destroy'])->name('knmp_tahap.destroy');
    });
});
