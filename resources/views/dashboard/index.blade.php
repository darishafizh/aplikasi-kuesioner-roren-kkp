@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <style>
        .sortable {
            position: relative;
            cursor: pointer;
            user-select: none;
            transition: all 0.2s;
        }

        .sortable:hover {
            background-color: #f3f4f6;
            color: #3b82f6;
        }

        .sortable i {
            font-size: 1.1em;
            vertical-align: middle;
            transition: transform 0.2s;
        }

        .sort-active {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
        }

        /* Force Poppins on Filter Form */
        #mainFilterForm,
        #mainFilterForm select,
        #mainFilterForm option,
        #mainFilterForm input,
        #mainFilterForm span {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Enhanced Search Field */
        .search-field-enhanced {
            border: 1px solid #d1d5db !important;
            border-radius: 8px !important;
            overflow: hidden;
            background: #fff;
        }

        .search-field-enhanced .input-group-text {
            background: #f8f9fa;
            border: none;
            border-right: 1px solid #e5e7eb;
            padding: 0.25rem 0.6rem;
        }

        .search-field-enhanced .input-group-text i {
            color: #6b7280;
            font-size: 1rem;
        }

        .search-field-enhanced .form-control,
        .search-field-enhanced .form-select {
            border: none;
            padding: 0.25rem 0.6rem;
            font-size: 0.875rem;
        }

        .search-field-enhanced .form-control:focus,
        .search-field-enhanced .form-select:focus {
            box-shadow: none;
        }

        .search-field-enhanced:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        /* Menyembunyikan ikon kalender native browser sepenuhnya */
        .search-field-enhanced input[type="date"]::-webkit-calendar-picker-indicator {
            display: none !important;
            -webkit-appearance: none !important;
        }

        /* Menyembunyikan ikon kalender native untuk Firefox */
        .search-field-enhanced input[type="date"] {
            -moz-appearance: textfield;
        }

        /* Fix modal centering - override app.css margin */
        #exportPdfModal .modal-dialog {
            margin: auto !important;
        }

        /* Flatpickr Modern & Compact Styling */
        .flatpickr-calendar {
            font-family: 'Poppins', sans-serif !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            padding-bottom: 8px !important;
        }

        .flatpickr-calendar.arrowTop:before,
        .flatpickr-calendar.arrowTop:after,
        .flatpickr-calendar.arrowBottom:before,
        .flatpickr-calendar.arrowBottom:after {
            display: none !important; /* Menghilangkan segitiga panah popup */
        }

        .flatpickr-current-month {
            font-size: 0.95rem !important;
            padding-top: 2px !important;
        }

        span.flatpickr-weekday {
            font-size: 0.72rem !important;
            font-weight: 600 !important;
            color: #64748b !important;
        }

        .flatpickr-day {
            font-size: 0.82rem !important;
            height: 34px !important;
            line-height: 34px !important;
            border-radius: 8px !important;
        }

        .flatpickr-day.selected {
            background-color: #3b82f6 !important;
            border-color: #3b82f6 !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3) !important;
        }

        /* ============ KNMP Rank Row (Top/Bottom 10) ============ */
        .knmp-rank-row__alert,
        .knmp-rank-row__check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            margin-right: 6px;
            border-radius: 50%;
            font-size: 13px;
            line-height: 1;
            flex-shrink: 0;
        }
        .knmp-rank-row__alert {
            color: #b91c1c;
            background: rgba(220, 38, 38, 0.12);
        }
        .knmp-rank-row__alert i { font-size: 13px; line-height: 1; }
        .knmp-rank-row__check {
            color: #047857;
            background: rgba(16, 185, 129, 0.12);
        }
        .knmp-rank-row__check i { font-size: 13px; line-height: 1; }

        .knmp-rank-row--alert {
            background: linear-gradient(90deg, rgba(254, 226, 226, 0.55) 0%, rgba(254, 226, 226, 0) 70%) !important;
        }
        .knmp-rank-row--alert td { border-color: rgba(220, 38, 38, 0.12) !important; }

        /* ============ Custom Tooltip Skin ============ */
        .knmp-rank-tooltip { --bs-tooltip-max-width: 260px; }
        .knmp-rank-tooltip .tooltip-inner {
            font-family: 'Poppins', sans-serif !important;
            max-width: 260px;
            padding: 0;
            background: #ffffff;
            color: #1f2937;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            text-align: left;
            font-size: 0.8rem;
            line-height: 1.4;
            overflow: hidden;
        }
        .knmp-rank-tooltip .tooltip-arrow::before { border-right-color: #ffffff !important; }
        .knmp-rank-tooltip.bs-tooltip-start .tooltip-arrow::before,
        .knmp-rank-tooltip.bs-tooltip-auto[data-popper-placement^="left"] .tooltip-arrow::before { border-left-color: #ffffff !important; border-right-color: transparent !important; }
        .knmp-rank-tooltip.bs-tooltip-top .tooltip-arrow::before,
        .knmp-rank-tooltip.bs-tooltip-auto[data-popper-placement^="top"] .tooltip-arrow::before { border-top-color: #ffffff !important; border-right-color: transparent !important; }
        .knmp-rank-tooltip.bs-tooltip-bottom .tooltip-arrow::before,
        .knmp-rank-tooltip.bs-tooltip-auto[data-popper-placement^="bottom"] .tooltip-arrow::before { border-bottom-color: #ffffff !important; border-right-color: transparent !important; }

        .knmp-tt { padding: 14px 16px; }
        .knmp-tt__title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #dc2626;
            margin-bottom: 8px;
        }
        .knmp-tt__title i { font-size: 1.1rem; }
        .knmp-tt--ok .knmp-tt__title { color: #059669; }
        .knmp-tt__name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
            line-height: 1.25;
            word-break: break-word;
        }
        .knmp-tt__rows {
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 10px;
            border: 1px solid #f1f5f9;
        }
        .knmp-tt__row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            padding: 3px 0;
            color: #64748b;
        }
        .knmp-tt__row strong {
            color: #334155;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }
        .knmp-tt__row--delta strong { color: #dc2626; font-weight: 700; }
        .knmp-tt__hint {
            font-size: 0.725rem;
            color: #64748b;
            line-height: 1.4;
            display: flex;
            align-items: start;
            gap: 6px;
        }
        .knmp-tt__hint::before {
            content: '•';
            color: #cbd5e1;
            font-weight: bold;
        }
        .knmp-tt--plain {
            padding: 8px 11px;
            font-size: 0.78rem;
            font-weight: 500;
            color: #0f172a;
        }
    </style>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2rem; color: #f59e0b;">
                        <i class="mdi {{ $greetingIcon ?? 'mdi-weather-sunny' }}"></i>
                    </div>
                    <div>
                        <h4 class="page-title mb-1">{{ $greeting }}, {{ Auth::user()->name ?? 'Pengguna' }}!</h4>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">Setiap langkah kecil membawa kita lebih dekat
                            ke tujuan besar.</p>
                    </div>
                </div>
                <div class="text-end">
                    <span class="text-muted" style="font-size: 0.9rem;">
                        <i class="mdi mdi-calendar-today me-1"></i>
                        <span id="current-date-display"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Filter Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card mb-0 shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <form method="GET" action="{{ route('dashboard.index') }}" class="d-inline-flex align-items-center flex-wrap gap-3" id="mainFilterForm">
                                <div class="d-flex align-items-center">
                                    <i class="mdi mdi-filter-outline text-muted me-2" style="font-size: 1rem;"></i>
                                    <span class="text-muted me-2" style="font-size: 0.78rem;">Periode:</span>
                                    <select name="period" class="form-select form-select-sm border-0 bg-light"
                                        style="width: auto; font-family: 'Poppins', sans-serif !important; font-weight: 500; font-size: 0.78rem;" onchange="this.form.submit()">
                                        <option value="all" {{ ($period ?? 'all') == 'all' ? 'selected' : '' }}>Semua Waktu</option>
                                        <option value="week" {{ ($period ?? '') == 'week' ? 'selected' : '' }}>Minggu Ini</option>
                                        <option value="month" {{ ($period ?? '') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                                        <option value="year" {{ ($period ?? '') == 'year' ? 'selected' : '' }}>Tahun Ini</option>
                                    </select>
                                </div>
                                <div class="d-flex align-items-center border-start ps-3">
                                    <span class="text-muted me-2" style="font-size: 0.78rem;">Tahap:</span>
                                    <select name="tahap" class="form-select form-select-sm border-0 bg-light"
                                        style="width: auto; font-family: 'Poppins', sans-serif !important; font-weight: 500; font-size: 0.78rem;" onchange="this.form.submit()">
                                        <option value="all" {{ ($tahap ?? 'all') == 'all' ? 'selected' : '' }}>Semua Tahap</option>
                                        @foreach($availableTahap as $t)
                                            <option value="{{ $t->id }}" {{ ($tahap ?? '') == (string)$t->id ? 'selected' : '' }}>{{ $t->nama_tahap }} - {{ $t->tahun }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex align-items-center border-start ps-3">
                                    <span class="text-muted me-2" style="font-size: 0.78rem;">Tanggal Progres:</span>
                                    <div class="input-group input-group-sm bg-light rounded" style="width:160px;">
                                        <span class="input-group-text border-0 bg-transparent pe-1"><i class="mdi mdi-calendar-month text-primary" style="font-size: 0.9rem;"></i></span>
                                        <input type="text" name="progres_date" class="form-control form-control-sm border-0 bg-transparent flatpickr-dashboard" 
                                            id="mainProgresDateFilter"
                                            value="{{ $selectedProgresDate ?? date('Y-m-d') }}"
                                            style="cursor:pointer;font-family: 'Poppins', sans-serif !important;font-weight:600;font-size: 0.78rem;color:#374151;padding-left: 0;"
                                            onchange="this.form.submit()">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-danger d-flex align-items-center gap-1 shadow-sm"
                                data-bs-toggle="modal" data-bs-target="#exportPdfModal"
                                style="font-size: 0.78rem; font-weight: 500; border-radius: 4px;">
                                <i class="mdi mdi-file-pdf-box"></i> Ekspor PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($tahap !== 'all')
    <div class="row mb-3">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-0" style="font-size: 1rem;">
                <i class="mdi mdi-flag-variant text-primary me-1"></i>
                Kampung Nelayan Merah Putih {{ $tahapLabel ?? 'Semua Tahap' }}
            </h5>
            <p class="text-muted mb-0" style="font-size: 0.75rem;">Menampilkan data untuk {{ $totalKnmp }} lokasi KNMP pada tahap ini.</p>
        </div>
    </div>
    @endif

    {{-- KPI Cards Nasional --}}
    <div class="row mb-3">
        {{-- Card 1: Rata-rata Nasional --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card widget-flat border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-2 d-block">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; text-transform: uppercase;">RATA-RATA NASIONAL</h5>
                        <div style="background: #eff6ff; color: #3b82f6; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-chart-bar" style="font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;" id="kpi-progresNasionalAvg">{{ number_format($progresNasionalAvg, 2) }}%</h3>
                    <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Persentase progres rata-rata</p>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Lokasi --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card widget-flat border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-2 d-block">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; text-transform: uppercase;">TOTAL LOKASI</h5>
                        <div style="background: #f8fafc; color: #475569; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-map-marker-multiple" style="font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;" id="kpi-progresCount">{{ count($progresNasional) }}</h3>
                    <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Total lokasi KNMP</p>
                </div>
            </div>
        </div>

        {{-- Card 3: Selesai --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card widget-flat border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-2 d-block">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; text-transform: uppercase;">TOTAL SELESAI</h5>
                        <div style="background: #ecfdf5; color: #047857; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-check-circle-outline" style="font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;" id="kpi-progresSelesai">{{ $progresNasional->where('progres', 100)->count() }}</h3>
                    <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Progres telah 100%</p>
                </div>
            </div>
        </div>

        {{-- Card 4: On Progress --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card widget-flat border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-2 d-block">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; text-transform: uppercase;">ON PROGRES</h5>
                        <div style="background: #fffbeb; color: #b45309; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="mdi mdi-clock-outline" style="font-size: 1.2rem;"></i>
                        </div>
                    </div>
                    <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;" id="kpi-progresOnProgress">{{ $progresNasional->where('progres', '<', 100)->count() }}</h3>
                    <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Progres di bawah 100%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ANALISIS PROGRES --}}
    <div class="row mb-4">
        
        <!-- Sebaran Progres -->
        <div class="col-xl-4 col-md-12 mb-xl-0 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom pb-2 pt-3 px-4" style="min-height: 48px;">
                    <h5 class="header-title mb-0">
                        <i class="mdi mdi-chart-bar me-2 text-info"></i>
                        Sebaran Persentase Progres
                    </h5>
                </div>
                <div class="card-body px-4 pb-4 pt-2 d-flex align-items-center justify-content-center" style="height: 300px;">
                    <div id="sebaranProgresChart" class="w-100"></div>
                </div>
            </div>
        </div>

        <!-- Top 10 Performa -->
        <div class="col-xl-4 col-md-6 mb-md-0 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom pb-2 pt-3 px-4" style="min-height: 48px;">
                    <h5 class="header-title mb-0">
                        <i class="mdi mdi-chart-bar me-2 text-info"></i>
                        10 KNMP Deviasi Tertinggi
                    </h5>
                </div>
                <div class="card-body p-0" style="height: 300px;">
                    @if($tahap !== 'all' && ($tahapSelesaiStatus[$tahap] ?? false))
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1);">
                                <i class="mdi mdi-check-decagram text-success" style="font-size: 3.5rem;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">Tahap {{ $tahap }} Selesai!</h6>
                            <p class="text-muted small mb-0 px-3">Seluruh KNMP pada tahap ini telah mencapai progres pembangunan 100%.</p>
                        </div>
                    @else
                        <div class="table-responsive" style="height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-centered table-hover mb-0">
                                <thead class="table-light fade-sticky-header">
                                    <tr>
                                        <th class="ps-4" style="font-size: 0.75rem; text-transform: uppercase; width: 65%;">KNMP</th>
                                        <th class="text-end pe-4" style="font-size: 0.75rem; text-transform: uppercase; width: 35%;">Deviasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($top10Knmp ?? [] as $item)
                                        @include('dashboard._knmp_rank_row', ['item' => $item, 'context' => 'top'])
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted py-4 align-middle" style="font-size: 0.8rem;">Tidak ada data performa aktif</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Bottom 10 Performa -->
        <div class="col-xl-4 col-md-6 mb-md-0 mb-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-bottom pb-2 pt-3 px-4" style="min-height: 48px;">
                    <h5 class="header-title mb-0">
                        <i class="mdi mdi-chart-bar me-2 text-info"></i>
                        10 KNMP Deviasi Terendah
                    </h5>
                </div>
                <div class="card-body p-0" style="height: 300px;">
                    @if($tahap !== 'all' && ($tahapSelesaiStatus[$tahap] ?? false))
                        <div class="h-100 d-flex flex-column align-items-center justify-content-center text-center p-4">
                            <div class="rounded-circle bg-soft-success d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1);">
                                <i class="mdi mdi-check-decagram text-success" style="font-size: 3.5rem;"></i>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">Tahap {{ $tahap }} Selesai!</h6>
                            <p class="text-muted small mb-0 px-3">Seluruh KNMP pada tahap ini telah mencapai progres pembangunan 100%.</p>
                        </div>
                    @else
                        <div class="table-responsive" style="height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-centered table-hover mb-0">
                                <thead class="table-light fade-sticky-header">
                                    <tr>
                                        <th class="ps-4" style="font-size: 0.75rem; text-transform: uppercase; width: 65%;">KNMP</th>
                                        <th class="text-end pe-4" style="font-size: 0.75rem; text-transform: uppercase; width: 35%;">Deviasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bottom10Knmp ?? [] as $item)
                                        @include('dashboard._knmp_rank_row', ['item' => $item, 'context' => 'bottom'])
                                    @empty
                                        <tr><td colspan="2" class="text-center text-muted py-4 align-middle" style="font-size: 0.8rem;">Tidak ada data performa aktif</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Progres KNMP Nasional --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="header-title mb-0">
                        <i class="mdi mdi-chart-bar me-2 text-info"></i>
                        Progres Pembangunan KNMP Nasional
                    </h5>
                    <div class="d-flex align-items-center flex-wrap gap-2">

                        <!-- Search -->
                        <div class="input-group input-group-sm search-field-enhanced" style="width:240px;">
                            <span class="input-group-text"><i class="mdi mdi-magnify"></i></span>
                            <input type="text" id="knmpDtSearch" class="form-control" placeholder="Cari KNMP, Penyedia...">
                        </div>
                    </div>
                </div>
                <div class="card-body">



                    {{-- Per-page selector & info --}}
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2" style="font-size:0.82rem;color:#6b7280;">
                            <span>Tampilkan</span>
                            <select id="knmpDtPageSize" class="form-select form-select-sm" style="width:80px;font-size:0.82rem;">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="-1">Semua</option>
                            </select>
                            <span>data</span>
                        </div>
                    </div>

                    @if(count($progresNasional) > 0)
                        <div class="table-responsive" style="border-radius:8px;border:1px solid #f1f5f9;">
                            <table class="table table-centered table-hover table-nowrap mb-0" id="knmpProgresTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="knmp-dt-sort" data-col="0" style="width:40px;cursor:pointer;user-select:none;"># <i class="mdi mdi-sort ms-1 text-muted opacity-50"></i></th>
                                        <th class="knmp-dt-sort" data-col="1" style="cursor:pointer;user-select:none;">Nama KNMP <i class="mdi mdi-sort ms-1 text-muted opacity-50"></i></th>
                                        <th style="width:340px;">Penyedia Jasa Konstruksi</th>
                                        <th class="knmp-dt-sort" data-col="3" style="width:250px;cursor:pointer;user-select:none;">Progres <i class="mdi mdi-sort ms-1 text-muted opacity-50"></i></th>
                                        <th class="knmp-dt-sort" data-col="5" style="width:160px;cursor:pointer;user-select:none;">Keterangan <i class="mdi mdi-sort ms-1 text-muted opacity-50"></i></th>
                                    </tr>
                                </thead>
                                <tbody id="progres-nasional-tbody">
                                    @foreach($progresNasional as $index => $item)
                                        @php
                                            $colorClass = 'bg-danger';
                                            if ($item->progres >= 100)     $colorClass = 'bg-success';
                                            elseif ($item->progres >= 75)  $colorClass = 'bg-primary';
                                            elseif ($item->progres >= 50)  $colorClass = 'bg-warning';
                                        @endphp
                                        <tr data-progres="{{ $item->progres }}"
                                            data-deviasi="{{ $item->deviasi ?? 0 }}"
                                            data-search="{{ strtolower(($item->knmp_nama ?? '') . ' ' . ($item->nama_jasa_konstruksi ?? '') . ' ' . ($item->keterangan ?? '')) }}">
                                            <td class="knmp-dt-rownum">{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $item->knmp_nama ?? 'KNMP #' . $item->knmp_id }}</td>
                                            <td><span class="text-muted fst-italic" style="font-size:0.85rem;">{{ $item->nama_jasa_konstruksi ?? '-' }}</span></td>
                                            <td>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold {{ $item->progres >= 100 ? 'text-success' : 'text-dark' }}" style="font-size:0.85rem;">
                                                        {{ number_format($item->progres, 2) }}%
                                                    </span>
                                                    @php $dev = $item->deviasi ?? 0; @endphp
                                                    @if($dev > 0)
                                                        <span class="fw-semibold text-success" style="font-size: 0.7rem;">+{{ number_format($dev, 2, '.', ',') }}%</span>
                                                    @elseif($dev < 0)
                                                        <span class="fw-semibold text-danger" style="font-size: 0.7rem;">{{ number_format($dev, 2, '.', ',') }}%</span>
                                                    @else
                                                        <span class="fw-semibold text-muted" style="font-size: 0.7rem;">0.00%</span>
                                                    @endif
                                                </div>
                                                <div class="progress" style="height:6px;background-color:#f1f3fa;border-radius:3px;">
                                                    <div class="progress-bar {{ $colorClass }}" role="progressbar"
                                                        style="width:{{ min($item->progres, 100) }}%;border-radius:3px;"
                                                        aria-valuenow="{{ $item->progres }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between gap-1">
                                                    <div>
                                                        @if($item->keterangan)
                                                            @foreach(explode(',', $item->keterangan) as $ket)
                                                                <span class="badge bg-soft-info text-info me-1" style="font-size:0.72rem;">{{ trim($ket) }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted" style="font-size:0.8rem;">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="d-flex align-items-center justify-content-between mt-3 flex-wrap gap-2">
                            <div style="font-size:0.8rem;color:#6b7280;" id="knmpDtInfoBottom"></div>
                            <nav aria-label="Navigasi Halaman">
                                <ul class="pagination pagination-sm mb-0" id="knmpDtPages" style="gap:3px;"></ul>
                            </nav>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-database-off fs-1"></i>
                            <p class="mt-2">Belum ada data progres nasional.</p>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Export PDF -->
    <div class="modal fade" id="exportPdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <form action="{{ route('dashboard.export-pdf') }}" method="GET" target="_blank">
                    <div class="modal-header bg-light border-bottom-0 pb-0 pt-3 px-4">
                        <h5 class="modal-title fw-bold" style="color: #1e293b;">
                            <i class="mdi mdi-file-pdf-box text-danger me-1"></i> Ekspor Laporan PDF
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pt-3 pb-4">
                        <p class="text-muted" style="font-size: 0.85rem; margin-bottom: 20px;">
                            Pilih tahap dan tanggal progres untuk menentukan data yang akan ditampilkan pada laporan PDF.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Pilih Tahap</label>
                            <select name="tahap" class="form-select" style="border-radius: 8px;">
                                <option value="all" {{ ($tahap ?? 'all') == 'all' ? 'selected' : '' }}>Semua Tahap</option>
                                @foreach($availableTahap as $t)
                                    <option value="{{ $t->id }}" {{ ($tahap ?? '') == (string)$t->id ? 'selected' : '' }}>{{ $t->nama_tahap }} - {{ $t->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label fw-semibold" style="font-size: 0.85rem;">Tanggal Progres <span class="text-danger">*</span></label>
                            <input type="date" name="progres_date" class="form-control" style="border-radius: 8px;"
                                value="{{ $selectedProgresDate ?? date('Y-m-d') }}" required>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.75rem;">
                                Data progres fisik dan dokumentasi akan diambil berdasarkan tanggal yang dipilih.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light px-4 py-3">
                        <button type="button" class="btn btn-light" style="border-radius: 6px; font-weight: 500;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger d-flex align-items-center gap-1" style="border-radius: 6px; font-weight: 500;" onclick="setTimeout(() => { $('#exportPdfModal').modal('hide'); }, 500)">
                            <i class="mdi mdi-download"></i> Download PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import -->


    {{-- Pipeline Tahapan KNMP --}}
    @php
        $pipelineCount = [
            'usulan' => 0,
            'survey' => 0,
            'ded' => 0,
            'lelang' => 0,
            'konstruksi' => 0,
            'serah_terima' => 0,
        ];
        
        $totalKnmpPipeline = count($desa_knmp) > 0 ? count($desa_knmp) : 1;
        foreach ($desa_knmp as $knmp) {
            $stg = str_replace(' ', '_', strtolower($knmp->tahap_saat_ini ?? 'usulan'));
            if ($stg === 'master_plan_ded' || $stg === 'master_plan') $stg = 'ded'; 
            if (isset($pipelineCount[$stg])) {
                $pipelineCount[$stg]++;
            } else {
                $pipelineCount['usulan']++; // default fallback
            }
        }

        $stagesConf = [
            'usulan' => ['label' => 'Usulan', 'color' => '#3b82f6'], // blue
            'survey' => ['label' => 'Survey', 'color' => '#10b981'], // emerald
            'ded' => ['label' => 'DED', 'color' => '#8b5cf6'], // violet
            'lelang' => ['label' => 'Lelang', 'color' => '#f59e0b'], // amber
            'konstruksi' => ['label' => 'Konstruksi', 'color' => '#ef4444'], // red
            'serah_terima' => ['label' => 'Serah Terima', 'color' => '#84cc16'] // green
        ];
    @endphp

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px; background: #ffffff;">
                <div class="card-header bg-white border-bottom pb-2 pt-3 px-4" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="header-title mb-0">
                        <i class="mdi mdi-chart-timeline me-2 text-primary"></i>Sebaran KNMP Berdasarkan Tahapan
                    </h5>
                </div>
                <div class="card-body px-4 py-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 mb-2">
                        @foreach ($stagesConf as $key => $conf)
                            @php
                                $cnt = $pipelineCount[$key];
                                $pct = round(($cnt / $totalKnmpPipeline) * 100);
                            @endphp
                            
                            <div class="text-center flex-fill" style="min-width: 90px;">
                                <h6 class="mb-3 fw-semibold" style="color: #475569; font-size: 0.75rem;">{{ $conf['label'] }}</h6>
                                <h2 class="mb-2 fw-bold" style="color: {{ $conf['color'] }}; font-size: 2.6rem; line-height: 1; text-shadow: 0 0 15px {{ $conf['color'] }}20;">
                                    {{ number_format($cnt) }}
                                </h2>
                                <span class="fw-medium" style="font-size: 0.85rem; color: #64748b;">{{ $pct }}%</span>
                            </div>

                            @if (!$loop->last)
                                <div class="d-none d-md-flex align-items-center justify-content-center" style="color: #cbd5e1;">
                                    <i class="mdi mdi-chevron-right fs-4"></i>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MAP SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="header-title mb-0">
                            <i class="mdi mdi-map-marker-multiple me-2 text-danger"></i>Sebaran Lokasi KNMP
                        </h5>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="d-flex align-items-center gap-1">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#10b981;"></span>
                            <span style="font-size:0.75rem;color:#64748b;">Selesai (100%)</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#ef4444;"></span>
                            <span style="font-size:0.75rem;color:#64748b;">Belum Selesai</span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div style="position: relative;">
                        <div id="map-knmp" style="height: 500px;"></div>
                        <!-- Fixed info card overlay -->
                        <div id="map-info-card" style="
                            position: absolute; top: 12px; right: 12px; z-index: 999;
                            width: 280px; background: #fff; border-radius: 10px;
                            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
                            display: none; overflow: hidden; font-family: 'Poppins', sans-serif;
                        ">
                            <div id="map-info-header" style="
                                background: linear-gradient(135deg, #003D7A, #0054A6);
                                color: #fff; padding: 12px 14px;
                            ">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 id="map-info-name" class="mb-0" style="font-size: 0.78rem; font-weight: 700; color: #fff;"></h6>
                                        <small id="map-info-location" style="font-size: 0.65rem; opacity: 0.85;"></small>
                                    </div>
                                    <button onclick="document.getElementById('map-info-card').style.display='none'" style="
                                        background: rgba(255,255,255,0.15); border: none; color: #fff;
                                        width: 22px; height: 22px; border-radius: 50%; cursor: pointer;
                                        display: flex; align-items: center; justify-content: center; font-size: 0.75rem; flex-shrink: 0;
                                    ">&times;</button>
                                </div>
                            </div>
                            <div style="padding: 12px 14px;">
                                <div id="map-info-progres" style="margin-bottom: 10px;"></div>
                                <div id="map-info-stats" style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 10px;"></div>
                                <div id="map-info-komoditas" style="margin-bottom: 10px;"></div>
                                <a id="map-info-link" href="#" class="btn btn-sm btn-primary w-100" style="font-size: 0.7rem; border-radius: 6px; padding: 6px;">
                                    <i class="mdi mdi-information-outline me-1"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Real-time update indicator --}}
    <div id="realtime-indicator" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; display: none;">
        <div class="card border-0 shadow-lg mb-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                <div class="spinner-grow spinner-grow-sm text-success" role="status" id="realtime-spinner">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <i class="mdi mdi-check-circle text-success" id="realtime-check"
                    style="display: none; font-size: 1.1rem;"></i>
                <small class="text-muted" id="realtime-text">Memperbarui data...</small>
            </div>
        </div>
    </div>

    <!-- Modal Edit Keterangan -->
    <div class="modal fade" id="editKeteranganModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editKeteranganForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Keterangan - <span id="edit-ket-knmp-name"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Variabel Pemantauan (Bisa pilih lebih dari satu)</label>
                            <select name="keterangan[]" id="edit-keterangan-select" class="form-control select2" multiple="multiple" style="width: 100%;">
                                <optgroup label="1. Variabel Sumber Daya (Input)">
                                    <option value="Tenaga Kerja (Manpower)">Tenaga Kerja (Manpower)</option>
                                    <option value="Ketersediaan Material">Ketersediaan Material</option>
                                    <option value="Kesiapan Alat Berat">Kesiapan Alat Berat</option>
                                </optgroup>
                                <optgroup label="2. Variabel Lingkungan & Eksternal">
                                    <option value="Kondisi Cuaca">Kondisi Cuaca</option>
                                    <option value="Kondisi Geologis/Medan">Kondisi Geologis/Medan</option>
                                    <option value="Sosial-Masyarakat">Sosial-Masyarakat</option>
                                </optgroup>
                                <optgroup label="3. Variabel Manajemen & Teknis">
                                    <option value="Kualitas Dokumentasi Teknis">Kualitas Dokumentasi Teknis</option>
                                    <option value="Proses Perizinan">Proses Perizinan</option>
                                    <option value="Logistik & Rantai Pasok">Logistik & Rantai Pasok</option>
                                </optgroup>
                                <optgroup label="4. Variabel Finansial Lapangan">
                                    <option value="Arus Kas Proyek (Cash Flow)">Arus Kas Proyek (Cash Flow)</option>
                                </optgroup>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    {{-- KNMP DataTable Engine --}}
    <script>
        (function () {
            'use strict';

            const TABLE_ID      = 'knmpProgresTable';
            const SEARCH_ID     = 'knmpDtSearch';
            const PAGESIZE_ID   = 'knmpDtPageSize';
            const PAGES_ID      = 'knmpDtPages';
            const INFO_TOP_ID   = 'knmpDtInfo';
            const INFO_BOT_ID   = 'knmpDtInfoBottom';

            let allRows       = [];
            let filteredRows  = [];
            let currentPage   = 1;
            let pageSize      = 25;
            let sortCol       = -1;
            let sortDir       = 'asc';

            function init() {
                const tbody = document.querySelector('#' + TABLE_ID + ' tbody');
                if (!tbody) return;
                allRows = Array.from(tbody.querySelectorAll('tr'));
                filteredRows = allRows.slice();
                render();
                bindEvents();
            }

            function bindEvents() {
                // Search
                const searchEl = document.getElementById(SEARCH_ID);
                if (searchEl) {
                    searchEl.addEventListener('input', function () {
                        currentPage = 1;
                        applyFilter();
                    });
                }
                // Page size
                const psEl = document.getElementById(PAGESIZE_ID);
                if (psEl) {
                    psEl.addEventListener('change', function () {
                        pageSize = parseInt(this.value, 10);
                        currentPage = 1;
                        render();
                    });
                }
                // Column sort
                document.querySelectorAll('#' + TABLE_ID + ' .knmp-dt-sort').forEach(function (th) {
                    th.addEventListener('click', function () {
                        const col = parseInt(this.dataset.col, 10);
                        if (sortCol === col) {
                            sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                        } else {
                            sortCol = col;
                            sortDir = 'asc';
                        }
                        // Update header icons
                        document.querySelectorAll('#' + TABLE_ID + ' .knmp-dt-sort').forEach(function (h) {
                            const ic = h.querySelector('i');
                            h.classList.remove('sort-active');
                            if (ic) ic.className = 'mdi mdi-sort ms-1 text-muted opacity-50';
                        });
                        this.classList.add('sort-active');
                        const icon = this.querySelector('i');
                        if (icon) icon.className = 'mdi mdi-sort-' + (sortDir === 'asc' ? 'ascending' : 'descending') + ' ms-1 fw-bold';
                        applySort();
                    });
                });
            }

            function getCellVal(row, col) {
                // Use data attributes for numeric cols
                if (col === 3) return parseFloat(row.dataset.progres || 0);
                if (col === 4) return parseFloat(row.dataset.deviasi || 0);
                const cells = row.querySelectorAll('td');
                return cells[col] ? (cells[col].textContent || '').trim().toLowerCase() : '';
            }

            function applyFilter() {
                const q = (document.getElementById(SEARCH_ID)?.value || '').toLowerCase().trim();
                filteredRows = allRows.filter(function (row) {
                    if (!q) return true;
                    return (row.dataset.search || '').includes(q);
                });
                if (sortCol >= 0) applySort(false);
                else render();
            }

            function applySort(doRender) {
                filteredRows.sort(function (a, b) {
                    const av = getCellVal(a, sortCol);
                    const bv = getCellVal(b, sortCol);
                    let cmp = 0;
                    if (typeof av === 'number') cmp = av - bv;
                    else cmp = av < bv ? -1 : av > bv ? 1 : 0;
                    return sortDir === 'asc' ? cmp : -cmp;
                });
                if (doRender !== false) render();
            }

            function render() {
                const tbody = document.querySelector('#' + TABLE_ID + ' tbody');
                if (!tbody) return;

                const total = filteredRows.length;
                const ps    = pageSize === -1 ? total : pageSize;
                const pages = ps > 0 ? Math.ceil(total / ps) : 1;
                if (currentPage > pages) currentPage = pages || 1;

                const start = ps === total && pageSize === -1 ? 0 : (currentPage - 1) * ps;
                const end   = pageSize === -1 ? total : Math.min(start + ps, total);

                // Hide all, then show current slice
                allRows.forEach(function (r) { r.style.display = 'none'; });
                filteredRows.slice(start, end).forEach(function (r, i) {
                    r.style.display = '';
                    const numCell = r.querySelector('.knmp-dt-rownum');
                    if (numCell) numCell.textContent = start + i + 1;
                });

                // Info
                const from = total === 0 ? 0 : start + 1;
                const infoText = 'Menampilkan ' + from + '\u2013' + end + ' dari ' + total + ' data';
                if (document.getElementById(INFO_TOP_ID)) document.getElementById(INFO_TOP_ID).textContent = infoText;
                if (document.getElementById(INFO_BOT_ID)) document.getElementById(INFO_BOT_ID).textContent = infoText;

                renderPagination(pages);
            }

            function renderPagination(pages) {
                const ul = document.getElementById(PAGES_ID);
                if (!ul) return;
                ul.innerHTML = '';

                if (pages <= 1) return;

                function mkLi(label, page, disabled, active) {
                    const li = document.createElement('li');
                    li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
                    const a = document.createElement('a');
                    a.className = 'page-link';
                    a.style.cssText = 'border-radius:6px!important;font-size:0.8rem;padding:4px 9px;';
                    a.href = '#';
                    a.innerHTML = label;
                    if (!disabled && !active) {
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            currentPage = page;
                            render();
                        });
                    }
                    li.appendChild(a);
                    return li;
                }

                ul.appendChild(mkLi('<i class="mdi mdi-chevron-left"></i>', currentPage - 1, currentPage === 1, false));

                const delta = 2;
                let lo = Math.max(1, currentPage - delta);
                let hi = Math.min(pages, currentPage + delta);
                if (lo > 1) {
                    ul.appendChild(mkLi('1', 1, false, false));
                    if (lo > 2) ul.appendChild(mkLi('...', null, true, false));
                }
                for (let p = lo; p <= hi; p++) {
                    ul.appendChild(mkLi(p, p, false, p === currentPage));
                }
                if (hi < pages) {
                    if (hi < pages - 1) ul.appendChild(mkLi('...', null, true, false));
                    ul.appendChild(mkLi(pages, pages, false, false));
                }

                ul.appendChild(mkLi('<i class="mdi mdi-chevron-right"></i>', currentPage + 1, currentPage === pages, false));
            }

            document.addEventListener('DOMContentLoaded', init);
        })();

        function filterByDate(date) {
            const url = new URL(window.location.href);
            if (date) url.searchParams.set('progres_date', date);

            // Preserve existing filters
            if (!url.searchParams.has('period')) url.searchParams.set('period', '{{ $period ?? "all" }}');
            if (!url.searchParams.has('tahap')) url.searchParams.set('tahap', '{{ $tahap ?? "all" }}');
            window.location.href = url.toString();
        }

    </script>
    {{-- Pass data to JavaScript --}}
    <script>
        window.dashboardData = {
            capaianPerKnmp: {!! json_encode($capaianPerKnmp ?? []) !!},
            labelKnmp: {!! json_encode($labelKnmp ?? []) !!},
            distribusiAsetData: {!! json_encode($distribusiAsetData ?? []) !!},
            distribusiAsetLabels: {!! json_encode($distribusiAsetLabels ?? []) !!},
            penyerapanTenagaKerja: {!! json_encode($penyerapanTenagaKerja ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!},
            penyerapanLabels: {!! json_encode($penyerapanLabels ?? ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']) !!},
            tingkatKesejahteraanData: {!! json_encode($tingkatKesejahteraanData ?? [0, 0, 0, 0]) !!},
            tingkatKesejahteraanLabels: {!! json_encode($tingkatKesejahteraanLabels ?? ['Sangat Sejahtera', 'Sejahtera', 'Cukup Sejahtera', 'Kurang Sejahtera']) !!},
            desaKnmp: @json($desa_knmp ?? []),
            trendDates: @json($trendDates ?? []),
            trendAverages: @json($trendAverages ?? []),
            detailUrlPattern: "{{ route('informasi_umum.index') }}?knmp_id=:id"
        };
    </script>
    <script src="{{ asset('assets/js/dashboard.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('.flatpickr-dashboard', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: false
                });
            }

            // Render Sebaran Progres Pie Chart
            if (document.getElementById('sebaranProgresChart') && typeof ApexCharts !== 'undefined') {
                var sebaranData = @json(array_values($sebaranProgres ?? []));
                var sebaranLabels = @json(array_keys($sebaranProgres ?? []));
                
                // Cek apakah ada data sama sekali
                var hasData = sebaranData.some(function(val) { return val > 0; });
                
                if (hasData) {
                    var sebaranOptions = {
                        chart: {
                            type: 'pie',
                            height: 250,
                            toolbar: { show: false },
                            fontFamily: 'Poppins, sans-serif'
                        },
                        series: sebaranData,
                        labels: sebaranLabels,
                        colors: ['#ef4444', '#f59e0b', '#0ea5e9', '#6366f1', '#10b981'], // modern red, amber, sky, indigo, emerald
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '12px',
                            fontFamily: 'Poppins, sans-serif',
                            markers: { radius: 12, offsetX: -4 },
                            itemMargin: { horizontal: 8, vertical: 4 }
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontFamily: 'Poppins, sans-serif',
                                fontWeight: 600,
                                fontSize: '11px'
                            },
                            dropShadow: { enabled: false }
                        },
                        tooltip: {
                            y: {
                                formatter: function (val) {
                                    return val + " Lokasi";
                                }
                            }
                        },
                        stroke: { width: 1, colors: ['#fff'] }
                    };

                    var sebaranChart = new ApexCharts(document.querySelector("#sebaranProgresChart"), sebaranOptions);
                    sebaranChart.render();
                } else {
                    document.getElementById('sebaranProgresChart').innerHTML = '<div class="d-flex h-100 justify-content-center align-items-center text-muted"><p class="mb-0">Tidak ada data</p></div>';
                }
            }
        });
        // Handle Edit Keterangan Modal
        $('.btn-edit-keterangan').on('click', function() {
            const id = $(this).data('id');
            const knmpName = $(this).data('knmp');
            const currentKet = $(this).data('keterangan') || '';
            
            $('#edit-ket-knmp-name').text(knmpName);
            $('#editKeteranganForm').attr('action', `/dashboard/progres-nasional/${id}`);
            
            // Set values in select2
            const values = currentKet.split(',').map(s => s.trim()).filter(s => s !== '');
            $('#edit-keterangan-select').val(values).trigger('change');
            
            $('#editKeteranganModal').modal('show');
        });

        $(document).ready(function() {
            if ($.fn.select2) {
                $('#edit-keterangan-select').select2({
                    dropdownParent: $('#editKeteranganModal'),
                    placeholder: "Pilih variabel...",
                    allowClear: true
                });
            }
        });
    </script>
@endpush
