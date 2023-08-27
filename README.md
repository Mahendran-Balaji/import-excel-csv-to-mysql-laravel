# import-xls-xlsx-csv-file-into-mysql-databse-using-laravel
Import XLS, XLSX and CSV File into MySQL Database Using Laravel Application

Laravel Version: 10.20.0

PHP Version: 8.2.4

Requirements

```
PHP: ^7.2\|^8.0
Laravel: ^5.8
PhpSpreadsheet: `^1.21
PHP extension php_zip enabled
PHP extension php_xml enabled
PHP extension php_gd2 enabled
PHP extension php_iconv enabled
PHP extension php_simplexml enabled
PHP extension php_xmlreader enabled
PHP extension php_zlib enabled
```
maatwebsite/excel 3.1

Step 1: Create Laravel project with below command in the terminal

```bash
  composer create-project laravel/laravel import_xls_xlsx_csv_files_to_mysql
```

Step 2: Now let's create database migration using below artisan command:

```bash
  php artisan make:migration create_employee_table
```

Step 3: Now add table fields in the migration class in the up() method.

```php
Schema::create('employee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200);
            $table->integer('age', );
            $table->date('doj');
            $table->timestamps();
        });
```

Step 4: Run the migrate command to generate table in the database:

```bash
  php artisan migrate
```

Step 5: create model using following command:

```bash
  php artisan make:model Employee
```

Step 6: Add following code into your Employee Model

```bash
    protected $table="employee";

    protected $fillable = ['name','age','doj'];
```

Step 7: Package Installation

```bash
  composer require maatwebsite/excel
```

The Maatwebsite\Excel\ExcelServiceProvider is auto-discovered and registered by default.

If you want to register it yourself, add the ServiceProvider in config/app.php:


```php
'providers' => [
    /*
     * Package Service Providers...
     */
    Maatwebsite\Excel\ExcelServiceProvider::class,
]
```

The Excel facade is also auto-discovered.

If you want to add it manually, add the Facade in config/app.php:


```php
    'aliases' => [
    ...
    'Excel' => Maatwebsite\Excel\Facades\Excel::class,
]
```

To publish the config, run the vendor publish command:

```php
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

```

This will create a new config file named config/excel.php.


Step 8: Create an import class in app/Imports

You may do this by using the make:import command.

```bash
  php artisan make:import EmployeeImport --model=Employee
```
```bash
  php artisan make:import EmployeeCSVImport --model=Employee
```

The File can be found in app/Imports

.
├── app
│   ├── Imports
│   │├── EmployeeImport.php
│   │├── EmployeeImport.php
│ 
└── composer.json

Step 9: Create new controller

```bash
php artisan make:controller EmployeeController
```

Add Following Traits:

```bash
use App\Models\Employee;
```

```bash
use App\Imports\{EmployeesImport,EmployeeCSVImport};
```

```bash
use Maatwebsite\Excel\Facades\Excel;
```

```bash
use Exception;
```

Step 10: In Controller (For Form Action)

```bash
    public function index()
    {
        return view('welcome'); 
    }
```

Step 11: Call Import Function From EmployeeController

```bash
     public function store(Request $request)
    {
        try{
        if ($request->hasFile('bulk_employee_records')) {

            switch ($request->file('bulk_employee_records')->clientExtension()) {
                case "xlsx":
                    Excel::import(new EmployeesImport, $request->file('bulk_employee_records'));
                    return redirect('/')->with('success', 'All good!');
                case "xls":
                    Excel::import(new EmployeesImport, $request->file('bulk_employee_records'));
                    return redirect('/')->with('success', 'All good!');
                case "csv":
                    Excel::import(new EmployeeCSVImport, $request->file('bulk_employee_records'));
                    return redirect('/')->with('success', 'All good!');
                default:
                    throw new \Exception('Invalid file format');
            }
        }
    }
    catch(Exception $e) {
        return redirect('/')->with('error',$e->getMessage());
    }
    catch(\Maatwebsite\Excel\Validators\ValidationException $ve){
        return redirect('/')->with('error',$ve->failures());
        }
    }
```

Step 12: View Blade File (welcome.blade.php)

```bash
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-3">
            @include('flash-message')
            <form action="{{ route('store_employee_records') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3 mt-3">
                    <label for="email">File:</label>
                    <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel,text/comma-separated-values, text/csv, application/csv" required class="form-control" name="bulk_employee_records">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
```
Step 12: Open EmployeesImport (App/Import/) and Add following code

```bash
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

```

Step 13: Open EmployeesCSVImport (App/Import/) and Add following code

```bash
<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeCSVImport implements ToModel,WithChunkReading, SkipsEmptyRows, WithHeadingRow, WithBatchInserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $employeeRecords)
    {
        return new Employee([
            'name' => $employeeRecords['name'],
            'age' => $employeeRecords['age'],
            'doj' => date('Y-m-d',strtotime($employeeRecords['doj']))
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
```

Step 14: Route

```bash
use App\Http\Controllers\EmployeeController;
```

```bash
Route::get('/',[EmployeeController::class,'index']);

Route::post('/',[EmployeeController::class,'store'])->name('store_employee_records');
```

Step 15: Clear the cache

```bash
php artisan optimize:clear
```

Step 16: Run the application

```bash
php artisan serve
```
