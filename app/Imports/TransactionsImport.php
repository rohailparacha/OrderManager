<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TransactionsImport implements ToCollection
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
            if(count($row)!=5)
                continue; 
            else
            {                           
                $date = $row[0];
                
                $description = $row[1];
                
                $debits = $row[2];
                
                $credits = $row[3];

                $category = $row[4];

                $newDate = date("Y-m-d", strtotime($date));  

                $dataArray[]= ['date'=>$newDate, 'description'=>$description, 'credits'=>$credits, 'debits'=>$debits,'category'=>$category];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
