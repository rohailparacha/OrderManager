<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use App\products;
use App\blacklist;
use DB;
use App\accounts; 

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InformedExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        $offset = $this->offset;
        
        $products = products::select()->offset($offset)->limit(5000)->get();         
        $accounts = accounts::all(); 
        $accArray = array();
        foreach($accounts as $acc)
        {
            $accArray[$acc->store] = $acc->informed_id;
        }        

        $dataArray = array();

        foreach($products as $product)
        {
            $strategy = $this->getStrategy($product->lowestPrice);
            if($strategy == 0 )
                continue; 

            $market_id = '';

            foreach($accArray as $key=>$value)
            {
                if(strtolower($key)== strtolower($product->account))
                    $market_id = $value; 
            }

            if(empty($market_id))
                continue; 

            $dataArray[]=  [
                "SKU"=>$product->asin,
                "MARKETPLACE_ID"=>$market_id,
                "LISTING_TYPE"=>'',
                "STRATEGY_ID"=>$strategy,
                "COST"=>$product->lowestPrice,
                "CURRENCY"=>'USD',
                "CREATED_DATE"=>""
        ];
        }
        
        return collect($dataArray);

    }

    public function headings(): array
    {
        return [
            'SKU','MARKETPLACE_ID','LISTING_TYPE','STRATEGY_ID','COST','CURRENCY','CREATED_DATE'
        ];
    }


    public function getStrategy($price)
    {
        $ord = DB::select( DB::raw("SELECT * FROM `informed_settings` WHERE ".$price." between minAmount and maxAmount limit 1") );

        if(!empty($ord) && count($ord)>0)            
            return $ord[0]->strategy_id; 
        else
            return 0;
    }
}
