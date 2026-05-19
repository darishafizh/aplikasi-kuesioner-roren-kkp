@php
    $extraModals = '';
    foreach($knmps as $k) {
        $extraModals .= '
        <div class="modal fade" id="uploadModal'.$k->id.'" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 950px;">
                <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">
                    <form action="'.route('forms.store_bukti_upload').'" method="POST" enctype="multipart/form-data">
                        '.csrf_field().'
                        <input type="hidden" name="knmp_id" value="'.$k->id.'">
                        <div class="modal-header" style="background:linear-gradient(135deg,#10b981,#059669); border:none; padding: 1.25rem 1.5rem;">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                                    <i class="mdi mdi-image-multiple text-white" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title text-white fw-bold mb-0" style="letter-spacing: 0.5px;">Upload Bukti Konstruksi</h5>
                                    <small class="text-white-50">'.$k->nama.'</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="row g-4">
                                <!-- Kolom Before -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 text-center">
                                            <h6 class="fw-bold text-primary mb-1" style="font-size: 1.05rem;">
                                                <i class="mdi mdi-image-outline me-1 fs-5"></i> Kondisi Sebelum (Before)
                                            </h6>
                                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Foto sebelum pembangunan dimulai (Maks 10MB)</p>
                                        </div>
                                        <div class="card-body px-4 pb-4 pt-2">
                                            <div class="d-flex flex-column gap-3">';
                                            
                                            $beforeFiles = collect($k->buktiUploads ?? [])->where('kondisi', 'before')->values();
                                            for($i = 0; $i < 3; $i++) {
                                                $file = $beforeFiles[$i] ?? null;
                                                $fileExists = $file && \Illuminate\Support\Facades\Storage::disk('public')->exists($file->path_file);
                                                $imageUrl = $fileExists ? asset('storage/' . $file->path_file) : '';
                                                
                                                $extraModals .= '
                                                <div class="position-relative border rounded-3 p-3 bg-white d-flex align-items-center justify-content-center transition-all" 
                                                     style="height: 160px; border-style: dashed !important; border-width: 2px !important; border-color: '.($file ? '#cbd5e1' : '#bfdbfe').' !important; overflow: hidden; group:hover { border-color: #3b82f6 !important; }">
                                                    ';
                                                
                                                if($file && $fileExists) {
                                                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($file->path_file), '.pdf');
                                                    if($isPdf) {
                                                        $extraModals .= '
                                                        <div class="text-center w-100">
                                                            <i class="mdi mdi-file-pdf-box text-danger" style="font-size: 48px;"></i>
                                                            <p class="mb-0 small text-truncate px-3" title="'.$file->nama_file.'">'.$file->nama_file.'</p>
                                                        </div>';
                                                    } else {
                                                        $extraModals .= '
                                                        <a href="'.$imageUrl.'" target="_blank" class="w-100 h-100 d-block">
                                                            <img src="'.$imageUrl.'" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" alt="Before '.($i+1).'">
                                                        </a>';
                                                    }
                                                    $extraModals .= '
                                                    <div class="position-absolute top-0 end-0 p-2 z-index-1">
                                                        <button type="button" class="btn btn-sm btn-danger rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="document.getElementById(\'deleteFileForm'.$file->id.'\').submit();" title="Hapus File"><i class="mdi mdi-trash-can-outline"></i></button>
                                                    </div>';
                                                } else {
                                                    $extraModals .= '
                                                    <div class="text-center w-100">
                                                        <div class="mb-2">
                                                            <i class="mdi mdi-cloud-upload-outline text-primary" style="font-size: 32px; opacity: 0.7;"></i>
                                                        </div>
                                                        <span class="badge bg-primary-subtle text-primary mb-2">Slot '.($i+1).'</span>
                                                        <input type="file" name="file_before[]" class="form-control form-control-sm mx-auto shadow-sm" accept="image/*,.pdf" style="width: 100%; font-size: 0.75rem; border-radius: 6px;">
                                                    </div>';
                                                }
                                                $extraModals .= '</div>';
                                            }
                                            
                                        $extraModals .= '
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Kolom After -->
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100" style="border-radius: 14px;">
                                        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 text-center">
                                            <h6 class="fw-bold text-success mb-1" style="font-size: 1.05rem;">
                                                <i class="mdi mdi-image-check-outline me-1 fs-5"></i> Kondisi Sesudah (After)
                                            </h6>
                                            <p class="text-muted small mb-0" style="font-size: 0.75rem;">Foto setelah pembangunan selesai (Maks 10MB)</p>
                                        </div>
                                        <div class="card-body px-4 pb-4 pt-2">
                                            <div class="d-flex flex-column gap-3">';
                                            
                                            $afterFiles = collect($k->buktiUploads ?? [])->where('kondisi', 'after')->values();
                                            for($i = 0; $i < 3; $i++) {
                                                $file = $afterFiles[$i] ?? null;
                                                $fileExists = $file && \Illuminate\Support\Facades\Storage::disk('public')->exists($file->path_file);
                                                $imageUrl = $fileExists ? asset('storage/' . $file->path_file) : '';
                                                
                                                $extraModals .= '
                                                <div class="position-relative border rounded-3 p-3 bg-white d-flex align-items-center justify-content-center transition-all" 
                                                     style="height: 160px; border-style: dashed !important; border-width: 2px !important; border-color: '.($file ? '#cbd5e1' : '#bbf7d0').' !important; overflow: hidden; group:hover { border-color: #10b981 !important; }">
                                                    ';
                                                
                                                if($file && $fileExists) {
                                                    $isPdf = \Illuminate\Support\Str::endsWith(strtolower($file->path_file), '.pdf');
                                                    if($isPdf) {
                                                        $extraModals .= '
                                                        <div class="text-center w-100">
                                                            <i class="mdi mdi-file-pdf-box text-danger" style="font-size: 48px;"></i>
                                                            <p class="mb-0 small text-truncate px-3" title="'.$file->nama_file.'">'.$file->nama_file.'</p>
                                                        </div>';
                                                    } else {
                                                        $extraModals .= '
                                                        <a href="'.$imageUrl.'" target="_blank" class="w-100 h-100 d-block">
                                                            <img src="'.$imageUrl.'" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;" alt="After '.($i+1).'">
                                                        </a>';
                                                    }
                                                    $extraModals .= '
                                                    <div class="position-absolute top-0 end-0 p-2 z-index-1">
                                                        <button type="button" class="btn btn-sm btn-danger rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="document.getElementById(\'deleteFileForm'.$file->id.'\').submit();" title="Hapus File"><i class="mdi mdi-trash-can-outline"></i></button>
                                                    </div>';
                                                } else {
                                                    $extraModals .= '
                                                    <div class="text-center w-100">
                                                        <div class="mb-2">
                                                            <i class="mdi mdi-cloud-upload-outline text-success" style="font-size: 32px; opacity: 0.7;"></i>
                                                        </div>
                                                        <span class="badge bg-success-subtle text-success mb-2">Slot '.($i+1).'</span>
                                                        <input type="file" name="file_after[]" class="form-control form-control-sm mx-auto shadow-sm" accept="image/*,.pdf" style="width: 100%; font-size: 0.75rem; border-radius: 6px;">
                                                    </div>';
                                                }
                                                $extraModals .= '</div>';
                                            }
                                            
                                        $extraModals .= '
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer px-4 py-3" style="background:#fff; border-top: 1px solid #f1f5f9;">
                            <button type="button" class="btn btn-light fw-medium px-4" style="border-radius: 10px;" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success text-white fw-bold px-4 shadow-sm d-flex align-items-center gap-2" style="border-radius: 10px;">
                                <i class="mdi mdi-content-save"></i> Simpan Gambar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        ';
        
        // Add hidden delete forms for each file
        $allFiles = collect($k->buktiUploads ?? []);
        foreach($allFiles as $file) {
            $extraModals .= '
            <form id="deleteFileForm'.$file->id.'" action="'.route('forms.delete_bukti_single', hashid($file->id)).'" method="POST" class="d-none">
                '.csrf_field().'
                '.method_field('DELETE').'
            </form>
            ';
        }
    }
@endphp

@include('knmp.tahap._stage_index', [
    'title'       => 'Tahap Konstruksi',
    'stageName'   => 'Konstruksi',
    'icon'        => 'mdi-office-building',
    'lucideIcon'  => 'building-2',
    'color'       => '#ef4444',
    'colorEnd'    => '#dc2626',
    'colorShadow' => 'rgba(239, 68, 68, 0.3)',
    'showRoute'   => 'konstruksi.show',
    'importRoute' => route('dashboard.import_progres_nasional'),
    'knmps'       => $knmps,
    'availableTahap' => $availableTahap,
    'templateSection' => 'progres-knmp-nasional',
    'columns'     => [
        ['label' => 'Lokasi KNMP', 'key' => 'nama', 'type' => 'lokasi'],
        ['label' => 'Penyedia Jasa Konstruksi', 'key' => 'penyedia', 'type' => 'raw',
         'render' => function($k) { return '<span class="text-dark fw-medium" style="font-size: 0.8rem;">'.($k->konstruksiKnmp->penyediaJasa->nama ?? '-').'</span>'; }],
        ['label' => 'Progres', 'key' => 'progres', 'type' => 'progres_bar'],
        ['label' => 'Keterangan', 'key' => 'keterangan', 'type' => 'raw',
         'render' => function($k) { return '<div class="text-muted text-truncate" style="max-width: 200px; font-size: 0.75rem;" title="'.($k->latestProgresNasional->keterangan ?? '-').'">'.($k->latestProgresNasional->keterangan ?? '-').'</div>'; }],
    ],
    'extraModals' => $extraModals,
    'extraActions' => function($knmp) {
        return '<button type="button" class="btn btn-action btn-action-outline-success" data-bs-toggle="modal" data-bs-target="#uploadModal'.$knmp->id.'" title="Upload Bukti Before/After"><i data-lucide="upload-cloud"></i></button>';
    }
])
