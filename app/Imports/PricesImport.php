<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PricesImport implements ToCollection
{
    public $data;
  
    public function collection(Collection $rows)
    {
        $dataArray = array(); 
        foreach ($rows as $row) 
        {
            if(count($row)<18)
                continue; 
            else
            {                
                $asin = $row[1]; 
                                                                
                if(strlen($asin)<10)
                {
                    $rem = 10 - strlen($asin);
                    for($a=1; $a<=$rem; $a++)
                    {
                        $asin = "0".$asin;
                    }
                }       

                $price = $row[17];      
                $dataArray[]= ['asin'=>$asin, 'lowestPrice'=>$price];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
