<?php

namespace App\Exports;

use App\Constants\RemediationStatus;
use App\Services\SummariesService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EvaluationSheet implements FromCollection, ShouldAutoSize, WithEvents, WithStyles, WithTitle
{
    use RegistersEventListeners;

    const NAME_PARTS_SEPARATOR = '|';

    const MAIN_DATA = 'result';

    private Collection $data;

    private string $title;

    public $comments = [];

    public function __construct(string $title, Collection $data)
    {
        $this->data = $data;
        $this->title = $title;
    }

    public function collection()
    {
        $SUMMARY = 0;
        $PERCENTAGE = 1;
        $TIME_A = 2;
        $TIME_NA = 3;
        $TIME_TOTAL = 4;

        //Format is [bob][[1.1.2021,55%,...,projectName]]
        //create first columns headers before real projects
        $projects = collect([
            $SUMMARY => ['name' => 'bilan'],
            $PERCENTAGE => ['name' => '%'],
            $TIME_A => ['name' => 'nb pér. '.EvaluationResult::A->name],
            $TIME_NA => ['name' => 'nb pér. '.EvaluationResult::NA->name],
            $TIME_TOTAL => ['name' => 'pér. tot.']
        ]);

        //list all projects
        $this->data->each(function ($studentEvaluations) use ($projects) {
            foreach ($studentEvaluations as $studentEvaluation) {
                $projectNameSpecific = $studentEvaluation[SummariesService::PI_PROJECT_SPECIFIC];
                $clients = $studentEvaluation[SummariesService::PI_CLIENTS];

                $query = $projects->where(fn ($p) => $p['name'] == $projectNameSpecific);
                if ($query->count() == 0) {
                    $projects->add([
                        'name' => $projectNameSpecific,
                        'clients' => $clients,
                    ]);
                }
                //Merge clients if multiple
                else {
                    $existingProject = $query->first();
                    if (! str_contains($existingProject['clients'], $clients)) {
                        $existingProject['clients'] .= ','.$clients;
                    }

                }
            }
        });

        //Map students / projects data for row/column format
        $studentsProjectsMap = [];
        foreach ($this->data->keys() as $studentId) {

            $studentEvals = $this->data[$studentId];
            $studentEval = [];

            //Store result for each project
            for ($i = 0; $i < count($studentEvals); $i++) {
                $studentEval = $studentEvals[$i];
                $projectNameKey = $studentEval[SummariesService::PI_PROJECT_SPECIFIC];

                if (array_key_exists($studentId, $studentsProjectsMap) && array_key_exists($projectNameKey, $studentsProjectsMap[$studentId])) {
                    Log::warning("$studentId seems to have multiple evals on project ".$projectNameKey.', please check!');
                }

                $remediationPrefix = $studentEval[SummariesService::PI_REMEDIATION_STATUS] === RemediationStatus::EVALUATED ?
                    'R'
                    :'';

                $studentsProjectsMap[$studentId][$projectNameKey][self::MAIN_DATA] =
                    $remediationPrefix . ($studentEval[SummariesService::PI_SUCCESS_TIME] > 0 ?
                        EvaluationResult::A->name
                        : EvaluationResult::NA->name);
                $studentsProjectsMap[$studentId][$projectNameKey]['date'] = $studentEval[SummariesService::PI_DATE_SWISS];
                $studentsProjectsMap[$studentId][$projectNameKey]['clients'] = $studentEval[SummariesService::PI_CLIENTS];
                $studentsProjectsMap[$studentId][$projectNameKey]['allocated_time'] = $studentEval[SummariesService::PI_TIME].'p';
                $studentsProjectsMap[$studentId][$projectNameKey]['success_comment'] = $studentEval[SummariesService::PI_SUCCESS_COMMENT];
            }
            //Compute summary for student (fake project but still using columns...)
            //as data is sorted by date, last eval corresponds to latest status
            $summary = $studentEval[SummariesService::PI_CURRENT_PERCENTAGE] >= SummariesService::SUCCESS_REQUIREMENT_IN_PERCENTAGE ?
                    EvaluationResult::A->name :
                    EvaluationResult::NA->name;
            $studentsProjectsMap[$studentId][$projects[$SUMMARY]['name']][self::MAIN_DATA] = $summary;

            $studentsProjectsMap[$studentId][$projects[$PERCENTAGE]['name']][self::MAIN_DATA] =
                $studentEval[SummariesService::PI_CURRENT_PERCENTAGE].''; //force string for excel (0->'' wihout)

            $totalSuccessTime = $studentEval[SummariesService::PI_ACCUMULATED_SUCCESS_TIME].''; //force string for excel (0->'' wihout)
            $studentsProjectsMap[$studentId][$projects[$TIME_A]['name']][self::MAIN_DATA] = $totalSuccessTime;
            $studentsProjectsMap[$studentId][$projects[$TIME_NA]['name']][self::MAIN_DATA] =
                ($studentEval[SummariesService::PI_ACCUMULATED_TIME] - $totalSuccessTime).''; //force string for excel (0->'' wihout)
            $studentsProjectsMap[$studentId][$projects[$TIME_TOTAL]['name']][self::MAIN_DATA] = $studentEval[SummariesService::PI_ACCUMULATED_TIME];
        }

        //Build excel rows
        $rows = [];
        $header = array_merge(['prénom', 'nom'], $projects->map(function ($p) {
            return $p['name'];

        })->all());

        $rows[0] = $header;

        $row = 2;
        foreach ($studentsProjectsMap as $studentId => $studentProjectsEvals) {

            [$firstname,$lastname] = explode(self::NAME_PARTS_SEPARATOR, $studentId);
            $columns = [$firstname, $lastname];

            $column = 'c';
            foreach ($projects as $project) {
                $projectName = $project['name'];
                if (array_key_exists($projectName, $studentProjectsEvals)) {
                    $evalData = $studentProjectsEvals[$projectName];
                    $columns[] = $evalData[self::MAIN_DATA];
                    //add comment
                    if (array_key_exists('date', $evalData)) {
                        $this->comments[$column.$row]['author'] = $evalData['clients'];
                        $this->comments[$column.$row]['date'] = $evalData['date'];
                        $this->comments[$column.$row]['allocated_time'] = $evalData['allocated_time'];
                        $this->comments[$column.$row]['success_comment'] = $evalData['success_comment'];
                    }

                } else {
                    $columns[] = '';
                }

                $column++;
            }
            $rows[] = $columns;

            $row++;
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
        $sheet->getStyle('H1:CC1')->getAlignment()->setTextRotation(90);
    }

    public static function afterSheet(AfterSheet $event)
    {
        $comments = $event->getConcernable()->comments;

        $sheet = $event->getSheet();
        foreach ($comments as $address => $comment) {
            $author = $comment['author'];
            $date = $comment['date'];
            $allocated_time = $comment['allocated_time'];
            $success_comment = $comment['success_comment'];

            try {
                $excelComment = $sheet->getComment($address);
                $excelComment->setAuthor($author.' ('.$allocated_time.')');

                $commentRichText = $excelComment->getText()->createTextRun($author);
                $commentRichText->getFont()->setBold(true);

                $excelComment->getText()->createTextRun("\r\n".$date."\r\n".$allocated_time."\r\n".$success_comment);
            } catch (Exception $e) {
                Log::warning($e);
            }
        }
    }
}
