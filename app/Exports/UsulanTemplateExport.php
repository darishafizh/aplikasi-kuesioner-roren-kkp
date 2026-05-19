<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsulanTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Return a sample row
        return collect([
            [
                'nama' => 'Contoh KNMP 1',
                'provinsi' => 'JAWA BARAT',
                'kabupaten' => 'SUKABUMI',
                'kecamatan' => 'CIREUNGHAS',
                'desa' => 'CIREUNGHAS',
                'catatan' => 'Catatan usulan contoh 1',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'nama',
            'provinsi',
            'kabupaten',
            'kecamatan',
            'desa',
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
