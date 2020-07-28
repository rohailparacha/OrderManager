<?php

namespace App\Exports;
use App\orders;
use App\order_details;
use App\accounts;
use App\ebay_products;
use DB;
use App\states;
use App\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;;

class JonathanExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    protected $storeFilter; 
    protected $marketFilter; 
    protected $stateFilter; 
    protected $amountFilter; 
    protected $sourceFilter; 


    public function __construct($storeFilter,$marketFilter,$stateFilter, $amountFilter, $sourceFilter)
    {
        $this->storeFilter = $storeFilter;
        $this->marketFilter = $marketFilter;
        $this->stateFilter = $stateFilter;
        $this->amountFilter = $amountFilter;
        $this->sourceFilter = $sourceFilter;
    }

    public function collection()
    {        
        $storeFilter = $this->storeFilter;
        $marketFilter = $this->marketFilter;
        $stateFilter = $this->stateFilter;
        $amountFilter = $this->amountFilter;
        $sourceFilter = $this->sourceFilter;

        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
            
        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->leftJoin('ebay_products','order_details.SKU','=','ebay_products.sku')
        ->select(['orders.*',DB::raw('IFNULL( products.lowestPrice, 0) as lowestPrice'),'products.asin','ebay_products.sku'])
        ->where('flag','10');
        
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


        
        $orders = $orders->whereBetween(DB::raw('IFNULL( products.lowestPrice, 0)'),[$minAmount,$maxAmount]);

        if(!empty($stateFilter)&& $stateFilter !='0')
        {           
            $orders = $orders->where('state',$stateFilter);
        }
                
        if(auth()->user()->role==1|| auth()->user()->role==2)
            $orders = $orders->where('status','unshipped')->orderBy('date', 'ASC')->groupby('orders.id')->get();
        else
            $orders = $orders->where('status','unshipped')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->get();
        
        
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
            $flag='';
            if($order->flag==1)
                $flag='Overpriced';
            elseif($order->flag==2)
                $flag='Quantity Limit';
            elseif($order->flag==3)
                $flag='Unavailable';
            elseif($order->flag==4)
                $flag='Date';
            elseif($order->flag==5)
                 $flag='Address Issue';
            elseif($order->flag==6)
                $flag='Other';
            elseif($order->flag==7)
                $flag='Tax Issue';    
            elseif($order->flag==8)
                $flag='Cindy'; 
            elseif($order->flag==9)
                $flag='Jonathan';                                                 
            elseif($order->flag==10)
                $flag='Samuel'; 
   
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
                "Zip Code"=> $order->postalCode,
                "Purchase Price" => number_format((float)$order->lowestPrice , 2, '.', ''),
                "Store Name" => $order->storeName,
                "Flag" => $flag,
                             
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
            'Date','Sell Order ID','Buyer Name','Address1','Address2','City','State','Zip Code','Purchase Price','Store Name','Flag','SKU1','Qty','SKU2','Qty'
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
                    $total = $total + $price->ebayPrice;   
                }

            else
                $total = $total + $price->lowestPrice;
        }

        return $total;

    }
}
