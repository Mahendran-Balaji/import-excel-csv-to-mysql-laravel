<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToModel, WithChunkReading, SkipsEmptyRows, WithHeadingRow, WithBatchInserts
{
    use Importable;
    /**
    * @param array $employeeRecords
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $employeeRecords)
    {
        return new Employee([
                'name' => $employeeRecords['name'],
                'age' => $employeeRecords['age'],
                'doj' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($employeeRecords['doj'])
        ]);

    }

    //In case your heading row is not on the first row, you can easily specify this in your import class:
    public function headingRow(): int
    {
        return 1;
    }

    //Chunk reading : increase in memory usage (Importing a large file can have a huge impact on the memory usage)
    public function chunkSize(): int
    {
        return 1000;
    }

    //Importing a large file to Eloquent models, might quickly become a bottleneck as every row results into an insert query.
    // limit the amount of queries done by specifying a batch size
    //This concern only works with the ToModel concern.
    public function batchSize(): int
    {
        return 1000;
    }
}
