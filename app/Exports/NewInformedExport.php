<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use App\products;
use App\blacklist;
use DB;
use App\accounts; 
use App\amazon_settings;
use App\order_details;
use Carbon\Carbon;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class NewInformedExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        $setting = amazon_settings::get()->first();       
        
        $accounts = accounts::all(); 
        $accArray = array();
        foreach($accounts as $acc)
        {
            $accArray[$acc->store] = $acc->informed_id;
        }  
        $dataArray = array();
        
        foreach($collection as $col)
        {
            if(!is_numeric($col['lowestPrice']))   
                continue;

            $product = products::where('asin',$col['asin'])->get()->first();

            if(empty($product))
                continue;

            $strategy = $this->getStrategy($product->lowestPrice, $product->account);
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


    public function getStrategy($price, $account)
    {
        $accId = accounts::where('store',$account)->get()->first();
        $ord = DB::select( DB::raw("SELECT * FROM `informed_settings` WHERE account_id = ".$accId->infaccount_id." and ".$price." between minAmount and maxAmount limit 1") );

        if(!empty($ord) && count($ord)>0)            
            return $ord[0]->strategy_id; 
        else
            return 0;
    }
}
