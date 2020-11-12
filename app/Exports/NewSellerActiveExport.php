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

class NewSellerActiveExport implements FromCollection,WithHeadings,ShouldAutoSize,WithStrictNullComparison
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $collection; 
    protected $account;

    public function __construct($collection, $account)
    {
        $this->collection = $collection;
        $this->account = $account;
        
    }

    public function collection()
    {
        //
        $collection = $this->collection;     
        $account = $this->account;        

        $setting = amazon_settings::get()->first();
       
        $dataArray = array();

        foreach($collection as $col)
        {
            $product = products::where('asin',$col['asin'])
            ->leftJoin('accounts','products.account','accounts.store')
            ->leftJoin('blacklist','products.asin','blacklist.sku')
            ->select(['products.*','accounts.lagTime','accounts.quantity','accounts.maxListingBuffer','blacklist.allowance'])
            ->where('account',$account)
            ->get()->first();

            if(empty($product))
                continue;
            if(empty($product->asin))
                continue; 
                
            $qty='0';
            if($product->lowestPrice==0 || !is_numeric($col['lowestPrice']))
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
