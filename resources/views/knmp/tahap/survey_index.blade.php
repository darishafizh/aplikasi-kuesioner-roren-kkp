@include('knmp.tahap._stage_index', [
    'title'       => 'Tahap Survey',
    'stageName'   => 'Survey',
    'icon'        => 'mdi-clipboard-text-outline',
    'lucideIcon'  => 'search',
    'color'       => '#06b6d4',
    'colorEnd'    => '#0891b2',
    'colorShadow' => 'rgba(6, 182, 212, 0.3)',
    'showRoute'   => 'survey_tahap.show',
    'importRoute' => route('survey.import'),
    'knmps'       => $knmps,
    'templateSection' => 'survey-knmp',
    'importExtraFields' => [
        [
            'name'     => 'tanggal',
            'label'    => 'Tanggal Survey',
            'type'     => 'date',
            'required' => false,
        ],
    ],
    'columns'     => [
        ['label' => 'Lokasi KNMP', 'key' => 'nama', 'type' => 'lokasi'],
        ['label' => 'Status', 'key' => 'status', 'type' => 'badge_status'],
        ['label' => 'Jml Responden', 'key' => 'informasi_responden_count', 'type' => 'raw',
         'render' => function($k) { return '<span class="badge bg-info">'.$k->informasi_responden_count.'</span>'; }],
    ],
    'extraActions' => function($knmp) {
        $urlInput = route('forms.index', $knmp->nama);
        $urlResponden = route('forms.edit-responden', $knmp->nama);
        return '
        <a href="'.$urlInput.'" class="btn btn-action btn-action-outline-success" title="Input Survey">
            <i data-lucide="clipboard-list"></i>
        </a>
        <a href="'.$urlResponden.'" class="btn btn-action btn-action-outline-info" title="Kelola Responden">
            <i data-lucide="users"></i>
        </a>
        ';
    },
])
