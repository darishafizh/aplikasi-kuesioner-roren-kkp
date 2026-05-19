{{-- Partial: Reusable stage index page --}}
{{-- Usage: @include('knmp.tahap._stage_index', ['title' => ..., 'icon' => ..., 'color' => ..., 'knmps' => ..., 'showRoute' => ..., 'stageName' => ...]) --}}

@extends('layouts.app')

@section('content')

<div class="row mt-2">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-left">
                <h4 class="page-title"><i class="mdi {{ $icon ?? 'mdi-clipboard-outline' }} me-2"></i>{{ $title }}</h4>
                <small class="text-muted">Daftar KNMP pada tahap {{ $stageName ?? $title }}.</small>
            </div>
            <div class="page-title-right">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Beranda</a></li>
                        <li class="breadcrumb-item">KNMP</li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="row mb-2">
    {{-- Card 1: Total --}}
    <div class="col-lg-4 col-md-6">
        <div class="card widget-flat border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 d-block">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">TOTAL {{ $stageName ?? $title }}</h5>
                    <div style="background: #f0f9ff; color: #0369a1; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="{{ $lucideIcon ?? 'clipboard-list' }}" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;">{{ $knmps->count() }}</h3>
                <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">KNMP pada tahap ini</p>
            </div>
        </div>
    </div>
    
    {{-- Card 2: Hub --}}
    <div class="col-lg-4 col-md-6">
        <div class="card widget-flat border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 d-block">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; letter-spacing: 0.5px;">TOTAL HUB</h5>
                    <div style="background: #ecfdf5; color: #047857; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="anchor" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;">
                    {{ $knmps->filter(function($k) { 
                        return stripos($k->status ?? '', 'Hub') !== false; 
                    })->count() }}
                </h3>
                <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Berstatus Hub</p>
            </div>
        </div>
    </div>

    {{-- Card 3: Penyangga --}}
    <div class="col-lg-4 col-md-6">
        <div class="card widget-flat border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body p-2 d-block">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="fw-semibold mb-0" style="color: #475569; font-size: 0.75rem; letter-spacing: 0.5px;">TOTAL PENYANGGA</h5>
                    <div style="background: #fffbeb; color: #b45309; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i data-lucide="life-buoy" style="width: 18px; height: 18px;"></i>
                    </div>
                </div>
                <h3 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1e293b; line-height: 1;">
                    {{ $knmps->filter(function($k) { 
                        $status = $k->status ?? (in_array($k->batch_id, [1, 2]) ? 'Penyangga' : '');
                        return stripos($status, 'Penyangga') !== false; 
                    })->count() }}
                </h3>
                <p class="mb-0" style="color: #64748b; font-size: 0.8rem;">Berstatus Penyangga</p>
            </div>
        </div>
    </div>
</div>

{{-- Slot for extra content (e.g. import form on Usulan) --}}
@yield('stage_extra')

{{-- Table --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h4 class="header-title mb-0">Daftar KNMP — {{ $stageName ?? $title }}</h4>
                    @if(Auth::user()->isSuperAdmin())
                    <div class="d-flex align-items-center gap-1">
                        @if(!($hideStageActions ?? false))
                            {{-- Standardized Template Button --}}
                            <button type="button" class="btn btn-secondary btn-sm d-flex align-items-center justify-content-center gap-1"
                                data-bs-toggle="modal" data-bs-target="#templateStageModal" style="font-weight:500; height:32px; padding:0 24px;">
                                <i data-lucide="download" style="width: 16px; height: 16px;"></i>&nbsp;Template
                            </button>

                            {{-- Standardized Import Button --}}
                            <button type="button" class="btn btn-success btn-sm d-flex align-items-center justify-content-center gap-1"
                                data-bs-toggle="modal" data-bs-target="#importStageModal" style="font-weight:500; height:32px; padding:0 16px;">
                                <i data-lucide="upload" style="width: 16px; height: 16px;"></i> Import
                            </button>

                            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center gap-1" 
                                data-bs-toggle="modal" data-bs-target="#batchMoveModal" style="font-weight:500; height:32px; padding:0 16px;">
                                <i data-lucide="arrow-left-right" style="width: 16px; height: 16px;"></i> Pindah Tahap
                            </button>
                        @endif

                        
                        <form action="{{ route('knmp_tahap.batch_destroy') }}" method="POST" id="batchDeleteForm" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data KNMP yang dipilih?')">
                            @csrf
                            @method('DELETE')
                            <div id="batchDeleteIds"></div>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-stage-action" id="btnBatchDelete" style="display:none;">
                                <i data-lucide="trash-2" class="me-1"></i> Hapus Terpilih (<span id="batchDeleteCount">0</span>)
                            </button>
                        </form>
                    </div>
                    @endif
                </div>

                <div class="table-responsive">
                    @php
                        $defaultColumns = [
                            ['label' => 'Lokasi KNMP', 'key' => 'nama', 'type' => 'lokasi'],
                            ['label' => 'Status', 'key' => 'status', 'type' => 'badge_status'],
                        ];
                        $tableColumns = $columns ?? $defaultColumns;
                        $totalCols = count($tableColumns) + 2; // +2 for checkbox and aksi
                    @endphp
                    <table id="scroll-horizontal-datatable" class="table table-striped w-100 nowrap">
                        <thead>
                            <tr>
                                <th style="width:20px;"><input type="checkbox" id="checkAll"></th>
                                @foreach($tableColumns as $col)
                                    <th>{{ $col['label'] }}</th>
                                @endforeach
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($knmps as $knmp)
                                <tr>
                                    <td><input type="checkbox" class="knmp-check" value="{{ $knmp->id }}"></td>
                                    @foreach($tableColumns as $col)
                                        <td>
                                            @if(($col['type'] ?? '') === 'lokasi')
                                                <div>
                                                    <div class="fw-medium text-dark mb-0" style="font-size: 0.82rem;">{{ $knmp->nama }}</div>
                                                    <div class="text-muted" style="font-size: 0.7rem; opacity: 0.8;">
                                                        @php
                                                            $locParts = array_filter([
                                                                $knmp->kecamatan ? ucwords(strtolower($knmp->kecamatan)) : null,
                                                                $knmp->kabupaten ? ucwords(strtolower($knmp->kabupaten)) : null,
                                                                $knmp->provinsi ? ucwords(strtolower($knmp->provinsi)) : null
                                                            ]);
                                                        @endphp
                                                        {{ implode(', ', $locParts) }}
                                                    </div>
                                                </div>
                                            @elseif(($col['type'] ?? '') === 'badge_status')
                                                @php
                                                    $statusVal = $knmp->{$col['key']} ?? '';
                                                    if (empty($statusVal) && in_array($knmp->batch_id, [1, 2])) {
                                                        $statusVal = 'Penyangga';
                                                    }
                                                @endphp
                                                @if(stripos($statusVal, 'Hub') !== false)
                                                    <span class="badge bg-success">Hub</span>
                                                @elseif(stripos($statusVal, 'Penyangga') !== false)
                                                    <span class="badge bg-warning text-dark">Penyangga</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $statusVal ?: 'N/A' }}</span>
                                                @endif
                                            @elseif(($col['type'] ?? '') === 'badge_primary')
                                                <span class="badge bg-primary text-uppercase">{{ $knmp->{$col['key']} }}</span>
                                            @elseif(($col['format'] ?? '') === 'ucwords')
                                                {{ ucwords(strtolower($knmp->{$col['key']} ?? 'N/A')) }}
                                            @elseif(($col['type'] ?? '') === 'progres_bar')
                                                @php
                                                    $progress = $knmp->latestProgresNasional->progres ?? 0;
                                                @endphp
                                                <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                                                    <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px; background: #f1f5f9; overflow: hidden;">
                                                        <div class="progress-bar" role="progressbar" 
                                                            style="width: {{ $progress }}%; border-radius: 10px; background: linear-gradient(90deg, #2563eb, #3b82f6);" 
                                                            aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="fw-bold" style="font-size: 0.75rem; color: #1e293b; min-width: 35px;">{{ number_format($progress, 0) }}%</span>
                                                </div>
                                            @elseif(($col['type'] ?? '') === 'raw')
                                                {!! $col['render']($knmp) !!}
                                            @else
                                                {{ $knmp->{$col['key']} ?? 'N/A' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="action-buttons">
                                        @if(!(isset($hideDetailAction) && $hideDetailAction))
                                            <a href="{{ route($showRoute, $knmp->nama) }}"
                                                class="btn btn-action btn-action-primary" title="Detail">
                                                <i data-lucide="eye"></i>
                                            </a>
                                        @endif
                                        @if(isset($extraActions) && is_callable($extraActions))
                                            {!! $extraActions($knmp) !!}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODALS — Single & Batch Move Stage                           --}}
{{-- ============================================================ --}}
@if(Auth::user()->isSuperAdmin())

{{-- Single Move Modal --}}
<div class="modal fade" id="moveStageModal" tabindex="-1" aria-labelledby="moveStageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="moveStageForm">
                @csrf
                <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#f59e0b,#d97706);border:none;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="mdi mdi-swap-horizontal text-white" style="font-size:1rem;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title mb-0 text-white" id="moveStageLabel" style="font-size:.8rem;">Pindah Tahap</h6>
                            <small style="color:rgba(255,255,255,.7);font-size:.65rem;" id="moveStageKnmpName"></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size:.6rem;"></button>
                </div>
                <div class="modal-body px-3 py-3">
                    <div class="mb-2">
                        <label class="form-label">Tahap Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="tahap_baru" required>
                            <option value="">Pilih Tahap</option>
                            <option value="usulan">Usulan</option>
                            <option value="survey">Survey</option>
                            <option value="ded">DED</option>
                            <option value="lelang">Lelang</option>
                            <option value="konstruksi">Konstruksi</option>
                            <option value="serah_terima">Serah Terima</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Alasan perpindahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer px-3 py-2" style="background:#f8fafc;">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white btn-sm">
                        <i class="mdi mdi-swap-horizontal me-1"></i>Pindah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Batch Move Modal --}}
<div class="modal fade" id="batchMoveModal" tabindex="-1" aria-labelledby="batchMoveLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('knmp_tahap.batch_move') }}" id="batchMoveForm">
                @csrf
                <div class="modal-header py-2 px-3" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9);border:none;">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:32px;height:32px;background:rgba(255,255,255,.2);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                            <i class="mdi mdi-swap-horizontal-bold text-white" style="font-size:1rem;"></i>
                        </div>
                        <div>
                            <h6 class="modal-title mb-0 text-white" id="batchMoveLabel" style="font-size:.8rem;">Batch Pindah Tahap</h6>
                            <small style="color:rgba(255,255,255,.7);font-size:.65rem;" id="batchCount">0 KNMP dipilih</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" style="font-size:.6rem;"></button>
                </div>
                <div class="modal-body px-3 py-3">
                    <div id="batchKnmpIds"></div>
                    <div class="mb-2">
                        <label class="form-label">Tahap Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="tahap_baru" required>
                            <option value="">Pilih Tahap</option>
                            <option value="usulan">Usulan</option>
                            <option value="survey">Survey</option>
                            <option value="ded">DED</option>
                            <option value="lelang">Lelang</option>
                            <option value="konstruksi">Konstruksi</option>
                            <option value="serah_terima">Serah Terima</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" name="keterangan" rows="2" placeholder="Alasan perpindahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer px-3 py-2" style="background:#f8fafc;">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm text-white" style="background:#8b5cf6;">
                        <i class="mdi mdi-swap-horizontal-bold me-1"></i>Batch Pindah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@endif

{{-- Import Modal - Modern Upload --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            {{-- Header with gradient --}}
            <div class="modal-header py-3 px-4" style="background:linear-gradient(135deg,#10b981,#059669);border:none;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,.15);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i data-lucide="file-spreadsheet" class="text-white" style="width:18px;height:18px;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 text-white" style="font-size:.85rem;">Import Data</h6>
                        <small style="color:rgba(255,255,255,.7);font-size:.65rem;">Upload file Excel (.xlsx, .csv)</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Body with drag & drop --}}
            <form action="{{ $importRoute ?? route('usulan.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-body px-4 py-4">
                    {{-- Drop Zone --}}
                    <div class="import-dropzone" id="importDropzone">
                        <div class="import-dropzone-icon">
                            <i data-lucide="cloud-upload" style="width:36px;height:36px;"></i>
                        </div>
                        <p class="import-dropzone-text">Drag & drop file di sini</p>
                        <p class="import-dropzone-sub">atau klik untuk memilih file</p>
                        <input type="file" name="file" id="importFileInput" accept=".xlsx,.xls,.csv" required hidden>
                    </div>
                    
                    {{-- File preview (hidden by default) --}}
                    <div class="import-file-preview" id="importFilePreview" style="display:none;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="import-file-icon">
                                <i data-lucide="file-check-2"></i>
                            </div>
                            <div class="flex-1" style="min-width:0;flex:1;">
                                <p class="import-file-name" id="importFileName">file.xlsx</p>
                                <p class="import-file-size" id="importFileSize">0 KB</p>
                            </div>
                            <button type="button" class="import-file-remove" id="importFileRemove">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                    </div>

                    <small class="text-muted d-block mt-3" style="font-size:.6rem;">
                        Format: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code> — Maks 10MB
                    </small>
                </div>
                
                <div class="modal-footer px-4 py-3" style="background:#f8fafc;border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm" id="importSubmitBtn" disabled>
                        <i data-lucide="upload" class="me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Single move stage
    document.querySelectorAll('.btn-move-stage').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var nama = this.dataset.knmpNama;
            document.getElementById('moveStageKnmpName').textContent = nama;
            document.getElementById('moveStageForm').action = '/knmp-tahap/' + encodeURIComponent(nama) + '/move';
        });
    });

    // Checkbox & Batch actions logic
    var checkAll = document.getElementById('checkAll');
    var btnBatchDelete = document.getElementById('btnBatchDelete');
    var batchDeleteCount = document.getElementById('batchDeleteCount');
    var batchDeleteIdsContainer = document.getElementById('batchDeleteIds');

    var selectedKnmpIds = new Set();

    function updateBatchButtons() {
        if (btnBatchDelete) {
            if (selectedKnmpIds.size > 0) {
                btnBatchDelete.style.display = 'inline-block';
                batchDeleteCount.textContent = selectedKnmpIds.size;
            } else {
                btnBatchDelete.style.display = 'none';
            }
        }
    }

    function initCheckboxes() {
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable) {
            var $table = jQuery('#scroll-horizontal-datatable');
            if ($table.length === 0) return;

            // Event delegation on table body
            $table.find('tbody').on('change', '.knmp-check', function() {
                if (this.checked) {
                    selectedKnmpIds.add(this.value);
                } else {
                    selectedKnmpIds.delete(this.value);
                }
                updateBatchButtons();
                updateCheckAllState();
            });

            // Restore checked state when DataTable is drawn (pagination/search)
            $table.on('draw.dt', function() {
                var dt = $table.DataTable();
                var checkboxes = dt.rows({ page: 'current' }).nodes().to$().find('.knmp-check');
                checkboxes.each(function() {
                    this.checked = selectedKnmpIds.has(this.value);
                });
                updateCheckAllState();
            });

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    var isChecked = this.checked;
                    var dt = $table.DataTable();
                    // Select all rows based on current search filter
                    var checkboxes = dt.rows({ search: 'applied' }).nodes().to$().find('.knmp-check');
                    
                    checkboxes.each(function() {
                        this.checked = isChecked;
                        if (isChecked) {
                            selectedKnmpIds.add(this.value);
                        } else {
                            selectedKnmpIds.delete(this.value);
                        }
                    });
                    updateBatchButtons();
                });
            }

            function updateCheckAllState() {
                if (!checkAll) return;
                var dt = $table.DataTable();
                var checkboxes = dt.rows({ page: 'current' }).nodes().to$().find('.knmp-check');
                var allChecked = checkboxes.length > 0;
                checkboxes.each(function() {
                    if (!this.checked) allChecked = false;
                });
                checkAll.checked = allChecked;
            }

            // Init state
            if ($table.DataTable && typeof $table.DataTable().rows === 'function') {
                var initialCheckboxes = $table.DataTable().rows().nodes().to$().find('.knmp-check:checked');
                initialCheckboxes.each(function() {
                    selectedKnmpIds.add(this.value);
                });
            }
            updateBatchButtons();

        } else {
            // Fallback native DOM
            document.querySelectorAll('.knmp-check').forEach(function(cb) {
                cb.addEventListener('change', function() {
                    if (this.checked) selectedKnmpIds.add(this.value);
                    else selectedKnmpIds.delete(this.value);
                    updateBatchButtons();
                });
                if (cb.checked) selectedKnmpIds.add(cb.value);
            });

            if (checkAll) {
                checkAll.addEventListener('change', function() {
                    var isChecked = this.checked;
                    document.querySelectorAll('.knmp-check').forEach(function(cb) { 
                        cb.checked = isChecked;
                        if (isChecked) selectedKnmpIds.add(cb.value);
                        else selectedKnmpIds.delete(cb.value);
                    });
                    updateBatchButtons();
                });
            }
            updateBatchButtons();
        }
    }

    setTimeout(initCheckboxes, 300); // Give time for DataTable to initialize if async

    if (btnBatchDelete) {
        btnBatchDelete.addEventListener('click', function() {
            if (selectedKnmpIds.size === 0) return;
            
            batchDeleteIdsContainer.innerHTML = '';
            selectedKnmpIds.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; 
                inp.name = 'knmp_ids[]'; 
                inp.value = id;
                batchDeleteIdsContainer.appendChild(inp);
            });
            
            document.getElementById('batchDeleteForm').submit();
        });
    }

    // Batch move — populate hidden inputs
    var batchMoveModal = document.getElementById('batchMoveModal');
    if (batchMoveModal) {
        batchMoveModal.addEventListener('show.bs.modal', function() {
            var container = document.getElementById('batchKnmpIds');
            container.innerHTML = '';
            var checkedCount = selectedKnmpIds.size;
            document.getElementById('batchCount').textContent = checkedCount + ' KNMP dipilih';
            selectedKnmpIds.forEach(function(id) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'knmp_ids[]'; inp.value = id;
                container.appendChild(inp);
            });
        });
    }
});
</script>

@push('script')
<!-- Lucide Icons -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
    
    document.addEventListener('DOMContentLoaded', function() {
        // DataTable initialization
        if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && !jQuery.fn.DataTable.isDataTable('#scroll-horizontal-datatable')) {
            jQuery('#scroll-horizontal-datatable').DataTable({
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],
                scrollX: true,
                columnDefs: [
                    { orderable: false, targets: [0, -1] } // Disable sorting on checkbox (0) and Aksi (-1)
                ],
                order: [[1, 'asc']], // Default order by Nama KNMP
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    infoEmpty: "Tidak ada data",
                    infoFiltered: "(disaring dari _MAX_ data)",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "→",
                        previous: "←"
                    }
                }
            });
        }

        // Import Modal Drag & Drop Logic
        const dropzone = document.getElementById('importDropzone');
        const fileInput = document.getElementById('importFileInput');
        const filePreview = document.getElementById('importFilePreview');
        const fileName = document.getElementById('importFileName');
        const fileSize = document.getElementById('importFileSize');
        const fileRemove = document.getElementById('importFileRemove');
        const submitBtn = document.getElementById('importSubmitBtn');
        const importForm = document.getElementById('importForm');

        if (dropzone && fileInput) {
            dropzone.addEventListener('click', () => fileInput.click());

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
            });

            dropzone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                let dt = e.dataTransfer;
                let files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFiles(files[0]);
                }
            }

            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    handleFiles(this.files[0]);
                }
            });

            function handleFiles(file) {
                if (!file.name.match(/\.(xlsx|xls|csv)$/i)) {
                    alert('Hanya file Excel (.xlsx, .xls, .csv) yang diperbolehkan!');
                    fileInput.value = '';
                    return;
                }
                
                // Format file size
                let sizeStr = '';
                if (file.size < 1024 * 1024) {
                    sizeStr = Math.round(file.size / 1024) + ' KB';
                } else {
                    sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                }

                fileName.textContent = file.name;
                fileSize.textContent = sizeStr;
                
                dropzone.style.display = 'none';
                filePreview.style.display = 'block';
                submitBtn.disabled = false;
            }

            fileRemove.addEventListener('click', function() {
                fileInput.value = '';
                filePreview.style.display = 'none';
                dropzone.style.display = 'block';
                submitBtn.disabled = true;
            });
        }
    });
</script>
@endpush

@if(Auth::user()->isSuperAdmin())
    {{-- Combined Template Modal --}}
    <div class="modal fade" id="templateStageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; font-family: 'Poppins', sans-serif;">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold" style="color: #1e293b; letter-spacing: -0.5px;">Download Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pt-3 pb-4">
                    <form action="{{ route('forms.download_template', ['section' => $templateSection ?? 'usulan-knmp']) }}" method="GET" class="no-loader mb-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.85rem;">Pilih Tahap/Batch (Opsional)</label>
                            <div class="d-flex gap-2">
                                <select name="tahap" class="form-select" style="border-radius: 10px; font-size: 0.88rem; height: 42px; border-color: #e2e8f0; cursor: pointer;">
                                    <option value="all">Semua Tahap</option>
                                    @if(isset($availableTahap))
                                        @foreach($availableTahap as $t)
                                            <option value="{{ $t->id }}">{{ $t->nama_tahap }} - {{ $t->tahun }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" style="border-radius: 10px; min-width: 45px; height: 42px; background: #2563eb; border: none;">
                                    <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.72rem;">Unduh file excel sebagai template pengisian data.</small>
                        </div>
                    </form>

                    <div class="d-flex align-items-center my-4">
                        <hr class="flex-grow-1 m-0" style="opacity: 0.1;">
                        <span class="mx-3 text-uppercase fw-bold" style="font-size: 0.65rem; color: #94a3b8; letter-spacing: 1px;">Atau</span>
                        <hr class="flex-grow-1 m-0" style="opacity: 0.1;">
                    </div>

                    <div class="text-center">
                        <p class="text-muted mb-3" style="font-size: 0.8rem;">Ingin langsung mengunggah data?</p>
                        <button type="button" class="btn btn-success w-100 fw-bold d-flex align-items-center justify-content-center gap-2" 
                            style="border-radius: 10px; height: 45px; font-size: 0.9rem;"
                            onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('templateStageModal')).hide(); bootstrap.Modal.getOrCreateInstance(document.getElementById('importStageModal')).show();">
                            <i data-lucide="upload" style="width: 18px; height: 18px;"></i> Import Data Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Universal Import Modal --}}
    <div class="modal fade" id="importStageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; font-family: 'Poppins', sans-serif;">
                <form action="{{ $importRoute ?? route('survey.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: #1e293b; letter-spacing: -0.5px;">Import Data {{ $stageName ?? 'KNMP' }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4 pt-3 pb-4">
                        {{-- Extra fields (e.g. batch & tanggal for Usulan) --}}
                        @if(isset($importExtraFields) && is_array($importExtraFields))
                            @foreach($importExtraFields as $field)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ $field['label'] }}</label>
                                    @if(($field['type'] ?? 'text') === 'select')
                                        <select class="form-select" name="{{ $field['name'] }}" style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                            <option value="">{{ $field['placeholder'] ?? '— Pilih —' }}</option>
                                            @foreach($field['options'] as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(($field['type'] ?? 'text') === 'date')
                                        <input type="date" class="form-control" name="{{ $field['name'] }}" style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    @else
                                        <input type="text" class="form-control" name="{{ $field['name'] }}" style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2" style="font-size: 0.85rem;">File Excel (.xlsx)</label>
                            <div class="p-3 bg-light rounded-3 border-2 border-dashed border-primary-subtle text-center mb-3">
                                <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required style="border-radius: 8px; font-size: 0.85rem;">
                            </div>
                            <div class="alert alert-info border-0 rounded-3 p-2 mb-0" style="background: #f0f9ff;">
                                <div class="d-flex gap-2">
                                    <i data-lucide="info" style="width: 16px; height: 16px; color: #0ea5e9; flex-shrink: 0;"></i>
                                    <small style="font-size: 0.72rem; color: #0369a1; line-height: 1.4;">
                                        Pastikan format file sesuai dengan template yang telah diunduh sebelumnya.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light w-50 fw-semibold" data-bs-dismiss="modal" style="border-radius: 10px; height: 42px;">Batal</button>
                            <button type="submit" class="btn btn-success w-50 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px; height: 42px; border: none;">
                                <i data-lucide="upload" style="width: 18px; height: 18px;"></i> Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{!! $extraModals ?? '' !!}

@if(isset($extraModalsView))
    @include($extraModalsView)
@endif

@endsection
