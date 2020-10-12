<?php

namespace App\Exports;

use App\products;
use App\blacklist;
use App\amazon_settings;
use App\order_details;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AsinsExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $offset; 
    protected $flag; 

    public function __construct($offset, $flag)
    {
        $this->offset = $offset;
        $this->flag = $flag;
    }

    public function collection()
    {
        //
        $offset =  $this->offset;
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
        $products = $prd->orderBy('account')->offset($offset)->limit(20000)->get(); 
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
