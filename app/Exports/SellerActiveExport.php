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
        $products = products::leftJoin('accounts','products.account','=','accounts.store')
        ->select(['products.*','accounts.lagTime'])        
        ->orderBy('account')->get(); 

        $dataArray = array();

        

        foreach($products as $product)
        {
            $qty='0';
            if($product->lowestPrice==0)
                $qty='0';
            else
                $qty='100';
                
            $blacklist = blacklist::all();
            
            foreach($blacklist as $bl)
            {
                if(strtolower(trim($bl->sku))==strtolower(trim($product->asin)))
                {
                    if($product->lowestPrice>0)
                    {
                        $qty=$bl->allowance;
                        break;
                    }                    
                }                    
            }

            $dataArray[]=  [
                "Account"=>$product->account,
                "InventoryAction"=>'Modify',
                "Site"=>'walmart',
                "SellerSKU"=>$product->asin,
                "Price"=>$product->price,
                "Location"=>'My Warehouse',
                "MaxListing Buffer"=>"2",
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
