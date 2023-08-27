<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Imports\{EmployeesImport,EmployeeCSVImport};
use League\Flysystem\Config;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('welcome');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
        if ($request->hasFile('bulk_employee_records')) {

            switch ($request->file('bulk_employee_records')->clientExtension()) {
                case "xlsx":
                    Excel::import(new EmployeesImport, $request->file('bulk_employee_records'));
//                        ->toCollection(new EmployeesImport, $request->file('bulk_employee_records'));
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

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
    }
}
