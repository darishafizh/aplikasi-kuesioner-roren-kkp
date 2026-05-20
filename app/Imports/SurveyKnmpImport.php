<?php

namespace App\Imports;

use App\Models\Knmp;
use App\Models\TahapSurvey;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class SurveyKnmpImport implements ToModel, WithHeadingRow, SkipsEmptyRows, WithCalculatedFormulas
{
    protected ?string $tanggal;

    public function __construct(?string $tanggal = null)
    {
        $this->tanggal = $tanggal;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $knmpId = $row['knmp_id'] ?? null;
        
        if (empty($knmpId)) {
            return null;
        }

        return DB::transaction(function () use ($row, $knmpId) {
            $knmp = Knmp::find($knmpId);

            if (!$knmp) {
                return null;
            }

            // Update latitude and longitude in knmp table
            $knmp->update([
                'latitude'  => $row['latitude'] ?? $row['latitud'] ?? $knmp->latitude,
                'longitude' => $row['longitude'] ?? $knmp->longitude,
            ]);

            // Save/update catatan and tanggal in tahap_survey table
            $tahapSurveyData = [
                'catatan' => $row['catatan'] ?? null,
            ];

            if ($this->tanggal) {
                $tahapSurveyData['tanggal'] = $this->tanggal;
            }

            TahapSurvey::updateOrCreate(
                ['knmp_id' => $knmp->id],
                $tahapSurveyData
            );

            return $knmp;
        });
    }
}
