@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-left">
                <h4 class="page-title"><i class="mdi mdi-office-building me-2"></i>Detail Konstruksi</h4>
                <small class="text-muted">{{ $knmp->nama }}</small>
            </div>
            <div class="page-title-right">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('konstruksi.index') }}">Konstruksi</a></li>
                        <li class="breadcrumb-item active">{{ $knmp->nama }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if($konstruksi && $konstruksi->penyediaJasa)
<div class="row mb-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="me-3" style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#3b82f6,#1e40af);display:flex;align-items:center;justify-content:center;">
                    <i class="mdi mdi-account-hard-hat text-white" style="font-size:1.2rem;"></i>
                </div>
                <div>
                    <small class="text-muted text-uppercase" style="font-size:0.7rem;">Penyedia Jasa Konstruksi</small>
                    <h6 class="mb-0 fw-semibold">{{ $konstruksi->penyediaJasa->nama }}</h6>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="mdi mdi-settings-outline me-2 text-primary"></i>Pengaturan Konstruksi</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('konstruksi.update_settings', $knmp->nama) }}" method="POST">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal Mulai Konstruksi</label>
                    <input type="date" class="form-control" name="tanggal_mulai" value="{{ $konstruksi && $konstruksi->tanggal_mulai ? $konstruksi->tanggal_mulai->format('Y-m-d') : '' }}" required>
                    <small class="text-muted">Tanggal ini menjadi acuan perhitungan minggu ke-1.</small>
                </div>
                <div class="col-md-8 text-md-end mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="mdi mdi-sync me-1"></i> Simpan & Sinkronisasi Realisasi
                    </button>
                    @if($konstruksi && $konstruksi->tanggal_mulai)
                    <button type="button" onclick="event.preventDefault(); document.getElementById('sync-form').submit();" class="btn btn-outline-info px-4 ms-2">
                        <i class="mdi mdi-refresh me-1"></i> Sinkronisasi Ulang
                    </button>
                    @endif
                </div>
            </div>
        </form>
        <form id="sync-form" action="{{ route('konstruksi.sync_realisasi', $knmp->nama) }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</div>

<ul class="nav nav-tabs" id="konstruksiTabs" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabTimeline"><i class="mdi mdi-chart-line me-1"></i>Timeline <span class="badge bg-primary ms-1">{{ $timeline->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabProgres"><i class="mdi mdi-calendar-clock me-1"></i>Progres Harian <span class="badge bg-info ms-1">{{ $progresHarian->count() }}</span></a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabDokumentasi"><i class="mdi mdi-camera me-1"></i>Dokumentasi <span class="badge bg-success ms-1">{{ $dokumentasi->count() }}</span></a></li>
</ul>

<div class="tab-content mt-3">
    <div class="tab-pane fade show active" id="tabTimeline">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#ef4444,#dc2626);border:none;">
                <h5 class="card-title mb-0 text-white"><i class="mdi mdi-chart-line me-2"></i>Timeline (Kurva S)</h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addTimelineModal"><i class="mdi mdi-plus me-1"></i>Tambah</button>
            </div>
            <div class="card-body">
                @if($timeline->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead class="table-light"><tr><th>Minggu</th><th>Rencana %</th><th>Realisasi %</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($timeline as $t)
                            <tr>
                                <td>{{ $t->periode_mingguan }}</td>
                                <td>{{ $t->bobot_rencana_kumulatif ?? '-' }}%</td>
                                <td>{{ $t->bobot_realisasi_kumulatif ?? '-' }}%</td>
                                <td>
                                    @if($t->status==='on_track')<span class="badge bg-success">On Track</span>
                                    @elseif($t->status==='terlambat')<span class="badge bg-danger">Terlambat</span>
                                    @elseif($t->status==='selesai')<span class="badge bg-primary">Selesai</span>
                                    @else<span class="badge bg-secondary">-</span>@endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else <p class="text-muted text-center py-3">Belum ada data timeline.</p> @endif
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="tabProgres">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#06b6d4,#0891b2);border:none;">
                <h5 class="card-title mb-0 text-white"><i class="mdi mdi-calendar-clock me-2"></i>Progres Harian</h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addProgresModal"><i class="mdi mdi-plus me-1"></i>Tambah</button>
            </div>
            <div class="card-body">
                @if($progresHarian->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead class="table-light"><tr><th>Tanggal</th><th>Progres %</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @foreach($progresHarian as $p)
                            <tr><td>{{ $p->tanggal->format('d/m/Y') }}</td><td>{{ $p->progres ?? '-' }}%</td><td>{{ $p->keterangan ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else <p class="text-muted text-center py-3">Belum ada progres harian.</p> @endif
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="tabDokumentasi">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="background:linear-gradient(135deg,#10b981,#059669);border:none;">
                <h5 class="card-title mb-0 text-white"><i class="mdi mdi-camera me-2"></i>Dokumentasi</h5>
                <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addDokumentasiModal"><i class="mdi mdi-plus me-1"></i>Tambah</button>
            </div>
            <div class="card-body">
                @if($dokumentasi->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead class="table-light"><tr><th>Tanggal</th><th>Jenis</th><th>File</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            @foreach($dokumentasi as $d)
                            <tr><td>{{ $d->tanggal ? $d->tanggal->format('d/m/Y') : '-' }}</td><td><span class="badge bg-info text-uppercase">{{ $d->jenis_foto ?? '-' }}</span></td><td><a href="{{ $d->file_url }}" target="_blank">Lihat</a></td><td>{{ $d->keterangan ?? '-' }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else <p class="text-muted text-center py-3">Belum ada dokumentasi.</p> @endif
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="addTimelineModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px;overflow:hidden;">
<form action="{{ route('konstruksi.timeline.store', $knmp->nama) }}" method="POST">@csrf
<div class="modal-header" style="background:linear-gradient(135deg,#ef4444,#dc2626);border:none;"><h5 class="modal-title text-white">Tambah Timeline</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<div class="modal-body p-4">
    <div class="mb-3"><label class="form-label">Minggu Ke *</label><input type="number" class="form-control" name="periode_mingguan" min="1" required></div>
    <div class="row"><div class="col-6 mb-3"><label class="form-label">Rencana %</label><input type="number" step="0.01" class="form-control" name="bobot_rencana_kumulatif" min="0" max="100"></div><div class="col-6 mb-3"><label class="form-label">Realisasi %</label><input type="number" step="0.01" class="form-control" name="bobot_realisasi_kumulatif" min="0" max="100"></div></div>
    <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="">-</option><option value="on_track">On Track</option><option value="terlambat">Terlambat</option><option value="selesai">Selesai</option></select></div>
</div>
<div class="modal-footer" style="background:#f8fafc;"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-danger">Simpan</button></div>
</form></div></div></div>

<div class="modal fade" id="addProgresModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px;overflow:hidden;">
<form action="{{ route('konstruksi.progres_harian.store', $knmp->nama) }}" method="POST">@csrf
<div class="modal-header" style="background:linear-gradient(135deg,#06b6d4,#0891b2);border:none;"><h5 class="modal-title text-white">Tambah Progres Harian</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<div class="modal-body p-4">
    <div class="mb-3"><label class="form-label">Tanggal *</label><input type="date" class="form-control" name="tanggal" required></div>
    <div class="mb-3"><label class="form-label">Progres %</label><input type="number" step="0.01" class="form-control" name="progres" min="0" max="100"></div>
    <div class="mb-3"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="3"></textarea></div>
</div>
<div class="modal-footer" style="background:#f8fafc;"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-info text-white">Simpan</button></div>
</form></div></div></div>

<div class="modal fade" id="addDokumentasiModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px;overflow:hidden;">
<form action="{{ route('konstruksi.dokumentasi.store', $knmp->nama) }}" method="POST">@csrf
<div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669);border:none;"><h5 class="modal-title text-white">Tambah Dokumentasi</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<div class="modal-body p-4">
    <div class="mb-3"><label class="form-label">Tanggal</label><input type="date" class="form-control" name="tanggal"></div>
    <div class="mb-3"><label class="form-label">Jenis Foto</label><select class="form-select" name="jenis_foto"><option value="">-</option><option value="progress">Progress</option><option value="kondisi">Kondisi</option><option value="selesai">Selesai</option></select></div>
    <div class="mb-3"><label class="form-label">File URL *</label><input type="text" class="form-control" name="file_url" required></div>
    <div class="mb-3"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="3"></textarea></div>
</div>
<div class="modal-footer" style="background:#f8fafc;"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success">Simpan</button></div>
</form></div></div></div>
@endsection
