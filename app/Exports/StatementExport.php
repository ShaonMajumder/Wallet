<?php

namespace App\Exports;

use App\Models\Statement;
use Maatwebsite\Excel\Concerns\FromCollection;

class StatementExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Statement::all();
    }
}
