<?php

namespace App\Imports;

use App\Models\Player;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlayersImport implements ToModel, WithStartRow, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (!empty($row[1]) && !empty($row[1]) && !empty($row[1])) {
            return new Player([
                'name' => $row[1],
                'position' => $row[2],
                'unit' => $row[3],
            ]);
        }

        return null;
    }
}
