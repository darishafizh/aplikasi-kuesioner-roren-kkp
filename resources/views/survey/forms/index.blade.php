@extends('layouts.app')

@section('content')

{{-- ===================================================== --}}
{{-- AUTO ERROR ALERT (VALIDATION FAILS) --}}
{{-- ===================================================== --}}
@if ($errors->any() && !session('error'))
@php
    $errorList = $errors->all();
    $errorCount = count($errorList);
    $shownErrors = array_slice($errorList, 0, 3);
    $errorMessage = 'Terjadi kesalahan input: ' . implode('; ', $shownErrors);
    if ($errorCount > 3) {
        $errorMessage .= ' (dan ' . ($errorCount - 3) . ' kesalahan lainnya)';
    }
    session()->flash('error', $errorMessage);
@endphp
@endif



{{-- ===================================================== --}}
{{-- Alert handled in layouts.app --}}

<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-left">
                <h4 class="page-title"><i class="mdi mdi-clipboard-text-outline me-2"></i>Kuesioner KNMP</h4>
                <small class="text-muted">{{ $knmp->nama ?? 'Data KNMP' }}</small>
            </div>
            <div class="page-title-right">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('survey.index') }}">Survey</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kuesioner</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<!-- KNMP Info Card -->
<div class="row">
    <div class="col-12">
        <div class="card survey-header-card">
            <!-- Card Header with Gradient -->
            <div class="card-header survey-card-header border-0">
                <div class="d-flex align-items-center">
                    <div class="survey-header-icon">
                        <i class="mdi mdi-anchor"></i>
                    </div>
                    <div class="ms-3">
                        <h4 class="mb-1 text-white fw-bold" style="font-size: var(--font-size-lg); margin-bottom: 0.25rem;">{{ $knmp->nama ?? 'Nama KNMP Tidak Ditemukan' }}</h4>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <p class="mb-0 text-white-50" style="font-size: var(--font-size-xs);"><i class="mdi mdi-map-marker me-1"></i>Kampung Nelayan Merah Putih</p>
                            <span class="badge bg-white text-primary text-uppercase px-2 py-0.5 rounded-pill shadow-sm" style="font-size: var(--font-size-xs); font-weight: 600;">
                                <i class="mdi mdi-chart-timeline-variant me-1"></i>Tahap: {{ $knmp->tahap_label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <!-- Location Info -->
                    <div class="col-lg-5 col-md-12">
                        <div class="location-info-card">
                            <div class="location-header mb-3">
                                <i class="mdi mdi-map-marker-radius text-white bg-primary rounded p-1 me-2"></i>
                                <span class="fw-semibold text-dark">Informasi Lokasi</span>
                            </div>
                            <div class="location-grid">
                                <div class="location-item">
                                    <div class="location-label">
                                        <i class="mdi mdi-home-city-outline text-muted me-2"></i>
                                        <span class="text-muted">Desa</span>
                                    </div>
                                    <div class="location-value">
                                        <span
                                            class="badge bg-primary text-white rounded-pill px-3">{{ $knmp->desa_kelurahan ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="location-item">
                                    <div class="location-label">
                                        <i class="mdi mdi-map-outline text-muted me-2"></i>
                                        <span class="text-muted">Kecamatan</span>
                                    </div>
                                    <div class="location-value">
                                        <span
                                            class="badge bg-info text-white rounded-pill px-3">{{ $knmp->kecamatan ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="location-item">
                                    <div class="location-label">
                                        <i class="mdi mdi-city-variant-outline text-muted me-2"></i>
                                        <span class="text-muted">Kabupaten</span>
                                    </div>
                                    <div class="location-value">
                                        <span
                                            class="badge bg-success text-white rounded-pill px-3">{{ $knmp->kabupaten_kota ?? 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="location-item">
                                    <div class="location-label">
                                        <i class="mdi mdi-map-marker-multiple-outline text-muted me-2"></i>
                                        <span class="text-muted">Provinsi</span>
                                    </div>
                                    <div class="location-value">
                                        <span
                                            class="badge bg-warning text-white rounded-pill px-3">{{ $knmp->provinsi ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Section -->
                    <div class="col-lg-7 col-md-12">
                        <div class="progress-info-card h-100">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="progress-icon-wrapper">
                                        <i class="mdi mdi-chart-arc"></i>
                                    </div>
                                    <div class="ms-3">
                                        <h5 class="mb-0 fw-semibold text-dark" style="font-size: var(--font-size-sm);">Progres Pembangunan KNMP</h5>
                                        <small class="text-muted d-block" style="font-size: var(--font-size-xs); line-height: 1.2;">Status penyelesaian keseluruhan</small>
                                    </div>
                                </div>
                                <div class="progress-percentage">
                                    <span class="fw-bold text-primary" style="font-size: var(--font-size-xl);">{{ $rataRataProgres }}</span>
                                    <span class="text-muted" style="font-size: var(--font-size-sm);">%</span>
                                </div>
                            </div>

                            <div class="progress-wrapper">
                                <div class="progress progress-lg">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-gradient-primary"
                                        role="progressbar" style="width: {{ $rataRataProgres }}%"
                                        aria-valuenow="{{ $rataRataProgres }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <div class="progress-milestones mt-2">
                                    <span class="milestone {{ $rataRataProgres >= 0 ? 'active' : '' }}">0%</span>
                                    <span class="milestone {{ $rataRataProgres >= 25 ? 'active' : '' }}">25%</span>
                                    <span class="milestone {{ $rataRataProgres >= 50 ? 'active' : '' }}">50%</span>
                                    <span class="milestone {{ $rataRataProgres >= 75 ? 'active' : '' }}">75%</span>
                                    <span class="milestone {{ $rataRataProgres >= 100 ? 'active' : '' }}">100%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Survey Sections Accordion -->
<div class="row mb-4">
    <div class="col-12">
        <div class="survey-sections-wrapper">
            <div class="section-list-header mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <span
                            class="mdi mdi-format-list-bulleted-square text-white bg-primary rounded p-1 me-2" style="font-size: var(--font-size-base); line-height: 1;"></span>
                        <h5 class="mb-0 fw-semibold" style="font-size: var(--font-size-base);">Bagian Kuesioner</h5>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" style="font-size: var(--font-size-xs);">10 Bagian</span>
                </div>
            </div>

            <div class="accordion survey-accordion" id="accordionExample">

                <!-- A. Profil KNMP -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingA">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseA" aria-expanded="false"
                            aria-controls="collapseA">
                            <div class="survey-section-indicator bg-primary">A</div>
                            <div class="survey-section-icon bg-primary text-white">
                                <i class="mdi mdi-home-city-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Profil KNMP</span>
                                <span class="survey-section-desc">Data identitas kampung nelayan</span>
                            </div>
                            @if(($sectionCounts['A'] ?? 0) > 0)
                                <span class="badge bg-success ms-auto">Terisi</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Diisi</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseA" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="profile-knmp"
                                    data-route="{{ route('forms.import_profile_knmp', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <a href="{{ route('forms.download_template', 'profile-knmp') }}"
                                    class="btn btn-secondary text-white btn-sm ms-2">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </a>
                            </div>
                            @include('survey.forms.form_layouts.profile_knmp')
                        </div>
                    </div>
                </div>

                <!-- B. Proses Pembangunan KNMP -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingB">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseB" aria-expanded="false"
                            aria-controls="collapseB">
                            <div class="survey-section-indicator bg-info">B</div>
                            <div class="survey-section-icon bg-info text-white">
                                <i class="mdi mdi-progress-wrench"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Progres Pembangunan KNMP</span>
                                <span class="survey-section-desc">Informasi progres pembangunan</span>
                            </div>
                            @if(($sectionCounts['B'] ?? 0) > 0)
                                <span class="badge bg-success ms-auto">Terisi</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Diisi</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseB" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="progres-knmp"
                                    data-route="{{ route('forms.import_progres_knmp', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <a href="{{ route('forms.download_template', 'progres-knmp') }}"
                                    class="btn btn-secondary text-white btn-sm ms-2">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </a>
                            </div>
                            @include('survey.forms.form_layouts.progres_pembangunan_knmp')
                        </div>
                    </div>
                </div>

                <!-- C. Informasi Responden -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingC">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseC" aria-expanded="false"
                            aria-controls="collapseC">
                            <div class="survey-section-indicator bg-success">C</div>
                            <div class="survey-section-icon bg-success text-white">
                                <i class="mdi mdi-account-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Informasi Responden</span>
                                <span class="survey-section-desc">Data pribadi responden</span>
                            </div>
                            @if(($sectionCounts['C'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['C'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseC" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="responden"
                                    data-route="{{ route('forms.import_responden', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <a href="{{ route('forms.download_template', 'responden') }}"
                                    class="btn btn-secondary text-white btn-sm ms-2">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </a>
                            </div>
                            @include('survey.forms.form_layouts.informasi_responden')
                        </div>
                    </div>
                </div>

                <!-- D. Tanggapan Masyarakat -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingD">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseD" aria-expanded="false"
                            aria-controls="collapseD">
                            <div class="survey-section-indicator bg-warning">D</div>
                            <div class="survey-section-icon bg-warning text-white">
                                <i class="mdi mdi-account-group-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Tanggapan Masyarakat</span>
                                <span class="survey-section-desc">Terkait pembangunan KNMP</span>
                            </div>
                            @if(($sectionCounts['D'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['D'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseD" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="tanggapan-masyarakat"
                                    data-route="{{ route('forms.import_tanggapan_masyarakat', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="tanggapan-masyarakat" data-section-name="Tanggapan Masyarakat">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.tanggapan_masyarakat')
                        </div>
                    </div>
                </div>

                <!-- E. Tingkat Kebahagiaan Nelayan -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingE">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseE" aria-expanded="false"
                            aria-controls="collapseE">
                            <div class="survey-section-indicator bg-danger">E</div>
                            <div class="survey-section-icon bg-danger text-white">
                                <i class="mdi mdi-emoticon-happy-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Tingkat Kebahagiaan Nelayan</span>
                                <span class="survey-section-desc">Survei kesejahteraan nelayan</span>
                            </div>
                            @if(($sectionCounts['E'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['E'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseE" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="tingkat-kebahagiaan"
                                    data-route="{{ route('forms.import_tingkat_kebahagiaan', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="tingkat-kebahagiaan" data-section-name="Tingkat Kebahagiaan">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.tingkat_kebahagiaan_nelayan')
                        </div>
                    </div>
                </div>

                <!-- F. Informasi Usaha -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingF">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseF" aria-expanded="false"
                            aria-controls="collapseF">
                            <div class="survey-section-indicator bg-secondary">F</div>
                            <div class="survey-section-icon bg-secondary text-white">
                                <i class="mdi mdi-store-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Informasi Usaha</span>
                                <span class="survey-section-desc">Kondisi existing usaha</span>
                            </div>
                            @if(($sectionCounts['F'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['F'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseF" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="informasi-usaha"
                                    data-route="{{ route('forms.import_informasi_usaha', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="informasi-usaha" data-section-name="Informasi Usaha">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.informasi_usaha')
                        </div>
                    </div>
                </div>

                <!-- G. Informasi Pemasaran -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingG">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseG" aria-expanded="false"
                            aria-controls="collapseG">
                            <div class="survey-section-indicator" style="background-color: #6f42c1;">G</div>
                            <div class="survey-section-icon text-white" style="background-color: #6f42c1;">
                                <i class="mdi mdi-fish"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Informasi Pemasaran Hasil Perikanan</span>
                                <span class="survey-section-desc">Data distribusi dan penjualan</span>
                            </div>
                            @if(($sectionCounts['G'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['G'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseG" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="informasi-pemasaran"
                                    data-route="{{ route('forms.import_informasi_pemasaran', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="informasi-pemasaran" data-section-name="Informasi Pemasaran">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.informasi_pemasaran_hasil_perikanan')
                        </div>
                    </div>
                </div>

                <!-- H. Informasi Pendapatan -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingH">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseH" aria-expanded="false"
                            aria-controls="collapseH">
                            <div class="survey-section-indicator" style="background-color: #20c997;">H</div>
                            <div class="survey-section-icon text-white" style="background-color: #20c997;">
                                <i class="mdi mdi-cash-multiple"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Informasi Pendapatan Rumah Tangga</span>
                                <span class="survey-section-desc">Data ekonomi keluarga</span>
                            </div>
                            @if(($sectionCounts['H'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['H'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseH" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="pendapatan-rt"
                                    data-route="{{ route('forms.import_pendapatan_rt', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="pendapatan-rt" data-section-name="Pendapatan Rumah Tangga">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.informasi_pendapatan_rumah_tangga')
                        </div>
                    </div>
                </div>

                <!-- I. Sosial dan Kelembagaan -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingI">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseI" aria-expanded="false"
                            aria-controls="collapseI">
                            <div class="survey-section-indicator" style="background-color: #e83e8c;">I</div>
                            <div class="survey-section-icon text-white" style="background-color: #e83e8c;">
                                <i class="mdi mdi-account-multiple-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Sosial dan Kelembagaan</span>
                                <span class="survey-section-desc">Struktur organisasi masyarakat</span>
                            </div>
                            @if(($sectionCounts['I'] ?? 0) > 0)
                                <span class="badge bg-primary ms-auto">{{ $sectionCounts['I'] }} Responden</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseI" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- Import Action Bar --}}
                            <div class="import-action-bar mb-3">
                                <button type="button" class="btn btn-success text-white btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#importModal" data-section="sosial-kelembagaan"
                                    data-route="{{ route('forms.import_sosial_kelembagaan', $knmp->nama) }}">
                                    <i class="mdi mdi-file-excel me-1"></i>Import Excel
                                </button>
                                <button type="button"
                                    class="btn btn-secondary text-white btn-sm ms-2 btn-select-responden"
                                    data-bs-toggle="modal" data-bs-target="#selectRespondenModal"
                                    data-section="sosial-kelembagaan" data-section-name="Sosial dan Kelembagaan">
                                    <i class="mdi mdi-download me-1"></i>Download Template
                                </button>
                            </div>
                            @include('survey.forms.form_layouts.sosial_dan_kelembagaan')
                        </div>
                    </div>
                </div>

                <!-- J. Bukti Pendukung -->
                <div class="accordion-item survey-accordion-item">
                    <h2 class="accordion-header" id="headingJ">
                        <button class="accordion-button collapsed survey-accordion-btn" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseJ" aria-expanded="false"
                            aria-controls="collapseJ">
                            <div class="survey-section-indicator" style="background-color: #fd7e14;">J</div>
                            <div class="survey-section-icon text-white" style="background-color: #fd7e14;">
                                <i class="mdi mdi-file-document-multiple-outline"></i>
                            </div>
                            <div class="survey-section-content">
                                <span class="survey-section-title">Bukti Pendukung</span>
                                <span class="survey-section-desc">Upload dokumen dan foto</span>
                            </div>
                            @if(($sectionCounts['J'] ?? 0) > 0)
                                <span class="badge bg-success ms-auto">{{ $sectionCounts['J'] }} File</span>
                            @else
                                <span class="badge bg-secondary ms-auto">Belum Ada</span>
                            @endif
                        </button>
                    </h2>
                    <div id="collapseJ" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                        <div class="accordion-body survey-accordion-body">
                            {{-- UPLOAD ZONE --}}
                            <form action="{{ route('forms.store_bukti_upload') }}" method="POST"
                                enctype="multipart/form-data" id="uploadForm">
                                @csrf
                                <input type="hidden" name="knmp_id" value="{{ $knmp->id }}">

                                <div class="card border-0 shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <label class="form-label fw-bold text-primary">
                                                    <i class="mdi mdi-image-outline me-1"></i>Bukti/Foto Sebelum (Before)
                                                </label>
                                                <p class="text-muted small mb-2">Upload kondisi awal sebelum intervensi/pembangunan (Maks. 10MB)</p>
                                                <input type="file" name="file_before[]" id="input_before" class="form-control" accept="image/*,.pdf" multiple onchange="previewMultipleImages(this, 'before')">
                                                <div id="preview_before_container" class="mt-2 d-flex flex-wrap gap-2" style="display: none;"></div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-success">
                                                    <i class="mdi mdi-image-check-outline me-1"></i>Bukti/Foto Sesudah (After)
                                                </label>
                                                <p class="text-muted small mb-2">Upload kondisi akhir sesudah intervensi/pembangunan (Maks. 10MB)</p>
                                                <input type="file" name="file_after[]" id="input_after" class="form-control" accept="image/*,.pdf" multiple onchange="previewMultipleImages(this, 'after')">
                                                <div id="preview_after_container" class="mt-2 d-flex flex-wrap gap-2" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end mb-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="mdi mdi-upload me-1"></i>Upload File
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">
                            
                            {{-- EXISTING UPLOADED FILES --}}
                            @include('survey.forms.form_layouts.evidence')
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let errorSection = "{{ old('active_section') ?? 'collapseOne' }}";
                let el = document.getElementById(errorSection);
                if (el) {
                    new bootstrap.Collapse(el, {
                        show: true
                    });
                }
            });
        </script>
    @endif

    <link rel="stylesheet" href="{{ asset('css/evidence-custom.css') }}">
    <style>
        /* ================================================= */
        /* SURVEY HEADER CARD STYLES */
        /* ================================================= */
        .survey-header-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            margin-bottom: var(--space-3);
        }

        .survey-card-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            padding: 0.75rem 1rem;
        }

        .survey-header-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .survey-header-icon i {
            font-size: 18px;
            color: #fff;
        }

        /* ================================================= */
        /* LOCATION INFO STYLES */
        /* ================================================= */
        .location-info-card {
            background: linear-gradient(145deg, #f8fafc, #f1f5f9);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            height: 100%;
        }

        .location-header {
            display: flex;
            align-items: center;
            font-size: var(--font-size-base);
        }

        .location-grid {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .location-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }

        .location-item:last-child {
            border-bottom: none;
        }

        .location-label {
            display: flex;
            align-items: center;
            font-size: var(--font-size-xs);
        }

        .location-value .badge {
            font-weight: 500;
            font-size: var(--font-size-xs);
        }

        /* ================================================= */
        /* PROGRESS INFO STYLES */
        /* ================================================= */
        .progress-info-card {
            background: linear-gradient(145deg, #f8fafc, #f1f5f9);
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        .progress-icon-wrapper {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .progress-icon-wrapper i {
            font-size: 18px;
            color: #fff;
        }

        .progress-percentage {
            text-align: right;
        }

        .progress-wrapper .progress {
            height: 8px;
            border-radius: 4px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .bg-gradient-primary {
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%) !important;
        }

        .progress-milestones {
            display: flex;
            justify-content: space-between;
        }

        .progress-milestones .milestone {
            font-size: var(--font-size-xs);
            color: #94a3b8;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .progress-milestones .milestone.active {
            color: #3b82f6;
            font-weight: 600;
        }

        /* ================================================= */
        /* SURVEY ACCORDION STYLES */
        /* ================================================= */
        .survey-sections-wrapper {
            background: #fff;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .survey-accordion {
            border: none !important;
        }

        .survey-accordion-item {
            border: 1px solid #e2e8f0 !important;
            border-radius: 8px !important;
            margin-bottom: 0.5rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .survey-accordion-item:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .survey-accordion-btn {
            background: #fff !important;
            padding: 0.5rem 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem;
            box-shadow: none !important;
        }

        .survey-accordion-btn:not(.collapsed) {
            background: linear-gradient(145deg, #f8fafc, #ffffff) !important;
        }

        .survey-accordion-btn::after,
        .survey-accordion-btn:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
            width: 0.85rem !important;
            height: 0.85rem !important;
            background-size: contain !important;
            background-repeat: no-repeat !important;
            flex-shrink: 0 !important;
            margin-left: 0.75rem !important;
        }

        .survey-section-indicator {
            width: 26px;
            height: 26px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: var(--font-size-xs);
            color: #fff;
            flex-shrink: 0;
        }

        .survey-section-icon {
            width: 28px;
            height: 28px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .survey-section-icon i {
            font-size: 14px;
        }

        .survey-section-content {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            flex-grow: 1;
        }

        .survey-section-title {
            font-weight: 600;
            font-size: var(--font-size-base);
            color: #1e293b;
        }

        .survey-section-desc {
            font-size: var(--font-size-xs);
            color: #64748b;
        }

        /* Section Badge */
        .survey-accordion-btn .badge {
            flex-shrink: 0;
            font-size: var(--font-size-xs);
            padding: 0.25rem 0.5rem;
        }

        .survey-accordion-body {
            padding: 0.75rem 1rem;
            background: #fcfcfd;
            border-top: 1px solid #e2e8f0;
        }

        /* ================================================= */
        /* FORM SECTION HEADER STYLES */
        /* ================================================= */
        .form-section-header {
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-section-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.25);
        }

        .form-section-icon i {
            font-size: 20px;
        }

        .form-section-icon.bg-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        .form-section-icon.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .form-section-icon.bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }

        .form-section-icon.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .form-section-icon.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .form-section-icon.bg-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }

        .form-section-icon.bg-purple {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .form-section-icon.bg-teal {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        }

        .form-section-icon.bg-pink {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }

        .form-section-icon.bg-orange {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        /* ================================================= */
        /* UPLOAD ZONE STYLES */
        /* ================================================= */
        .upload-zone {
            border: 2px dashed #3b82f6;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            background: linear-gradient(145deg, #f0f7ff, #e8f2ff);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-zone:hover {
            border-color: #1e40af;
            background: linear-gradient(145deg, #e8f2ff, #dbeafe);
            transform: translateY(-2px);
        }

        .upload-zone.dragover {
            border-color: #10b981;
            background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        }

        .upload-icon {
            font-size: 40px;
            color: #3b82f6;
            margin-bottom: 15px;
            display: block;
        }

        /* ================================================= */
        /* PREVIEW WRAPPER STYLES */
        /* ================================================= */
        .preview-wrapper {
            display: flex;
            flex-wrap: nowrap;
            gap: 15px;
            overflow-x: auto;
            padding: 10px 0;
        }

        .preview-wrapper::-webkit-scrollbar {
            height: 6px;
        }

        .preview-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .preview-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            border-radius: 3px;
        }

        .preview-item {
            flex: 0 0 auto;
            min-width: 200px;
            max-width: 250px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .preview-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .preview-item .preview-thumb {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            overflow: hidden;
        }

        .preview-item .preview-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .preview-item .preview-thumb .pdf-preview {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preview-item .preview-thumb .pdf-preview i {
            font-size: 50px;
            color: #fff;
        }

        .preview-item .preview-details {
            padding: 12px;
        }

        .preview-item .preview-details .file-name {
            font-weight: 600;
            font-size: 13px;
            color: #1e293b;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .preview-item .preview-details .file-size {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 0;
        }

        /* ================================================= */
        /* RESPONSIVE ADJUSTMENTS */
        /* ================================================= */
        @media (max-width: 768px) {
            .survey-card-header {
                padding: 0.5rem 0.75rem;
            }

            .survey-header-icon {
                width: 32px;
                height: 32px;
            }

            .survey-header-icon i {
                font-size: 16px;
            }

            .survey-accordion-btn {
                padding: 0.4rem 0.6rem !important;
                gap: 0.4rem;
            }

            .survey-section-indicator {
                width: 24px;
                height: 24px;
                font-size: var(--font-size-xs);
            }

            .survey-section-icon {
                display: none;
            }

            .survey-section-title {
                font-size: 0.75rem;
            }

            .survey-section-desc {
                font-size: 0.625rem;
            }
        }

        /* =============================== */
        /* FIX BREADCRUMB POSITION */
        /* =============================== */
        .page-title-right {
            margin-bottom: 0.35rem;
            /* beri jarak ke judul */
        }

        .page-title-right .breadcrumb {
            margin-top: -6px;
            /* naikkan breadcrumb */
        }

        /* =============================== */
        /* IMPORT MODAL STYLES */
        /* =============================== */
        #importModal {
            z-index: 1060 !important;
        }

        #importModal .modal-backdrop {
            z-index: 1055 !important;
        }

        #importModal .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100% - 1rem);
            margin: 0.5rem auto;
        }

        #importModal .modal-content {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            pointer-events: auto;
        }

        #importModal .modal-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 0.75rem 1.25rem;
        }

        #importModal .modal-title {
            font-weight: 600;
        }

        #importModal .modal-header .btn-close {
            filter: brightness(0) invert(1);
            opacity: 0.8;
        }

        #importModal .modal-header .btn-close:hover {
            opacity: 1;
        }

        #importModal .modal-body {
            padding: 1.25rem;
        }

        #importModal .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 0.75rem 1.25rem;
        }

        @media (min-width: 576px) {
            #importModal .modal-dialog {
                min-height: calc(100% - 3.5rem);
                margin: 1.75rem auto;
            }
        }

        /* =============================== */
        /* SELECT RESPONDEN MODAL STYLES */
        /* =============================== */
        #selectRespondenModal .modal-dialog {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100%;
            margin: 0 auto;
            padding: 1rem;
        }

        #selectRespondenModal .modal-content {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }
    </style>

    <script>
        // ==================================
        // INLINE IMAGE PREVIEW (MULTIPLE)
        // ==================================
        const selectedFiles = {
            'before': new DataTransfer(),
            'after': new DataTransfer()
        };

        function previewMultipleImages(input, type) {
            const dt = selectedFiles[type];

            // Add new files to DataTransfer
            for(let i = 0; i < input.files.length; i++) {
                dt.items.add(input.files[i]);
            }
            
            // Sync input files with DataTransfer
            input.files = dt.files;

            renderPreviews(type, input);
        }

        function renderPreviews(type, input) {
            const container = document.getElementById('preview_' + type + '_container');
            const dt = selectedFiles[type];
            container.innerHTML = '';

            if (dt.files.length > 0) {
                container.style.display = 'flex';
                for(let i = 0; i < dt.files.length; i++) {
                    const file = dt.files[i];
                    const wrapper = document.createElement('div');
                    wrapper.className = 'position-relative d-inline-block';

                    const removeBtn = document.createElement('button');
                    removeBtn.className = 'btn btn-danger btn-sm position-absolute rounded-circle p-0 d-flex align-items-center justify-content-center shadow-sm';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.top = '-8px';
                    removeBtn.style.right = '-8px';
                    removeBtn.style.zIndex = '10';
                    removeBtn.innerHTML = '<i class="mdi mdi-close" style="font-size: 12px;"></i>';
                    removeBtn.onclick = function(e) {
                        e.preventDefault();
                        dt.items.remove(i);
                        input.files = dt.files; // Update input
                        renderPreviews(type, input);
                    };

                    if (file.type.startsWith('image/')) {
                        const img = document.createElement('img');
                        img.className = 'img-thumbnail shadow-sm';
                        img.style.height = '80px';
                        img.style.width = '80px';
                        img.style.objectFit = 'cover';
                        
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                        }
                        reader.readAsDataURL(file);
                        
                        wrapper.appendChild(img);
                    } else {
                        const fileDiv = document.createElement('div');
                        fileDiv.className = 'img-thumbnail shadow-sm d-flex align-items-center justify-content-center bg-light';
                        fileDiv.style.height = '80px';
                        fileDiv.style.width = '80px';
                        fileDiv.innerHTML = '<i class="mdi mdi-file-pdf-box text-danger" style="font-size: 32px;"></i>';
                        wrapper.appendChild(fileDiv);
                    }

                    wrapper.appendChild(removeBtn);
                    container.appendChild(wrapper);
                }
            } else {
                container.style.display = 'none';
            }
        }
    </script>

    {{-- Import Modal --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="importForm" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 mb-3">
                            <small><i class="mdi mdi-information me-1"></i> Pastikan format file sesuai template yang
                                telah didownload.</small>
                        </div>
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Pilih File Excel (.xlsx, .xls, .csv)</label>
                            <input class="form-control" type="file" id="importFile" name="file"
                                accept=".xlsx, .xls, .csv" required>
                        </div>
                        <input type="hidden" name="knmp_id" value="{{ $knmp->id }}">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-upload me-1"></i>Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var importModal = document.getElementById('importModal');
            if (importModal) {
                importModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var route = button.getAttribute('data-route');
                    var section = button.getAttribute('data-section');
                    var modalForm = importModal.querySelector('#importForm');

                    // Update form action
                    modalForm.action = route;
                });
            }
        });
    </script>

    {{-- Select Responden Modal for Template Download --}}
    <div class="modal fade" id="selectRespondenModal" tabindex="-1" aria-labelledby="selectRespondenModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header"
                    style="background: #f8f9fa; color: #333; border-radius: 12px 12px 0 0; border-bottom: 1px solid #dee2e6;">
                    <h5 class="modal-title" id="selectRespondenModalLabel">
                        <i class="mdi mdi-account-check me-2 text-primary"></i>Pilih Responden untuk Template
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="mdi mdi-information me-1"></i> Pilih responden yang akan disertakan dalam
                            template. <span class="badge rounded-circle bg-success ms-1" style="width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;"><i class="mdi mdi-check" style="font-size:0.7rem;"></i></span> menandakan responden sudah memiliki data pada kuesioner ini.</small>
                    </div>

                    <input type="hidden" id="selectedSection" value="">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="selectAllResponden">
                                <i class="mdi mdi-checkbox-multiple-marked me-1"></i>Pilih Semua
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="deselectAllResponden">
                                <i class="mdi mdi-checkbox-multiple-blank-outline me-1"></i>Hapus Semua
                            </button>
                        </div>
                        <span class="badge bg-primary" id="selectedCount">0 dipilih</span>
                    </div>

                    <div class="responden-list-container"
                        style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 8px; padding: 10px;">
                        @if(isset($respondenList) && count($respondenList) > 0)
                            @foreach($respondenList as $responden)
                                <div class="responden-check-item d-flex align-items-center mb-1 px-2 py-2"
                                    style="background: #f8f9fa; border-radius: 6px; transition: background 0.2s;"
                                    id="check-item-{{ $responden->id }}">
                                    <div class="ps-1 pe-2 flex-shrink-0">
                                        <input class="form-check-input responden-checkbox" type="checkbox"
                                            value="{{ $responden->id }}" id="responden_{{ $responden->id }}"
                                            data-nama="{{ $responden->nama_responden }}">
                                    </div>
                                    <label class="d-flex justify-content-between align-items-center w-100 mb-0"
                                        for="responden_{{ $responden->id }}" style="cursor:pointer;">
                                        <strong class="text-dark">{{ $responden->nama_responden }}</strong>
                                        <span class="d-flex align-items-center gap-2">
                                            <span class="text-muted small">ID: {{ $responden->id }}</span>
                                            <span class="responden-data-badge badge rounded-circle bg-success"
                                                style="width:20px;height:20px;display:none;align-items:center;justify-content:center;padding:0;">
                                                <i class="mdi mdi-check" style="font-size:0.7rem;"></i>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4 text-muted">
                                <i class="mdi mdi-account-off mdi-48px"></i>
                                <p class="mt-2 mb-0">Belum ada responden. Silakan tambahkan responden di Section C terlebih
                                    dahulu.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a href="#" id="downloadTemplateBtn" class="btn btn-primary" @if(!isset($respondenList) || count($respondenList) == 0) disabled @endif>
                        <i class="mdi mdi-download me-1"></i>Download Template
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectRespondenModal = document.getElementById('selectRespondenModal');

            if (selectRespondenModal) {
                // When modal opens, set the section from the button that triggered it
                selectRespondenModal.addEventListener('show.bs.modal', function (event) {
                    var button = event.relatedTarget;
                    var section = button.getAttribute('data-section');
                    var sectionName = button.getAttribute('data-section-name');

                    document.getElementById('selectedSection').value = section;
                    document.getElementById('selectRespondenModalLabel').innerHTML =
                        '<i class="mdi mdi-account-check me-2"></i>Pilih Responden - ' + sectionName;

                    // Reset checkboxes
                    document.querySelectorAll('.responden-checkbox').forEach(function (cb) {
                        cb.checked = false;
                    });

                    // Toggle badges based on active section using JS data
                    var respondenWithData = @json($respondenWithData ?? []);
                    var activeIds = respondenWithData[section] || [];

                    document.querySelectorAll('.responden-checkbox').forEach(function (cb) {
                        var respondenId = parseInt(cb.value, 10);
                        var item  = cb.closest('.responden-check-item');
                        var badge = item ? item.querySelector('.responden-data-badge') : null;

                        if (item && badge) {
                            var hasData = activeIds.map(function(id){ return parseInt(id, 10); }).indexOf(respondenId) !== -1;
                            if (hasData) {
                                badge.style.display = 'inline-flex';
                                item.style.background = '#e8f5e9';
                            } else {
                                badge.style.display = 'none';
                                item.style.background = '#f8f9fa';
                            }
                        }
                    });

                    updateSelectedCount();
                    updateDownloadLink();
                });

                // Select All button
                document.getElementById('selectAllResponden').addEventListener('click', function () {
                    document.querySelectorAll('.responden-checkbox').forEach(function (cb) {
                        cb.checked = true;
                    });
                    updateSelectedCount();
                    updateDownloadLink();
                });

                // Deselect All button
                document.getElementById('deselectAllResponden').addEventListener('click', function () {
                    document.querySelectorAll('.responden-checkbox').forEach(function (cb) {
                        cb.checked = false;
                    });
                    updateSelectedCount();
                    updateDownloadLink();
                });

                // Update count when checkbox changes
                document.querySelectorAll('.responden-checkbox').forEach(function (cb) {
                    cb.addEventListener('change', function () {
                        updateSelectedCount();
                        updateDownloadLink();
                    });
                });
            }

            function updateSelectedCount() {
                var count = document.querySelectorAll('.responden-checkbox:checked').length;
                document.getElementById('selectedCount').textContent = count + ' dipilih';
            }

            function updateDownloadLink() {
                var section = document.getElementById('selectedSection').value;
                var checkedBoxes = document.querySelectorAll('.responden-checkbox:checked');
                var baseUrl = "{{ route('forms.download_template', 'SECTION_PLACEHOLDER') }}".replace('SECTION_PLACEHOLDER', section);

                if (checkedBoxes.length > 0) {
                    var params = [];
                    checkedBoxes.forEach(function (cb) {
                        params.push('responden_ids[]=' + cb.value);
                    });
                    baseUrl += '?' + params.join('&');
                }

                var downloadBtn = document.getElementById('downloadTemplateBtn');
                downloadBtn.href = baseUrl;
                downloadBtn.classList.remove('disabled');
            }
        });
    </script>
    <script src="{{ asset('js/evidence-custom.js') }}"></script>
    @endsection