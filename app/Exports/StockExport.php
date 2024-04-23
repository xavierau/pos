<?php

// app/Exports/StockExport.php

namespace App\Exports;

use App\Models\CountStock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Product Name',
            'Quantity',
        ];
    }

    public function map($row): array
    {
        return [
            $row['product_name'],
            $row['quantity'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set the format of the 'Quantity' column as text
        $sheet->getStyle('B')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        return [
            // Additional styling if needed
        ];
    }
}
