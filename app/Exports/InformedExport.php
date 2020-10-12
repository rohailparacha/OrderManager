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

class InformedExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $offset;     
    protected $flag; 


    public function __construct($offset, $flag)
    {
        $this->offset = $offset;        
        $this->flag= $flag;
    }

    public function collection()
    {
        //
        $offset = $this->offset;

        $flag =  $this->flag;

        $setting = amazon_settings::get()->first();

        if($flag==1)
        {
            $prd = products::whereIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->orWhere('created_at', '>', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
    
        }
        else
        {
            $prd = products::whereNotIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->Where('created_at', '<=', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
        }

        $products = $prd->select()->offset($offset)->limit(5000)->get();         
        
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
