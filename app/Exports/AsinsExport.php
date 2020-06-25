<?php

namespace App\Exports;

use App\products;
use App\blacklist;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsinsExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $offset; 

    public function __construct($offset)
    {
        $this->offset = $offset;
    }

    public function collection()
    {
        //
        $offset =  $this->offset;
        $products = products::orderBy('account')->offset($offset)->limit(20000)->get(); 
        $dataArray = array();
        
        

        foreach($products as $product)
        {           

            $dataArray[]=  [
                "ASIN"=>$product->asin,
                
            ];
        }
        
        return collect($dataArray);

    }

    public function headings(): array
    {
        return [
            'ASIN'
        ];
    }
}
