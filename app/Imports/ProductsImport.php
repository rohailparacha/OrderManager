<?php

namespace App\Imports;

use App\products;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection
{
    public $data;
  
    public function collection(Collection $rows)
    {
        $dataArray = array(); 
        foreach ($rows as $row) 
        {
            if(count($row)<4)
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
                $account = $row[1];
                $strategy = $row[2];
                $action = $row[3];

                $dataArray[]= ['asin'=>$asin, 'account'=>$account, 'strategy'=>$strategy, 'action'=>$action];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
