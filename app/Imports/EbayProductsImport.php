<?php

namespace App\Imports;

use App\products;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EbayProductsImport implements ToCollection
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
            if(count($row)<2)
                continue; 
            else
            {
                                     
                $action = $row[0];
                $sku = $row[1];
                if($action=='modify'&&count($row)>=3)
                    $strategy = $row[2];   
                else
                    $strategy = "";

                $dataArray[]= ['action'=>$action, 'sku'=>$sku, 'strategy'=>$strategy];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
