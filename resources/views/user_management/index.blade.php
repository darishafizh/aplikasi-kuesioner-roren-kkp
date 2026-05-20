@extends('layouts.app')

@push('styles')
<style>
    .dataTables_wrapper .dataTables_filter input {
        border-radius: 8px;
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
    }
    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px;
        padding: 6px 12px;
        border: 1px solid #e2e8f0;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
        border-color: #3b82f6 !important;
        color: white !important;
    }
    .dataTables_wrapper .dataTables_info {
        color: #64748b;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-left">
                    <h4 class="page-title"><i class="mdi mdi-account-group me-2"></i>Manajemen User</h4>
                    <small class="text-muted">Kelola pengguna sistem</small>
                </div>
                <div class="page-title-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Beranda</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="mdi mdi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="mdi mdi-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon-sm bg-primary">
                            <i class="mdi mdi-account-multiple"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Total User</h6>
                            <h3 class="mb-0 fw-bold">{{ $users->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon-sm" style="background-color: #8b5cf6; color: white;">
                            <i class="mdi mdi-shield-crown"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Super Admin</h6>
                            <h3 class="mb-0 fw-bold">{{ $users->filter(fn($u) => $u->hasRole('super_admin'))->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon-sm bg-danger">
                            <i class="mdi mdi-shield-account"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Admin</h6>
                            <h3 class="mb-0 fw-bold">{{ $users->filter(fn($u) => $u->hasRole('admin'))->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon-sm bg-success">
                            <i class="mdi mdi-clipboard-account"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-muted">Enumerator</h6>
                            <h3 class="mb-0 fw-bold">{{ $users->filter(fn($u) => $u->hasRole('enumerator'))->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Table Card -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0"><i class="mdi mdi-table-account me-2"></i>Daftar User</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="mdi mdi-plus me-1"></i>Tambah User
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="userTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Dibuat</th>
                                    <th width="120" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2">
                                                {{ strtoupper(substr($user->username ?? $user->name, 0, 1)) }}
                                            </div>
                                            <span class="fw-medium">{{ $user->username ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td><a href="mailto:{{ $user->email }}" class="text-muted">{{ $user->email }}</a></td>
                                    <td>
                                        @if($user->hasRole('super_admin'))
                                            <span class="badge" style="background-color: #ede9fe; color: #8b5cf6;">
                                                <i class="mdi mdi-shield-crown me-1"></i>Super Admin
                                            </span>
                                        @elseif($user->hasRole('admin'))
                                            <span class="badge bg-danger-subtle text-danger">
                                                <i class="mdi mdi-shield-account me-1"></i>Admin
                                            </span>
                                        @elseif($user->hasRole('enumerator'))
                                            <span class="badge bg-success-subtle text-success">
                                                <i class="mdi mdi-clipboard-account me-1"></i>Enumerator
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Role</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editUserModal{{ $user->id }}"
                                                title="Edit">
                                            <i class="mdi mdi-pencil"></i>
                                        </button>
                                        @if($user->id !== auth()->id())
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="showDeleteModal('{{ hashid($user->id) }}', '{{ $user->name }}')"
                                                title="Hapus">
                                            <i class="mdi mdi-trash-can"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="mdi mdi-account-off" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">Belum ada user</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade user-modal" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content user-modal-content">
                <form action="{{ route('user_management.store') }}" method="POST">
                    @csrf
                    <div class="modal-header user-modal-header">
                        <div class="d-flex align-items-center">
                            <div class="modal-header-icon bg-primary">
                                <i class="mdi mdi-account-plus"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="modal-title mb-0">Tambah User Baru</h5>
                                <small class="text-white-50">Buat akun pengguna baru</small>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="Masukkan username" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-card-account-details"></i></span>
                                <input type="text" class="form-control" name="name" placeholder="Masukkan nama lengkap" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="contoh@email.com" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-shield-account"></i></span>
                                <select class="form-select" name="role" required>
                                    <option value="">Pilih Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer user-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-plus me-1"></i>Tambah User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modals -->
    @foreach($users as $user)
    <div class="modal fade user-modal" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content user-modal-content">
                <form action="{{ route('user_management.update', hashid($user->id)) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header user-modal-header" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <div class="d-flex align-items-center">
                            <div class="modal-header-icon" style="background: rgba(255,255,255,0.2);">
                                <i class="mdi mdi-account-edit"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="modal-title mb-0">Edit User</h5>
                                <small class="text-white-50">{{ $user->name }}</small>
                            </div>
                        </div>
                        <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                            <i class="mdi mdi-close"></i>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-account"></i></span>
                                <input type="text" class="form-control" name="username" value="{{ $user->username }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-card-account-details"></i></span>
                                <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                <input type="email" class="form-control" name="email" value="{{ $user->email }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Password <small class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah)</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Password baru">
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="mdi mdi-shield-account"></i></span>
                                <select class="form-select" name="role" required>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer user-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="mdi mdi-content-save me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Delete Confirmation Modal -->
    <div class="modal fade user-modal" id="deleteUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content user-modal-content">
                <div class="modal-header user-modal-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                    <div class="d-flex align-items-center">
                        <div class="modal-header-icon" style="background: rgba(255,255,255,0.2);">
                            <i class="mdi mdi-trash-can"></i>
                        </div>
                        <div class="ms-3">
                            <h5 class="modal-title mb-0">Hapus User</h5>
                            <small class="text-white-50">Konfirmasi penghapusan</small>
                        </div>
                    </div>
                    <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                        <i class="mdi mdi-close"></i>
                    </button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="delete-icon-wrapper mb-3">
                        <i class="mdi mdi-account-remove"></i>
                    </div>
                    <h6 class="mb-2">Apakah Anda yakin?</h6>
                    <p class="text-muted mb-0">User <strong id="deleteUserName"></strong> akan dihapus secara permanen.</p>
                </div>
                <div class="modal-footer user-modal-footer justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="mdi mdi-trash-can me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .stat-icon-sm {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
        }
        
        .stat-icon-sm.bg-primary { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .stat-icon-sm.bg-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-icon-sm.bg-success { background: linear-gradient(135deg, #10b981, #059669); }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1) !important; }
        .bg-success-subtle { background-color: rgba(16, 185, 129, 0.1) !important; }
        
        #userTable th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Modal Styles */
        .user-modal {
            z-index: 1060 !important;
        }
        
        .user-modal .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
            margin: 0.5rem auto;
        }
        
        .user-modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            width: 100%;
        }
        
        .user-modal-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 1.25rem 1.5rem;
            border: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-modal-header .modal-title {
            color: #fff;
            font-weight: 600;
        }
        
        .user-modal-header small {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .modal-header-icon {
            width: 48px;
            height: 48px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
        }
        
        .modal-close-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.15);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .modal-close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        
        .modal-close-btn i {
            font-size: 1.25rem;
        }
        
        .user-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .user-modal .input-group-text {
            background: #f8fafc;
            border-right: none;
        }
        
        .user-modal .form-control,
        .user-modal .form-select {
            border-left: none;
        }
        
        .user-modal .form-control:focus,
        .user-modal .form-select:focus {
            border-color: #ced4da;
            box-shadow: none;
        }
        
        .user-modal .input-group:focus-within .input-group-text {
            border-color: #86b7fe;
        }
        
        .user-modal .input-group:focus-within .form-control,
        .user-modal .input-group:focus-within .form-select {
            border-color: #86b7fe;
        }
        
        .delete-icon-wrapper {
            width: 64px;
            height: 64px;
            background: rgba(239, 68, 68, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .delete-icon-wrapper i {
            font-size: 2rem;
            color: #ef4444;
        }
    </style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                zeroRecords: "Tidak ada data yang cocok",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            pageLength: 10,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    });

    function showDeleteModal(userId, userName) {
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteForm').action = '{{ route("user_management.index") }}/' + userId;
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
        deleteModal.show();
    }
</script>
@endpush
