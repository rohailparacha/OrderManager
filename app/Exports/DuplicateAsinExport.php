<?php

namespace App\Exports;

use App\products;
use App\blacklist;
use App\amazon_settings;
use App\order_details;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DuplicateAsinExport implements FromCollection,WithHeadings,ShouldAutoSize,WithStrictNullComparison
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $collection; 

    public function __construct($collection)
    {
        $this->collection = $collection;
        
    }

    public function collection()
    {
        //
        $collection = $this->collection;  
                
        $dataArray = array();

        foreach($collection as $col)
        {
            $product = products::where('asin',$col['asin'])->get()->first(); 

            if(empty($product))
                continue; 

            $dataArray[]=  [
                "ASIN"=>$product->asin
            ];
        }
                
        return collect($dataArray);

    }

    public function headings(): array
    {
        return ['ASIN'];
    }
}
