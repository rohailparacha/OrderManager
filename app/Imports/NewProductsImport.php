<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class NewProductsImport implements ToCollection
{
    public $data;
  
    public function collection(Collection $rows)
    {
        $asinIndex = 0; 
        $priceIndex = 0; 
        $dataArray = array(); 

        if(strtolower(trim($rows[0][0]))=='asin')
                $asinIndex= 0;
        elseif(strtolower(trim($rows[0][1]))=='asin')
                $asinIndex= 1;
        elseif(strtolower(trim($rows[0][2]))=='asin')
                $asinIndex= 2;

        if(strtolower(trim($rows[0][0]))=='price')
                $priceIndex= 0;
        elseif(strtolower(trim($rows[0][1]))=='price')
                $priceIndex= 1;
        elseif(strtolower(trim($rows[0][2]))=='price')
                $priceIndex= 2;

        foreach ($rows as $row) 
        {
            if(count($row)<3)
                continue; 
            else
            {                          
                $asin = $row[$asinIndex]; 
                                                                
                if(strlen($asin)<10)
                {
                    $rem = 10 - strlen($asin);
                    for($a=1; $a<=$rem; $a++)
                    {
                        $asin = "0".$asin;
                    }
                }       

                $price = $row[$priceIndex];   
                
                $dataArray[]= ['asin'=>$asin, 'lowestPrice'=>$price];
            } 
        }
        
        $this->data = collect($dataArray);
    }
}
