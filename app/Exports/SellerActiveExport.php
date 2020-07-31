<?php

namespace App\Exports;

use App\products;
use App\blacklist;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SellerActiveExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $products = products::leftJoin('accounts','products.account','accounts.store')
        ->leftJoin('blacklist','products.asin','blacklist.sku')
        ->select(['products.*','accounts.lagTime','accounts.quantity','accounts.maxListingBuffer','blacklist.allowance'])    
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
                "Price"=>$product->price,
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
