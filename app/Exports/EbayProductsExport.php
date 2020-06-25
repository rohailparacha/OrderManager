<?php

namespace App\Exports;
use App\ebay_products;
use App\ebay_strategies;
use App\categories;
use App\accounts;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use URL;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EbayProductsExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{
    
    protected $categoryFilter; 
    protected $strategyFilter; 
    protected $daterange; 
    protected $amountFilter; 

    public function __construct($categoryFilter,$strategyFilter,$daterange, $amountFilter)
    {
        $this->categoryFilter = $categoryFilter;
        $this->strategyFilter = $strategyFilter;
        $this->daterange = $daterange;
        $this->amountFilter = $amountFilter;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {        
        $categoryFilter = $this->categoryFilter;
        $strategyFilter = $this->strategyFilter;
        $daterange = $this->daterange;
        $amountFilter = $this->amountFilter;

        $startDate = explode('-',$daterange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$daterange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 
       
    
       $minAmount = trim(explode('-',$amountFilter)[0]);
       $maxAmount = trim(explode('-',$amountFilter)[1]);
             

       //now show orders
       $products = ebay_products::select();
                                 
       if(!empty($categoryFilter)&& $categoryFilter !=0)
       {               
           $products = $products->where('category_id',$categoryFilter);
       }

       if(!empty($strategyFilter)&& $strategyFilter !=0)
       {            
           $products = $products->where('strategy_id',$strategyFilter);
       }
       
       $products = $products->whereBetween('ebayPrice',[$minAmount,$maxAmount]);
       
       if(!empty($startDate)&& !empty($endDate))
       {
            $products = $products->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
       }

        $products  = $products->orderBy('created_at','desc')->get();
        
        $strategyCodes = array(); 
        $categoryCodes = array(); 
        $accountCodes = array(); 

        $strategies = ebay_strategies::select()->get(); 
        $categories = categories::select()->get(); 
        $accounts = accounts::select()->get(); 

        foreach($strategies as $strategy)
        {
            $strategyCodes[$strategy->id] = $strategy->code;
        }

        foreach($categories as $category)
        {
            $categoryCodes[$category->id] = $category->name;
        }

        foreach($accounts as $account)
        {
            $accountCodes[$account->id] = $account->store;
        }
        
        $dataArray = array();

        foreach($products as $product)
        {

            $dataArray[]=  [
                "SKU"=> strval($product->sku),
                "Account" => empty($product->account_id)?"":$accountCodes[$product->account_id],
                "Product Name"=>$product->name,              
                "Product Id Type"=>$product->productIdType,
                "Product Id"=>strval($product->productId),
                "Description"=>$product->description,
                'Brand'=>$product->brand,            
                "Original Primary Image"=> $product->primaryImg,
                "Primary Image"=> URL::to('/').'/images/ebay/' . $product->sku.'-1.jpg',
                "Original Secondary Image"=> $product->secondaryImg,
                "Secondary Image"=> empty($product->secondaryImg)?"":URL::to('/').'/images/ebay/' . $product->sku.'-2.jpg',                
                "Ebay Price"=> number_format((float)$product->ebayPrice, 2, '.', ''),
                "Pricing Strategy"=>empty($product->strategy_id)?"":$strategyCodes[$product->strategy_id],
                "Our Price"=> number_format((float)$product->price, 2, '.', ''),
                "Category" => empty($product->category_id)?"":$categoryCodes[$product->category_id],
                "Link"=> "https://www.ebay.com/itm/".$product->sku,                                
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'SKU','Account','Product Name','Product Id Type','Product Id','Description','Brand','Original Primary Image','Primary Image','Original Secondary Image','Secondary Image','Ebay Price','Pricing Strategy','Our Price','Category','Link'
        ];
    }
    
    public function columnFormats(): array
    {
        return [
            'A' => '0',
            'E' => '0',
        ];
    }
}
