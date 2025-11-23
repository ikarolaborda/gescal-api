<?php

namespace App\Services\ReportFormatters;

use App\Models\Report;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelReportFormatter
{
    public function generate(array $formattedData, Report $report): string
    {
        $data = $formattedData['data'];
        $entityType = $formattedData['entity_type'];

        $export = new class($data, $entityType) implements FromArray, WithHeadings, WithStyles, WithTitle
        {
            public function __construct(
                private array $data,
                private string $entityType
            ) {}

            public function array(): array
            {
                return $this->data;
            }

            public function headings(): array
            {
                if (empty($this->data)) {
                    return [];
                }

                return array_keys($this->data[0]);
            }

            public function styles(Worksheet $sheet)
            {
                $headerBgColor = config('reports.excel.header_background_color', 'F0F0F0');

                // Style header row
                $sheet->getStyle('1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $headerBgColor],
                    ],
                ]);

                // Freeze header row
                if (config('reports.excel.freeze_header_row', true)) {
                    $sheet->freezePane('A2');
                }

                // Auto-size columns
                $maxWidth = config('reports.excel.auto_width_max_chars', 50);
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())
                        ->setAutoSize(true);
                }

                return [];
            }

            public function title(): string
            {
                return ucfirst($this->entityType);
            }
        };

        // Generate Excel file and return content
        $tempPath = tempnam(sys_get_temp_dir(), 'report');
        Excel::store($export, $tempPath, 'local', \Maatwebsite\Excel\Excel::XLSX);

        $content = file_get_contents(storage_path('app/' . $tempPath));
        unlink(storage_path('app/' . $tempPath));

        return $content;
    }
}
