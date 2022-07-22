<?php

namespace App\Http\Components;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait DBTrait{

    /**
     * Set Valiables with Default Value
     */
    protected $status   = false;
    protected $message  = "Error";
    protected $access_token;
    protected $data;
    
    
    protected function getDBColumns($model){
        $columns=[];
        $results = DB::select( "SHOW COLUMNS FROM ".$model->getTable() );
        foreach($results as $result) $columns[]= $result->Field;
        return $columns;
    }
    
    protected function getDBColumns2($model){
        return Schema::getColumnListing( $model->getTable() );
    }

    protected function getDBListing($model,$pages=10){
        $columns = $this->getDBColumns($model);
        $paginated_model = $model->latest()->paginate($pages);
        return array($columns,$paginated_model);
    }
}