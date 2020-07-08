<?php

namespace App\Exports;
use App\walmart_products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class WalmartProductsExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{
    protected $sellersFilter; 
    protected $daterange; 
    protected $amountFilter; 

    public function __construct($sellersFilter,$daterange, $amountFilter)
    {
        $this->sellersFilter = $sellersFilter;
        $this->daterange = $daterange;
        $this->amountFilter = $amountFilter;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {        
        $sellersFilter = $this->sellersFilter;
        $daterange = $this->daterange;
        $amountFilter = $this->amountFilter;

        $startDate = explode('-',$daterange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$daterange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 
       
    
       $minAmount = trim(explode('-',$amountFilter)[0]);
       $maxAmount = trim(explode('-',$amountFilter)[1]);
             

       //now show orders
       $products = walmart_products::select();
                                  
        
       $products = $products->whereBetween('price',[$minAmount,$maxAmount]);
       
       if(!empty($startDate)&& !empty($endDate))
       {
           $products = $products->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
       }
       
       if(!empty($sellersFilter)&& $sellersFilter !='0')
       {            
           $products = $products->where('seller',$sellersFilter);
       }
       
       $products  = $products->orderBy('created_at','desc')->get(); 
 
       $dataArray = array();

        foreach($products as $product)
        {

            $dataArray[]=  [
                "Date"=> date('m/d/Y H:i:s',strtotime($product->created_at)),
                "Image"=>$product->image,              
                "Title"=>$product->name,
                "Attribute"=>strval($product->productIdType),
                "Attribute Value"=> $product->productId,
                "Seller Name"=> $product->seller,
                "Price"=> number_format((float)$product->price, 2, '.', ''),
                "Link"=> "https://www.walmart.com/".$product->link,                                
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Date','Image','Title','Attribute','Attribute Value','Seller Name','Price','Link'];
    }
    
    public function columnFormats(): array
    {
        return [            
            'E' => '0',
        ];
    }
}
