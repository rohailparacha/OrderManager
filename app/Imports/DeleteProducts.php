<?php

namespace App\Imports;

use App\products;
use App\blacklist;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DeleteProducts implements ToCollection
{
    public $data;

    public function collection(Collection $rows)
    {
        $count=0;
        foreach ($rows as $row) 
        {
            if($count==0)
            {
                $count++;
                continue; 
            }   
            
            if(count($row)!=1)
                continue; 
            else
            {
                
                $asin = $row[0];        
                        
                if(strlen($asin)<10)
                {
                    $rem = 10 - strlen($asin);
                    for($a=1; $a<=$rem; $a++)
                    {
                        $asin = "0".$asin;
                    }
                }        

                if(empty($asin))
                    continue;

                $dataArray[]= ['asin'=>$asin];
                
            } 
        }
        
        $this->data = collect($dataArray);
    }

    
}
