<?php

namespace App\Imports;

use App\products;
use App\blacklist;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AttributeImport implements ToCollection
{
    public $data;
    public $category;

    public function __construct($category) {
        $this->category = $category;
     
    }
    public function collection(Collection $rows)
    {
        $category = $this->category;

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
        
        foreach($dataArray as $arr)
        {
            products::where('asin',$arr['asin'])->update(['category'=>$category]);
        }
        $this->data = collect($dataArray);
    }

    
}
