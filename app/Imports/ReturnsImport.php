<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReturnsImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public $data;
  
    public function collection(Collection $rows)
    {
        $dataArray = array(); 
        foreach ($rows as $row) 
        {
            try{
                $sellOrderId = $row[1];
                $status = $row[26];
                $created_at = $row[5];
                $reason = $row[6];
                $carrier = $row[12];
                $tracking = $row[13];
                $dataArray[]= ['sellOrderId'=>$sellOrderId, 'status'=>$status, 'created_at'=>$created_at, 'reason'=>$reason, 'carrier'=>$carrier, 'tracking'=>$tracking];            
            }
            catch(\Exception $ex){

            }
        }
        
        $this->data = collect($dataArray);
    }
}
