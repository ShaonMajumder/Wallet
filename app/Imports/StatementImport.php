<?php

namespace App\Imports;

use App\Models\Statement;
use Carbon\Carbon;
use DateTime;
use Exception;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StatementImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // return $row;
        // try{
        //     return new Statement([
        //         'date' => Carbon::parse( str_replace("'",'',$row['date']) )->format('Y-m-d h:i:s'),
        //         'ref_check' => $row['ref_check'],
        //         'description' => $row['description'],
        //         'withdraw' => (float) str_replace(',','',$row['withdraw']) ?? 0,
        //         'deposit' => (float) str_replace(',','',$row['deposit']) ?? 0,
        //         'balance' => (float) str_replace(',','',$row['balance']) ?? 0
        //     ]);
        // }catch(Exception $e){
        //     return new Statement([
        //         'date' => DateTime::createFromFormat("j-M-Y", str_replace("'",'',$row['date']) )->format('Y-m-d h:i:s'),
        //         'ref_check' => $row['ref_check'],
        //         'description' => $row['description'],
        //         'withdraw' => (float) str_replace(',','',$row['withdraw']) ?? 0,
        //         'deposit' => (float) str_replace(',','',$row['deposit']) ?? 0,
        //         'balance' => (float) str_replace(',','',$row['balance']) ?? 0
        //     ]);
        // }
        
    }
}
