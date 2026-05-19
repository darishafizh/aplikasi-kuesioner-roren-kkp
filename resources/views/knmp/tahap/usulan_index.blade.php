@php
    // Build batch options for the import modal dropdown
    $batchOptions = $batches->map(function ($b) {
        return ['value' => $b->id, 'label' => 'Batch ' . $b->id . ' — ' . $b->nama_tahap . ' (' . $b->tahun . ')'];
    })->toArray();
@endphp

@include('knmp.tahap._stage_index', [
    'title'       => 'Tahap Usulan',
    'stageName'   => 'Usulan',
    'icon'        => 'mdi-clipboard-outline',
    'lucideIcon'  => 'clipboard-list',
    'color'       => '#3b82f6',
    'colorEnd'    => '#1e40af',
    'colorShadow' => 'rgba(59,130,246,.2)',
    'showRoute'   => 'usulan.show',
    'importRoute' => route('usulan.import'),
    'knmps'       => $knmps,
    'templateSection' => 'usulan-knmp',
    'columns'     => [
        ['label' => 'Lokasi KNMP', 'key' => 'nama', 'type' => 'lokasi'],
        ['label' => 'Status', 'key' => 'status', 'type' => 'badge_status'],
        ['label' => 'Tanggal Usulan', 'key' => 'tanggal', 'type' => 'raw', 'render' => function($k) {
            $tgl = $k->tahapUsulan->tanggal ?? null;
            if (!$tgl) return '<span class="text-muted" style="font-size:0.78rem;">—</span>';
            return '<span style="font-size:0.78rem;">' . \Carbon\Carbon::parse($tgl)->translatedFormat('d M Y') . '</span>';
        }],
        ['label' => 'Catatan', 'key' => 'catatan', 'type' => 'raw', 'render' => function($k) {
            $catatan = $k->tahapUsulan->catatan ?? null;
            if (!$catatan) return '<span class="text-muted" style="font-size:0.78rem;">—</span>';
            $short = \Illuminate\Support\Str::limit($catatan, 50);
            return '<span style="font-size:0.78rem;" title="' . e($catatan) . '">' . e($short) . '</span>';
        }],
    ],
    'hideDetailAction' => true,
    'extraModalsView'  => 'knmp.tahap._usulan_edit_modals',

    // Extra fields shown inside the import modal (above the file input)
    'importExtraFields' => [
        [
            'name'        => 'batch_id',
            'label'       => 'Batch',
            'type'        => 'select',
            'placeholder' => '— Pilih Batch —',
            'options'     => $batchOptions,
            'required'    => false,
        ],
        [
            'name'     => 'tanggal',
            'label'    => 'Tanggal Usulan',
            'type'     => 'date',
            'required' => false,
        ],
    ],

    'extraActions' => function($knmp) {
        $deleteUrl = route('knmp_tahap.destroy', $knmp->nama);
        $csrf = csrf_field();
        $method = method_field('DELETE');

        return '
            <button type="button" class="btn btn-action btn-action-primary" data-bs-toggle="modal" data-bs-target="#editModal'.$knmp->id.'" title="Edit">
                <i data-lucide="edit"></i>
            </button>
            <form action="'.$deleteUrl.'" method="POST" class="d-inline delete-knmp-form no-loader">
                '.$csrf.'
                '.$method.'
                <button type="submit" class="btn btn-action btn-action-outline-danger" title="Hapus">
                    <i data-lucide="trash-2"></i>
                </button>
            </form>
        ';
    }
])
