<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Statement;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StatementImport;
use App\Exports\StatementExport;

class StatementController extends Controller
{
    public function importView(Request $request){
        return view('importFile');
    }

    public function import(Request $request){
        Excel::import(new StatementImport, $request->file('file')->store('files'));
        return redirect()->back();
    }

    public function exportUsers(Request $request){
        return Excel::download(new StatementExport, 'users.xlsx');
    }
}
