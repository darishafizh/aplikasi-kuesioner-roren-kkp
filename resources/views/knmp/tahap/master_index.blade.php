@include('knmp.tahap._stage_index', [
    'title'       => 'Master KNMP',
    'stageName'   => 'Master Data',
    'icon'        => 'mdi-database',
    'lucideIcon'  => 'database',
    'color'       => '#475569',
    'colorEnd'    => '#1e293b',
    'colorShadow' => 'rgba(71, 85, 105, 0.2)',
    'showRoute'   => 'usulan.show',
    'importRoute' => route('usulan.import'),
    'knmps'       => $knmps,
    'availableTahap' => $availableTahap,
    'templateSection' => 'usulan-knmp',
    'hideStageActions' => true,
    'columns'     => [
        ['label' => 'Lokasi KNMP', 'key' => 'nama', 'type' => 'lokasi'],
        ['label' => 'Status', 'key' => 'status', 'type' => 'badge_status'],
    ]
])


