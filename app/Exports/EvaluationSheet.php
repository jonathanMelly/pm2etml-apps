<?php

namespace App\Exports;

use App\Services\SummariesService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluationSheet implements WithTitle,FromCollection,ShouldAutoSize,WithStyles
{

    const NAME_PARTS_SEPARATOR = '|';
    private Collection $data;
    private string $title;

    public function __construct(string $title,Collection $data)
    {
        $this->data=$data;
        $this->title =$title;
    }

    public function collection()
    {
        $SUMMARY =0;
        $PERCENTAGE=1;
        $TIME_A=2;
        $TIME_NA=3;

        //Format is [bob][[1.1.2021,55%,...,projectName,clients]]
        //create first columns headers before real projects
        $projects = collect([
            $SUMMARY=>['name'=>'bilan'],
            $PERCENTAGE=>['name'=>'%'],
            $TIME_A=>['name'=> 'nb pér. '.EvaluationResult::A->name],
            $TIME_NA=>['name'=> 'nb pér. '. EvaluationResult::NA->name]
        ]);

        //list all projects
        $this->data->each(function($studentEvaluations) use($projects){
            foreach($studentEvaluations as $studentEvaluation){
                $projectNameSpecific = $studentEvaluation[SummariesService::PI_PROJECT_SPECIFIC];

                if($projects->where(fn($p)=>$p['name']==$projectNameSpecific)->count()==0)
                {
                    $projects->add([
                        'name'=>$projectNameSpecific,
                    ]);
                }
            }
        });

        //Map students / projects data for row/column format
        $studentsProjectsMap = [];
        foreach($this->data->keys() as $studentId){

            $studentEvals = $this->data[$studentId];
            $studentEval=[];

            //Store result for each project
            for($i=0;$i<sizeof($studentEvals);$i++)
            {
                $studentEval = $studentEvals[$i];

                $studentsProjectsMap[$studentId][$studentEval[SummariesService::PI_PROJECT_SPECIFIC]]=
                    $studentEval[SummariesService::PI_SUCCESS_TIME]>0?EvaluationResult::A->name : EvaluationResult::NA->name;
            }

            //Compute summary for student (fake project but still using columns...)
            //as data is sorted by date, last eval corresponds to latest status
            $summary = $studentEval[SummariesService::PI_CURRENT_PERCENTAGE] >= SummariesService::SUCCESS_REQUIREMENT_IN_PERCENTAGE  ?
                    EvaluationResult::A->name:
                    EvaluationResult::NA->name;
            $studentsProjectsMap[$studentId][$projects[$SUMMARY]['name']]=$summary;

            $studentsProjectsMap[$studentId][$projects[$PERCENTAGE]['name']]=
                $studentEval[SummariesService::PI_CURRENT_PERCENTAGE].""; //force string for excel (0->'' wihout)

            $totalSuccessTime = $studentEval[SummariesService::PI_ACCUMULATED_SUCCESS_TIME].""; //force string for excel (0->'' wihout)
            $studentsProjectsMap[$studentId][$projects[$TIME_A]['name']]=$totalSuccessTime;
            $studentsProjectsMap[$studentId][$projects[$TIME_NA]['name']]=
                ($studentEval[SummariesService::PI_ACCUMULATED_TIME]-$totalSuccessTime).""; //force string for excel (0->'' wihout)
        }

        //Build excel rows
        $rows = [];
        $header  = array_merge(['prénom','nom'],$projects->map(function($p) {
            return $p['name'];

        })->all());

        $rows[0]=$header;
        foreach($studentsProjectsMap as $studentId=>$studentProjectsEvals){
            [$firstname,$lastname] = explode(self::NAME_PARTS_SEPARATOR,$studentId);
            $columns = [$firstname,$lastname];
            foreach($projects as $project){
                $projectName = $project['name'];
                if(array_key_exists($projectName,$studentProjectsEvals)){
                    $columns[]=$studentProjectsEvals[$projectName];
                }else{
                    $columns[]='';
                }
            }
            $rows[]=$columns;
        }

        return collect($rows);
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet)
    {
        //Set style
        $sheet->getStyle('A1:CC1')->getFont()->setItalic(true)->setBold(true);
        $sheet->getStyle('G1:CC1')->getAlignment()->setTextRotation(90);
    }
}
