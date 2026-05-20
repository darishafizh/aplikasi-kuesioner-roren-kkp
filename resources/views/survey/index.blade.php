@extends('layouts.app')

@section('content')
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
        ['label' => 'Lokasi KNMP', 'key' => 'lokasi', 'type' => 'raw', 'render' => function($k) {
            $nama = ucwords(strtolower($k->nama ?? 'N/A'));
            $desa = ucwords(strtolower($k->desa ?? ''));
            $kecamatan = ucwords(strtolower($k->kecamatan ?? ''));
            $kabupaten = ucwords(strtolower($k->kabupaten ?? ''));
            $provinsi = ucwords(strtolower($k->provinsi ?? ''));
            
            // Gabungkan wilayah dengan koma, hiraukan yang kosong
            $wilayah = collect([$desa, $kecamatan, $kabupaten, $provinsi])->filter()->implode(', ');
            
            return "<div class='fw-bold text-dark'>{$nama}</div>
                    <div class='text-muted' style='font-size: 0.75rem;'>{$wilayah}</div>";
        }],
        ['label' => 'Status', 'key' => 'status', 'type' => 'badge_status'],
        ['label' => 'Jml Responden', 'key' => 'informasi_responden_count', 'type' => 'raw',
         'render' => function($k) { return '<span class="badge bg-info">'.($k->informasi_responden_count ?? 0).'</span>'; }],
    ],
    'extraActions' => function($knmp) {
        $url = route('forms.index', $knmp->nama);
        return '<a href="'.$url.'" class="btn btn-action btn-action-outline-info" title="Lihat Responden"><i data-lucide="users"></i></a>';
    },
])
@endsection