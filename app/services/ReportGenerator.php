<?php

namespace Worklog\Service;

use \PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Style\Alignment;
use \PhpOffice\PhpSpreadsheet\Style\Border;
use \PhpOffice\PhpSpreadsheet\Style\Color;
use \PhpOffice\PhpSpreadsheet\Style\Fill;
use \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \Worklog\Models\Log;
use \Worklog\Models\Project;
use \Worklog\Models\User;

/**
 * Generates excel report for selected project
 */
class ReportGenerator
{
    /**
     * @var \Worklog\Models\Project
     */
    private $project;
    /**
     * \Worklog\Models\User
     */
    private $user;
    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    private $spreadsheet;
    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    private $sheet;
    /**
     * Total number of work logs for the selected project
     * @var int
     */
    private $totalLogs;
    /**
     * Sum of all work logs for this project, in seconds
     * @var int
     */
    private $totalElapsedTime;

    public function __construct(Project $project, User $user)
    {
        $this->project = $project;
        $this->user = $user;
    }

    public function generate()
    {

        $logs = $this->project->getLogs();
        $this->totalLogs = $logs->count();
        $this->totalElapsedTime = 0;

        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();

        $this->setMetaData();

        $this->setHeaderTitles()
            ->setHeaderStyle()
            ->setAlignment()
            ->setTableBorders()
            ->setTotalCellStyle()
            ->setColumnsWidth();

        foreach ($logs as $idx => $log) {
            $this->totalElapsedTime += $log->elapsed;
            $this->addRow($log, $idx);
        }

        $this->addTotalRow();

        return $this;
    }

    /**
     * Force download
     */
    public function download()
    {
        // Redirect output to a client’s web browser (Xls)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->generateFileName() . '"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Save report to the file system
     */
    public function save()
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save('/tmp/' . $this->generateFileName());
    }

    private function setMetaData(): Spreadsheet
    {
        $this->spreadsheet->getProperties()
            ->setCreator($this->user->getFullName())
            ->setLastModifiedBy($this->user->getFullName())
            ->setTitle($this->project->name . ' - worklog report')
            ->setDescription('Worklog report for project ' . $this->project->name . ', generated on ' . date('Y-m-d H:i:s'));

        return $this->spreadsheet;
    }

    private function setHeaderTitles(): ReportGenerator
    {
        $this->sheet->setCellValueByColumnAndRow(1, 1, 'Name');
        $this->sheet->setCellValueByColumnAndRow(2, 1, 'Start');
        $this->sheet->setCellValueByColumnAndRow(3, 1, 'End');
        $this->sheet->setCellValueByColumnAndRow(4, 1, 'Total (h:m)');

        return $this;
    }

    private function setHeaderStyle(): ReportGenerator
    {
        $this->sheet->getStyle('A1:D1')
            ->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('000000');
        $this->sheet->getStyle('A1:D1')
            ->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

        return $this;
    }

    private function setAlignment(): ReportGenerator
    {

        $this->sheet->getStyle('B1:B' . ($this->totalLogs + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getStyle('C1:C' . ($this->totalLogs + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $this->sheet->getStyle('D1:D' . ($this->totalLogs + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return $this;

    }

    private function setTableBorders(): ReportGenerator
    {
        $this->sheet->getStyle('A1:D' . ($this->totalLogs + 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        return $this;
    }

    private function setTotalCellStyle(): ReportGenerator
    {
        $this->sheet->getStyle('D' . ($this->totalLogs + 2))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => 'aaaaaa'],
            ],
        ]);

        return $this;
    }

    private function setColumnsWidth(): ReportGenerator
    {
        $this->sheet->getColumnDimension('A')->setAutoSize(true);
        $this->sheet->getColumnDimension('B')->setAutoSize(true);
        $this->sheet->getColumnDimension('C')->setAutoSize(true);
        $this->sheet->getColumnDimension('D')->setAutoSize(true);

        return $this;
    }

    private function addRow(Log $log, int $idx)
    {
        $this->sheet->setCellValueByColumnAndRow(1, $idx + 2, $log->getUser()->firstName . ' ' . $log->getUser()->lastName);
        $this->sheet->setCellValueByColumnAndRow(2, $idx + 2, $log->start);
        $this->sheet->setCellValueByColumnAndRow(3, $idx + 2, $log->end);

        $this->sheet->setCellValueByColumnAndRow(
            4,
            $idx + 2,
            \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($log->elapsed)
        );
        $this->sheet->getCellByColumnAndRow(
            4,
            $idx + 2)
            ->getStyle()
            ->getNumberFormat()
            ->setFormatCode(
                \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME3
            );
    }

    private function addTotalRow()
    {
        $this->sheet->setCellValue('D' . ($this->totalLogs + 2), Log::displayTime($this->totalElapsedTime));
    }

    private function generateFileName(): string
    {
        return 'report-project-' . $this->project->id . '_' . date('Y-m-d_H:i:s') . '.xlsx';
    }
}
