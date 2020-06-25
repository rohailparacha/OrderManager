<?php

namespace App\Exports;
use App\accounts;
use URL;
use App\products;
use App\strategies;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductsExport implements FromCollection,WithHeadings,ShouldAutoSize
{    
    protected $accountFilter; 
    protected $strategyFilter; 
    protected $sellerFilter; 
    protected $amountFilter; 

    public function __construct($accountFilter,$strategyFilter,$sellerFilter, $amountFilter)
    {
        $this->accountFilter = $accountFilter;
        $this->strategyFilter = $strategyFilter;
        $this->sellerFilter = $sellerFilter;
        $this->amountFilter = $amountFilter;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {        
        $accountFilter = $this->accountFilter;
        $strategyFilter = $this->strategyFilter;
        $sellerFilter = $this->sellerFilter;
        $amountFilter = $this->amountFilter;

        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
        
        $minSeller = trim(explode('-',$sellerFilter)[0]);
        $maxSeller = trim(explode('-',$sellerFilter)[1]);        

        //now show orders
        $products = products::select();
                           

        if(!empty($accountFilter)&& $accountFilter !=0)
        {   
            $account= accounts::where('id',$accountFilter)->get()->first();          
            $products = $products->where('account',$account->store);
        }

        if(!empty($strategyFilter)&& $strategyFilter !=0)
        {            
            $products = $products->where('strategy_id',$strategyFilter);
        }

            $products = $products->whereBetween('totalSellers',[$minSeller,$maxSeller]);            
        
            $products = $products->whereBetween('price',[$minAmount,$maxAmount]);
        
        $products  = $products->get();

        $strategyCodes = array(); 
        $strategies = strategies::select()->get(); 
        
        foreach($strategies as $strategy)
        {
            $strategyCodes[$strategy->id] = $strategy->code;
        }

        foreach($products as $product)
        {

            $dataArray[]=  [
                "Original Image" =>$product->image,
                "Image"=> URL::to('/').'/images/amazon/' . $product->asin.'.jpg',
                "Account"=>$product->account,
                "ASIN"=>$product->asin,
                "UPC"=>$product->upc,
                "Title"=>$product->title,
                'Total FBA Sellers'=>$product->totalSellers,
                "Lowest FBA Price"=> number_format((float)$product->lowestPrice, 2, '.', ''),
                "Price"=> number_format((float)$product->price, 2, '.', ''),
                "Strategy"=>empty($product->strategy_id)?"":$strategyCodes[$product->strategy_id],
                
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Original Image','Image','Account','ASIN','UPC','Title','Total FBA Sellers','Lowest FBA Price','Price','Strategy'
        ];
    }
}
