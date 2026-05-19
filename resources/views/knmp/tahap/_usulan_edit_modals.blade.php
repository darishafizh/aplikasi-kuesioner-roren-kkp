{{-- Partial: Edit modals + Delete confirm for Usulan KNMP --}}

@foreach($knmps as $knmp)
    <div class="modal fade" id="editModal{{ $knmp->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $knmp->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Modal Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-0 bg-transparent">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="editModalLabel{{ $knmp->id }}" style="font-size: 1.1rem; letter-spacing: -0.01em;">
                            Edit Data KNMP
                        </h5>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Perbarui data lokasi dan informasi usulan</p>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.7rem;"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 py-3">
                    <form action="{{ route('usulan.update', $knmp->nama) }}" method="POST" class="edit-knmp-form no-loader">
                        @csrf
                        @method('PUT')

                        <!-- Nama KNMP -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Nama KNMP</label>
                            <input type="text" class="form-control" name="nama" value="{{ $knmp->nama }}" required
                                style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px; transition: border-color 0.2s;">
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Provinsi -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Provinsi</label>
                                <input type="text" class="form-control" name="provinsi" value="{{ $knmp->provinsi }}" required
                                    style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;">
                            </div>
                            <!-- Kabupaten -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Kabupaten</label>
                                <input type="text" class="form-control" name="kabupaten" value="{{ $knmp->kabupaten }}" required
                                    style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Kecamatan -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Kecamatan</label>
                                <input type="text" class="form-control" name="kecamatan" value="{{ $knmp->kecamatan }}" required
                                    style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;">
                            </div>
                            <!-- Desa -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Desa</label>
                                <input type="text" class="form-control" name="desa" value="{{ $knmp->desa }}" required
                                    style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;">
                            </div>
                        </div>

                        <!-- Batch -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Batch</label>
                            <select class="form-select" name="batch_id"
                                style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px;">
                                <option value="">— Pilih Batch —</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch->id }}" {{ $knmp->batch_id == $batch->id ? 'selected' : '' }}>
                                        Batch {{ $batch->id }} — {{ $batch->nama_tahap }} ({{ $batch->tahun }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Catatan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-secondary mb-1" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Catatan</label>
                            <textarea class="form-control" name="catatan" rows="3" placeholder="Tambahkan catatan..."
                                style="font-size: 0.85rem; border-radius: 10px; border: 1.5px solid #e2e8f0; padding: 10px 14px; resize: none;">{{ $knmp->tahapUsulan->catatan ?? '' }}</textarea>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-end gap-2 pt-2">
                            <button type="button" class="btn px-4" data-bs-dismiss="modal"
                                style="font-size: 0.82rem; border-radius: 10px; font-weight: 600; background: #f1f5f9; color: #475569; border: none; padding: 9px 20px;">
                                Batal
                            </button>
                            <button type="submit" class="btn px-4 text-white"
                                style="font-size: 0.82rem; border-radius: 10px; font-weight: 600; background: linear-gradient(135deg, #0054A6, #003a75); border: none; padding: 9px 20px; box-shadow: 0 4px 14px rgba(0,84,166,0.25);">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Delete confirmation overlay (custom, no SweetAlert2 dependency) --}}
<div id="deleteConfirmOverlay" class="alert-overlay" style="display:none;">
    <div class="alert-card error" style="animation: none; transform: scale(1);">
        <div class="alert-icon-circle">
            <span class="alert-icon">!</span>
        </div>
        <h3 class="alert-title" style="color:#dc2626;">Hapus Data KNMP?</h3>
        <p class="alert-subtitle">Tindakan ini tidak dapat dibatalkan. Semua data terkait KNMP ini akan dihapus secara permanen.</p>
        <div class="d-flex gap-2">
            <button class="alert-btn" id="deleteCancelBtn" style="background:#f1f5f9; color:#475569; border-radius:8px;">Batal</button>
            <button class="alert-btn" id="deleteConfirmBtn" style="background: linear-gradient(135deg,#EF4444,#DC2626); color:#fff; border-radius:8px;">Ya, Hapus</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var pageLoader = document.getElementById('pageLoader');

    // ─── EDIT: submit form inside modal ───
    document.querySelectorAll('.edit-knmp-form').forEach(function(form) {
        form.addEventListener('submit', function() {
            // Close the modal first
            var modalEl = this.closest('.modal');
            if (modalEl) {
                var bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
            }
            // Show global page loader
            if (pageLoader) pageLoader.classList.remove('hidden');
        });
    });

    // ─── DELETE: intercept with custom confirm dialog ───
    var pendingDeleteForm = null;
    var overlay = document.getElementById('deleteConfirmOverlay');
    var cancelBtn = document.getElementById('deleteCancelBtn');
    var confirmBtn = document.getElementById('deleteConfirmBtn');

    // Use event delegation for delete forms (DataTables re-renders rows)
    document.addEventListener('submit', function(e) {
        var form = e.target;
        if (!form.classList.contains('delete-knmp-form')) return;
        if (form.dataset.confirmed === 'true') return; // Already confirmed, let it through

        e.preventDefault();
        e.stopImmediatePropagation(); // Stop global loader from firing
        pendingDeleteForm = form;

        // Show custom overlay
        overlay.style.display = '';
        overlay.classList.add('show');
    }, true); // Capture phase to fire BEFORE global handler

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            overlay.classList.remove('show');
            setTimeout(function() { overlay.style.display = 'none'; }, 200);
            pendingDeleteForm = null;
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            overlay.classList.remove('show');
            setTimeout(function() { overlay.style.display = 'none'; }, 200);

            if (pendingDeleteForm) {
                // Mark as confirmed so global loader picks it up
                pendingDeleteForm.dataset.confirmed = 'true';
                // Show global page loader
                if (pageLoader) pageLoader.classList.remove('hidden');
                pendingDeleteForm.submit();
                pendingDeleteForm = null;
            }
        });
    }
});
</script>
@endpush
