<?php

namespace App\Imports;

use App\Models\Statement;
use Maatwebsite\Excel\Concerns\ToModel;

class StatementImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Statement([
            'date' => $row[0],
            'ref_check' => $row[1],
            'description' => $row[2],
            'withdraw' => $row[3],
            'deposit' => $row[4],
            'balance' => $row[5]
        ]);
    }
}
