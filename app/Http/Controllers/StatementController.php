<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Statement;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StatementImport;
use App\Exports\StatementExport;
use App\Http\Components\DBTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;


class StatementController extends Controller
{
    use DBTrait;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function excelColumnRange($lower, $upper) {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }

    public function importView(Request $request){
        return view('importFile');
    }

    public function import(Request $request){
        try{
            if($request->start_row or $request->start_column){
                if($request->dbvscell){
                    $request->merge(['dbvscell' => is_array($request->dbvscell) ? $request->dbvscell : explode(',',$request->dbvscell) ]);
                }
                $request->merge(['exclude_column' => is_array($request->exclude_column) ? $request->exclude_column : explode(',',$request->exclude_column) ]);
                $tempfile = Session::get('temporary');
                $tempfile = Storage::disk('temporary')->path($tempfile);
                // Session::forget('temporary');
                // dd($request->all());
            }else{
                $this->validate($request, [
                    'file' => 'required|file|mimes:xls,xlsx'
                ]);
                $the_file = $request->file('file');
                $tempfile = $the_file->getRealPath();
                $fileName = time().'.'.$request->file->extension();  
                Storage::disk('temporary')->put( $fileName, File::get($request->file));
                Session::put('temporary',$fileName);
            }

            $spreadsheet  = IOFactory::load($tempfile);
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();

            $row_range = range( $request->start_row ?? 0, $row_limit );
            $column_range = [];
            foreach ($this->excelColumnRange($request->start_column ?? 'A', $column_limit) as $value){  
                if( (! in_array( $value,$request->exclude_column ?? []) ) ){
                    $column_range[] = $value;
                }
            } 

            $startcount = $request->start_row ?? 0;
            $data = array();
            foreach ( $row_range as $row ) {
                if( !empty($row)){
                    $temp = [];
                    foreach($column_range as $cc){
                        $temp[$cc] = $sheet->getCell( $cc . $row )->getValue();
                    }
                    if ( $request->clean_empty_row == 'true' ) {
                        if(array_filter($temp)){
                            $data[$startcount] = $temp;
                        }
                    }else{
                        $data[$startcount] = $temp;
                    }
                }
                $startcount++;
            }
            $dbcolumns = $this->getDBColumns(new Statement);
            
            // DB::table('tbl_customer')->insert($data);
            return view('importView', compact('data','column_range','tempfile', 'dbcolumns'));
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }

        // Excel::import(new StatementImport, $request->file('file')->store('files'));
        
        // return view('importView', compact());
        // return redirect()->back();
    }


    public function importInDB(Request $request){
        try{
            
            if($request->start_row or $request->start_column){
                if($request->dbvscell){
                    $request->merge(['dbvscell' => is_array($request->dbvscell) ? $request->dbvscell : explode(',',$request->dbvscell) ]);
                }
                $request->merge(['exclude_column' => is_array($request->exclude_column) ? $request->exclude_column : explode(',',$request->exclude_column) ]);
                $tempfile = Session::get('temporary');
                $tempfile = Storage::disk('temporary')->path($tempfile);
                // Session::forget('temporary');
                // dd($request->all());
            }else{
                $this->validate($request, [
                    'file' => 'required|file|mimes:xls,xlsx'
                ]);
                $the_file = $request->file('file');
                $tempfile = $the_file->getRealPath();
                $fileName = time().'.'.$request->file->extension();  
                Storage::disk('temporary')->put( $fileName, File::get($request->file));
                Session::put('temporary',$fileName);
            }

            $spreadsheet  = IOFactory::load($tempfile);
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $column_limit = $sheet->getHighestDataColumn();

            $row_range = range( $request->start_row ?? 0, $row_limit );
            $column_range = [];
            foreach ($this->excelColumnRange($request->start_column ?? 'A', $column_limit) as $value){  
                if( (! in_array( $value,$request->exclude_column ?? []) ) ){
                    $column_range[] = $value;
                }
            } 

            $startcount = $request->start_row ?? 0;
            $data = array();
            foreach ( $row_range as $row ) {
                // linking db column to spreadsheet column
                $colLink = [];
                foreach($request->dbvscell as $dbcell){
                    list($dbfield,$cellname) = explode ("=>", $dbcell);
                    $colLink[$dbfield] = $cellname;
                }

                //empty row check
                $temp = [];
                foreach($column_range as $cc){
                    $temp[$cc] = $sheet->getCell( $cc . $row )->getValue();
                }
                
                if(array_filter($temp)){
                    $data[$startcount] = [
                        'date' => Carbon::parse( str_replace("'",'', $sheet->getCell( $colLink['date'] . $row )->getValue()) )->format('Y-m-d h:i:s') ?? DateTime::createFromFormat("j-M-Y", str_replace("'",'', $sheet->getCell( $colLink['date'] . $row )->getValue()) )->format('Y-m-d h:i:s'),
                        'ref_check' => $sheet->getCell( $colLink['ref_check'] . $row )->getValue(),
                        'description' => $sheet->getCell( $colLink['description'] . $row )->getValue(),
                        'withdraw' => (float) str_replace(',','',  $sheet->getCell( $colLink['withdraw'] . $row )->getValue() ) ?? 0,
                        'deposit' => (float) str_replace(',','',  $sheet->getCell( $colLink['deposit'] . $row )->getValue() ) ?? 0,
                        'balance' => (float) str_replace(',','',  $sheet->getCell( $colLink['balance'] . $row )->getValue() ) ?? 0,
                        'causer_id' => Auth::user()->id
                    ];
                }
                $startcount++;
                
            }
            
            Statement::insert($data);
            $dbcolumns = $this->getDBColumns(new Statement);
            return view('importView', compact('data','column_range','tempfile','dbcolumns'));
        } catch (Exception $e) {
            $error_code = $e->errorInfo[1];
            return back()->withErrors('There was a problem uploading the data!');
        }

        // Excel::import(new StatementImport, $request->file('file')->store('files'));
        
        // return view('importView', compact());
        // return redirect()->back();
    }

    public function exportUsers(Request $request){
        return Excel::download(new StatementExport, 'users.xlsx');
    }

    
}
