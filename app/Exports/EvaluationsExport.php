<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EvaluationsExport implements FromCollection, ShouldAutoSize, WithMultipleSheets
{
    private Collection $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function sheets(): array
    {
        $sheets = [];
        $comments = collect();

        //Format is [cin1b][bob][[1.1.2021,55%,...,projectName,clients]]
        $this->data->each(
            function ($groupEvaluations, $groupName) use (&$sheets) {
                $sheet = new EvaluationSheet($groupName, collect($groupEvaluations));
                $sheets[] = $sheet;
            }
        );

        return $sheets;
    }
}
