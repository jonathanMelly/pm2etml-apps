<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsersSheetImport implements WithMultipleSheets
{
    use Importable;

    public UsersImport $firstSheetImport;

    public function sheets(): array
    {
        $import = new UsersImport();
        $import->withOutput($this->output);
        $this->firstSheetImport = $import;

        return [
            //Look only at 1st sheet
            $import,
        ];
    }
}
