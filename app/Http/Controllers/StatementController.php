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

            if(strlen($column_limit)>1){
                $column_range = [];
                foreach ($this->excelColumnRange($request->start_column ?? 'A', $column_limit) as $value) {
                    $column_range[] = $value;
                }
            }else{
                $column_range = range( 'A', $column_limit );
            }
            
            $startcount = 0;
            $data = array();
            foreach ( $row_range as $row ) {
                //             $sheet->setCellValueByColumnAndRow($i, 1, $header);
                // $lastCellAddress = $sheet->getCellByColumnAndRow($i, 1)->getCoordinate();
                
                if( !empty($row)){
                    $temp = [];
                    foreach($column_range as $cc){
                        if( ! in_array($cc,$request->exclude_column ?? []) ){
                            $temp[$cc] = $sheet->getCell( $cc . $row )->getValue();
                        }
                        
                    }
                    if (array_filter($temp)) {
                        // all values are empty (where "empty" means == false)
                        $data[] = $temp;
                        // [    
                            // $sheet->setCellValueByColumnAndRow($i, 1, $header);
                            // $lastCellAddress = $sheet->getCellByColumnAndRow($i, 1)->getCoordinate();
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

    public function exportUsers(Request $request){
        return Excel::download(new StatementExport, 'users.xlsx');
    }

    
}
