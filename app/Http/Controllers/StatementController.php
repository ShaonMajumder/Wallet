<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Statement;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StatementImport;
use App\Exports\StatementExport;




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

            $startcount = 0;
            $data = array();
            foreach ( $row_range as $row ) {
                if( !empty($row)){
                    $temp = [];
                    foreach($column_range as $cc){
                        $temp[$cc] = $sheet->getCell( $cc . $row )->getValue();
                    }
                    if ( $request->clean_empty_row == 'true' ) {
                        if(array_filter($temp)){
                            $data[] = $temp;
                            $startcount++;
                        }
                    }else{
                        $data[] = $temp;
                        // [    
                            // 'CustomerName' =>$sheet->getCell( 'A' . $row )->getValue(),
                            // 'Gender' => $sheet->getCell( 'B' . $row )->getValue(),
                            // 'Address' => $sheet->getCell( 'C' . $row )->getValue(),
                            // 'City' => $sheet->getCell( 'D' . $row )->getValue(),
                            // 'PostalCode' => $sheet->getCell( 'E' . $row )->getValue(),
                            // 'Country' =>$sheet->getCell( 'F' . $row )->getValue(),
                        // ];
                        $startcount++;
                    }
                }
            }
            // DB::table('tbl_customer')->insert($data);
            return view('importView', compact('data','column_range','tempfile'));
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
            foreach ($this->excelColumnRange($request->start_column ?? 'A', $column_limit) as $value) $column_range[] = $value;
            
            $startcount = 0;
            $data = array();
            foreach ( $row_range as $row ) {
                $data[] = [    
                    'CustomerName' =>$sheet->getCell( 'A' . $row )->getValue(),
                    'Gender' => $sheet->getCell( 'B' . $row )->getValue(),
                    'Address' => $sheet->getCell( 'C' . $row )->getValue(),
                    'City' => $sheet->getCell( 'D' . $row )->getValue(),
                    'PostalCode' => $sheet->getCell( 'E' . $row )->getValue(),
                    'Country' =>$sheet->getCell( 'F' . $row )->getValue(),
                ];
                $startcount++;    
            }
            DB::table('statements')->insert($data);
            return view('importView', compact('data','column_range','tempfile'));
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
