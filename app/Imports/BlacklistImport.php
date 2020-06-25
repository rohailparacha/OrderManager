<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class BlacklistImport implements ToCollection
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
                $reason = $row[1];
                $allowance = $row[2];
                $action = $row[3];

                $dataArray[]= ['asin'=>$asin, 'reason'=>$reason, 'allowance'=>$allowance, 'action'=>$action];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
