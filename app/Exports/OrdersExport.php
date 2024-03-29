<?php

namespace App\Exports;
use App\orders;
use App\order_details;
use App\accounts;
use App\ebay_products;
use App\order_settings;
use DB;
use App\states;
use App\flags;
use App\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class OrdersExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $storeFilter; 
    protected $marketFilter; 
    protected $stateFilter; 
    protected $amountFilter; 
    protected $sourceFilter; 
    protected $flagFilter;
    protected $route; 


    public function __construct($storeFilter,$marketFilter,$stateFilter, $amountFilter, $sourceFilter,$flagFilter, $route)
    {
        $this->storeFilter = $storeFilter;
        $this->marketFilter = $marketFilter;
        $this->stateFilter = $stateFilter;
        $this->amountFilter = $amountFilter;
        $this->sourceFilter = $sourceFilter;
        $this->flagFilter = $flagFilter;
        $this->route = $route; 
    }

    public function collection()
    {        
        $price1 = order_settings::get()->first()->price1; 
        $price2 = order_settings::get()->first()->price2; 
        $dataArray = array(); 
        $storeFilter = $this->storeFilter;
        $marketFilter = $this->marketFilter;
        $stateFilter = $this->stateFilter;
        $amountFilter = $this->amountFilter;
        $sourceFilter = $this->sourceFilter;
        $flagFilter = $this->flagFilter;
        $route = $this->route; 

        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
            
        $val = flags::where('name','Expensive')->get()->first(); 

        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag','!=','16')->where('flag','!=','17')
        ->where('flag','!=','22')
        ->where('flag','!=','23')
        ->where('flag','!=','24')
        ->where('flag','!=','25')
        ->where('flag','!=','26')    
        ->groupBy('orders.id')        
        ->where('flag','!=','8')
        ->where('flag','!=','9')
        ->where('flag','!=','10')
        ->where('flag','!=','16')
        ->where('flag','!=','17')
        ->where('flag','!=','22')
        ->where('flag','!=','23')
        ->where('flag','!=','24')
        ->where('flag','!=','25')
        ->where('flag','!=','26')
        ;
        
        if($route == 'new')
            $orders = $orders->where('flag','0');
        elseif($route=='flagged')
        {
            $orders = $orders->where('flag','!=','0');
            if(!empty($flagFilter)&& $flagFilter !='0')
            {           
                $orders = $orders->where('flag',$flagFilter);
            }
        }
            
        elseif($route =='multi')
            $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1');
        elseif($route=='price1')
        {
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1);
        }
        elseif($route=='price2')
        {
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2);
        }
        elseif($route=='expensive')
        {
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2);
        }
        elseif($route=='zero')
        {
            $orders = $orders->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0');
        }
        elseif($route=='food')
        {
            $orders = $orders->where('products.category','Food');
        }
        elseif($route=='movie')
        {
            $orders = $orders->where('products.category','Movie');
        }

        elseif($route=='minus')
        {           
            $orders = $orders->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2');       
             
        }
        
        elseif($route=='checked')
        {
            $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'<=','1')
            ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'!=','0')
           ->where(function($test){
                        $test->whereNull('products.category');
                        $test->orWhere('products.category','!=','Movie');
                        
                    })                         
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
            ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'>=','2');           
        }
        
        
        if(!empty($storeFilter)&& $storeFilter !=0)
        {
            $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
            $orders = $orders->where('storeName',$storeName->store);
        }
        

        if(!empty($marketFilter)&& $marketFilter !=0)
        {                            
            if($marketFilter==1)
                $orders = $orders->where('marketplace','Amazon');
            elseif($marketFilter==2)
                $orders = $orders->where('marketplace','eBay');
            elseif($marketFilter==3)
                $orders = $orders->where('marketplace','Walmart');
                      
        }

        if(!empty($sourceFilter)&& $sourceFilter !=0)
        {                            
            if($sourceFilter==1)
                $orders = $orders->whereNotNull('products.asin');
            elseif($sourceFilter==2)
                $orders = $orders->whereNotNull('ebay_products.sku');                                
        }


        
        $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>=',$minAmount);
        $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$maxAmount);


        if(!empty($stateFilter)&& $stateFilter !='0')
        {           
            $orders = $orders->where('state',$stateFilter);
        }

        if($route == 'new')
                $orders = $orders->where('isChecked',false);    
            else
                $orders = $orders->where('isChecked',true);

         
        if(auth()->user()->role==1|| auth()->user()->role==2)        
            $orders = $orders->where('isChecked',true)->where('status','unshipped')->orderBy('date', 'ASC')->groupby('orders.id')->get();
        else
            $orders = $orders->where('isChecked',true)->where('status','unshipped')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->get();
        
        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();

     
        
        $maxPrice = ceil(orders::where('status','unshipped')->max('totalAmount'));
        foreach($orders as $order)
        {        
            $order->lowestPrice = $this->getLowestPrice($order->id);

            $sources = array();
            
            $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
            foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }

        foreach($orders as $order)
        {
            $flagName  = flags::where('id',$order->flag)->get()->first();
            if(empty($flagName))
                $flagName= '';
            else
                $flagName = $flagName->name;

            $counter=0; 
            $order_details = order_details::where('order_id',$order->id)->get();
            $temp = array();
            $temp =  [
                "Date" =>$this->getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')),
                "Sell Order ID"=> $order->sellOrderId,
                "Buyer Name"=>$order->buyerName,
                "Address1"=>$order->address1,
                "Address2"=>$order->address2,
                "City"=>$order->city,
                'State'=> $order->state,
                "Phone" => $order->phone,
                "Zip Code"=> $order->postalCode,
                "Purchase Price" => number_format((float)$order->lowestPrice , 2, '.', ''),
                "Store Name" => $order->storeName,
                "Flag" => $flagName,
                
                             
            ];

            foreach($order_details as $detail)
            {
                $counter++;
                $temp["SKU".$counter] = $detail->SKU;
                $temp["Qty".$counter] = $detail->quantity;
                
            }

            $dataArray[]= $temp;
        }
        
        return collect($dataArray);
        
    }

    public static function getIranTime($date)
    {
        
        $datetime = new \DateTime($date);        
        
        return $datetime->format('m/d/Y H:i:s');
        
    }

    public function headings(): array
    {
        return [
            'Date','Sell Order ID','Buyer Name','Address1','Address2','City','State','Phone','Zip Code','Purchase Price','Store Name','Flag','SKU1','Qty','SKU2','Qty'
        ];
    }

    public function getLowestPrice($id)
    {

        $details = order_details::where('order_id',$id)->get(); 
        $total = 0; 
        foreach($details as $detail)
        {
            $price  = products::select('lowestPrice')->where('asin',$detail->SKU)->get()->first(); 

            if(empty($price))
                {
                    $total = $total + 0; 
                    $price  = ebay_products::select('ebayPrice')->where('sku',$detail->SKU)->get()->first(); 
                    if(empty($price))
                    {
                        $total = $total + 0; 
                    }
                    else
                    $total = $total + ($price->ebayPrice * $detail->quantity);   
                }

            else
                $total = $total + ($price->lowestPrice * $detail->quantity);
        }

        return $total;

    }
    public function columnFormats(): array
    {
        return [
            'H' => '0'            
        ];
    }
   
}
