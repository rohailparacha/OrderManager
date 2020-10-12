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

class SellerActiveExport implements FromCollection,WithHeadings,ShouldAutoSize,WithStrictNullComparison
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
        $flag = $this->flag;
        $offset =  $this->offset;
        
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

        $products = $prd->leftJoin('accounts','products.account','accounts.store')
        ->leftJoin('blacklist','products.asin','blacklist.sku')
        ->select(['products.*','accounts.lagTime','accounts.quantity','accounts.maxListingBuffer','blacklist.allowance'])
        ->offset($offset)->limit(100000)    
        ->orderBy('account')->get(); 
        
        $dataArray = array();

        foreach($products as $product)
        {
            if(empty($product->asin))
                continue; 
                
            $qty='0';
            if($product->lowestPrice==0)
                $qty='0';
            else
                $qty=empty($product->quantity)?'100':$product->quantity;
                
            if(!empty($product->allowance))
                $qty = $product->allowance;

            $dataArray[]=  [
                "Account"=>$product->account,
                "InventoryAction"=>'Modify',
                "Site"=>'walmart',
                "SellerSKU"=>$product->asin,
                "Price"=>$product->price==0?99.99:$product->price,
                "Location"=>'My Warehouse',
                "MaxListing Buffer"=>empty($product->maxListingBuffer)?'2':$product->maxListingBuffer,
                "Leadtime to Ship"=>$product->lagTime,
                'Price (minimum)'=>'0',
                'Price (maximum)'=>'0',
                'Quantity' =>$qty,
        ];
        }
        
        return collect($dataArray);

    }

    public function headings(): array
    {
        return [
            'Account','InventoryAction','Site','SellerSKU','Price','Location','MaxListing Buffer','Leadtime to Ship','Price (minimum)','Price (maximum)', 'Quantity'
        ];
    }
}
