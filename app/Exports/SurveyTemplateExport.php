<?php

namespace App\Exports;

use App\Models\Knmp;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SurveyTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $batchId;

    public function __construct($batchId = null)
    {
        $this->batchId = $batchId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Knmp::where('tahap_saat_ini', 'survey')
            ->with('tahapSurvey');

        if ($this->batchId && $this->batchId !== 'all') {
            $query->where('batch_id', $this->batchId);
        }

        $knmps = $query->get();

        if ($knmps->isEmpty()) {
            return collect([
                [
                    'knmp_id'   => 1,
                    'nama knmp' => 'Contoh KNMP 1',
                    'latitude'  => '-6.3167',
                    'longitude' => '107.9000',
                    'catatan'   => 'Catatan survey contoh 1',
                ]
            ]);
        }

        return $knmps->map(function ($k) {
            return [
                'knmp_id'   => $k->id,
                'nama knmp' => $k->nama,
                'latitude'  => $k->latitude,
                'longitude' => $k->longitude,
                'catatan'   => $k->tahapSurvey->catatan ?? null,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'knmp_id',
            'nama knmp',
            'latitude',
            'longitude',
            'catatan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
