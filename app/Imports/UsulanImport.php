<?php

namespace App\Imports;

use App\Models\Knmp;
use App\Models\TahapUsulan;
use App\Models\RiwayatTahap;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class UsulanImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithCalculatedFormulas
{
    protected ?int $batchId;
    protected ?string $tanggal;
    protected string $tahapSaatIni;

    /**
     * Menerima parameter dari modal import (bukan dari Excel).
     */
    public function __construct(?int $batchId = null, ?string $tanggal = null, string $tahapSaatIni = 'usulan')
    {
        $this->batchId      = $batchId;
        $this->tanggal      = $tanggal;
        $this->tahapSaatIni = $tahapSaatIni;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Support both variations of header names
        $namaRaw   = trim($row['nama'] ?? $row['nama_knmp'] ?? '');
        $desaRaw   = trim($row['desa'] ?? $row['desa_kelurahan'] ?? '');

        // Skip only if BOTH name and village are empty
        if ($namaRaw === '' && $desaRaw === '') {
            return null;
        }

        // If name is missing but village exists, use village as name
        if ($namaRaw === '') {
            $namaRaw = "KNMP Desa " . $desaRaw;
        }

        return DB::transaction(function () use ($row, $namaRaw, $desaRaw) {
            $provinsi  = trim($row['provinsi'] ?? $row['province'] ?? '');
            $kabupaten = trim($row['kabupaten'] ?? $row['kabupaten_kota'] ?? $row['regency'] ?? '');
            $kecamatan = trim($row['kecamatan'] ?? $row['district'] ?? '');

            // 1. Create KNMP — batch_id & tahap_saat_ini come from the constructor (modal form)
            $knmp = Knmp::create([
                'batch_id'       => $this->batchId,
                'nama'           => $namaRaw,
                'provinsi'       => $provinsi,
                'kabupaten'      => $kabupaten,
                'kecamatan'      => $kecamatan,
                'desa'           => $desaRaw,
                'status'         => trim($row['status'] ?? ''),
                'tahap_saat_ini' => $this->tahapSaatIni,
            ]);

            // 2. Create Tahap Usulan — tanggal comes from the constructor (modal form), catatan from Excel
            TahapUsulan::create([
                'knmp_id' => $knmp->id,
                'tanggal' => $this->tanggal,
                'catatan' => trim($row['catatan'] ?? ''),
            ]);

            // 3. Add initial history
            if ($knmp->wasRecentlyCreated) {
                RiwayatTahap::create([
                    'knmp_id'    => $knmp->id,
                    'tahap_dari' => null,
                    'tahap_ke'   => $this->tahapSaatIni,
                    'keterangan' => 'Import awal dari Excel',
                    'created_by' => auth()->check() ? (string) auth()->id() : 'system',
                ]);
            }

            return $knmp;
        });
    }
}
