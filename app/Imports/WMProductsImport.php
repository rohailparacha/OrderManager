<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WMProductsImport implements ToCollection
{

    public $data;
  
    public function collection(Collection $rows)
    {
        $dataArray = array(); 
        foreach ($rows as $row) 
        {
            if(count($row)!=3)
                continue; 
            else
            {                           
                $sku = $row[0];
                
                $id = $row[1];
                
                $link = $row[2];

                $dataArray[]= ['sku'=>$sku, 'id'=>$id, 'link'=>$link];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
