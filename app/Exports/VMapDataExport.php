<?php

namespace App\Exports;

use App\Models\User;
use App\Services\ExportCsv\ExportCsvService;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class VMapDataExport implements FromCollection, WithHeadings
{

    private $export;

    private $data;
    public function __construct($data = null)
    {
        $this->export = new ExportCsvService();
        $this->data = $data;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->export->exportCsvData($this->data);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function headings(): array
    {
        return [
            'Level 1 ID',
            'Level 1 Name',
            'Level 1 Date Completed',
            'Level 2 ID',
            'Level 2 Name',
            'Level 2 Delegate',
            'Level 2 Date Assigned',
            'Level 2 Due Date',
            'Level 2 Date Completed',
            'Level 2 Status',
            'Level 2 Dial',
            'Level 2 Tracking',
            'Level 2 Activity',
            'Level 2 Include in Avatar',
            'Level 2 Goal Type',
            'Level 2 Goal Amount',
            'Level 2 Do not Accumulate',
            'Level 3 ID',
            'Level 3 Name',
            'Level 3 Delegate',
            'Level 3 Date Assigned',
            'Level 3 Due Date',
            'Level 3 Date Completed',
            'Level 3 Status',
            'Level 3 Dial',
            'Level 3 Tracking',
            'Level 3 Activity',
            'Level 3 Include in Avatar',
            'Level 3 Goal Type',
            'Level 3 Goal Amount',
            'Level 3 Do not Accumulate',
            'Level 4 ID',
            'Level 4 Name',
            'Level 4 Delegate',
            'Level 4 Date Assigned',
            'Level 4 Due Date',
            'Level 4 Date Completed',
            'Level 4 Status',
            'Level 4 Dial',
            'Level 4 Tracking',
            'Level 4 Activity',
            'Level 4 Include in Avatar',
            'Level 4 Goal Type',
            'Level 4 Goal Amount',
            'Level 4 Do not Accumulate',
            'Level 5 ID',
            'Level 5 Name',
            'Level 5 Delegate',
            'Level 5 Date Assigned',
            'Level 5 Due Date',
            'Level 5 Date Completed',
            'Level 5 Status',
            'Level 5 Dial',
            'Level 5 Tracking',
            'Level 5 Activity',
            'Level 5 Include in Avatar',
            'Level 5 Goal Type',
            'Level 5 Goal Amount',
            'Level 5 Do not Accumulate',
        ];
    }
}
