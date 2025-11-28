<?php

namespace App\Exports;

use App\Constants\RemediationStatus;
use App\DataObjects\EvaluationPoint;
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
            $TIME_A => ['name' => 'nb pér. '.EvaluationResult::ACQUIS->value.'|'.EvaluationResult::LARGEMENT_ACQUIS->value],
            $TIME_NA => ['name' => 'nb pér. '.EvaluationResult::PARTIELLEMENT_ACQUIS->value.'|'.EvaluationResult::NON_ACQUIS->value],
            $TIME_TOTAL => ['name' => 'pér. tot.']
        ]);

        //list all projects
        $this->data->each(function ($studentEvaluations) use ($projects) {
            // $studentEvaluations is now a Collection<EvaluationPoint>
            foreach ($studentEvaluations as $studentEvaluation) {
                /** @var EvaluationPoint $studentEvaluation */
                $projectNameSpecific = $studentEvaluation->projectSpecific;
                $clients = $studentEvaluation->clients;

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

            $studentEvals = $this->data[$studentId]; // Collection<EvaluationPoint>
            $lastEval = null;

            //Store result for each project
            foreach ($studentEvals as $studentEval) {
                /** @var EvaluationPoint $studentEval */
                $projectNameKey = $studentEval->projectSpecific;

                if (array_key_exists($studentId, $studentsProjectsMap) && array_key_exists($projectNameKey, $studentsProjectsMap[$studentId])) {
                    Log::warning("$studentId seems to have multiple evals on project ".$projectNameKey.', please check!');
                }

                $remediationPrefix = $studentEval->remediationStatus === RemediationStatus::EVALUATED ?
                    'R'
                    :'';

                // Use the actual evaluation_result value instead of converting to binary success/failure
                $studentsProjectsMap[$studentId][$projectNameKey][self::MAIN_DATA] =
                    $remediationPrefix . strtoupper($studentEval->evaluationResult->value);

                $studentsProjectsMap[$studentId][$projectNameKey]['date'] = $studentEval->dateSwiss;
                $studentsProjectsMap[$studentId][$projectNameKey]['clients'] = $studentEval->clients;
                $studentsProjectsMap[$studentId][$projectNameKey]['allocated_time'] = $studentEval->time.'p';
                $studentsProjectsMap[$studentId][$projectNameKey]['success_comment'] = $studentEval->successComment;

                $lastEval = $studentEval;
            }

            if ($lastEval === null) {
                continue;
            }

            //Compute summary for student (fake project but still using columns...)
            //as data is sorted by date, last eval corresponds to latest status
            $summary = strtoupper($lastEval->currentPercentage >= SummariesService::SUCCESS_REQUIREMENT_IN_PERCENTAGE ?
                    EvaluationResult::ACQUIS->value :
                    EvaluationResult::NON_ACQUIS->value);
            $studentsProjectsMap[$studentId][$projects[$SUMMARY]['name']][self::MAIN_DATA] = $summary;

            $studentsProjectsMap[$studentId][$projects[$PERCENTAGE]['name']][self::MAIN_DATA] =
                $lastEval->currentPercentage.''; //force string for excel (0->'' wihout)

            $totalSuccessTime = $lastEval->accumulatedSuccessTime.''; //force string for excel (0->'' wihout)
            $studentsProjectsMap[$studentId][$projects[$TIME_A]['name']][self::MAIN_DATA] = $totalSuccessTime;
            $studentsProjectsMap[$studentId][$projects[$TIME_NA]['name']][self::MAIN_DATA] =
                ($lastEval->accumulatedTime - $totalSuccessTime).''; //force string for excel (0->'' wihout)
            $studentsProjectsMap[$studentId][$projects[$TIME_TOTAL]['name']][self::MAIN_DATA] = $lastEval->accumulatedTime;
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
