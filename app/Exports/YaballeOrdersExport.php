<?php

namespace App\Exports;
use App\orders;
use App\order_details;
use App\accounts;
use App\ebay_products;
use App\flags;
use DB;
use App\states;
use App\products;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class YaballeOrdersExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{    

    public function __construct()
    {
        
    }

    public function collection()
    {        
        $dataArray = array();    

        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),'products.asin'])->where('status','unshipped')          
        ->groupBy('orders.id')        
        ->where('flag','17');
        
        if(auth()->user()->role==1|| auth()->user()->role==2)
            $orders = $orders->where('status','unshipped')->orderBy('date', 'ASC')->groupby('orders.id')->get();
        else
            $orders = $orders->where('status','unshipped')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->get();
        
        
        foreach($orders as $order)
        {                                  
            $order_details = order_details::where('order_id',$order->id)->selectRaw("*, SUM(quantity) as total_quantity")->groupBy('SKU')->get();

            if(count($order_details)>1)
                continue;
            
            $temp = array();
            $temp =  [                
                "transaction_id"=> $order->sellOrderId,
                "buyer_name"=>$order->buyerName,
                "address1"=>$order->address1,
                "address2"=>$order->address2,
                "city"=>$order->city,
                "state"=> $order->state,
                "phone" => $order->phone,
                "zip_code"=> $order->postalCode,
                "source_price" => number_format((float)$order->totalAmount , 2, '.', ''),
                "max_price" => number_format((float)$order->totalAmount , 2, '.', ''),                
                "quantity" => $order->quantity                             
            ];

            foreach($order_details as $detail)
            {                
                $temp["SKU"] = $detail->SKU;                            
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
            'transaction_id','buyer_name','address1','address2','city','state','phone','zip_code','source_price','max_price','sku','quantity'
        ];
    }

   

    public function columnFormats(): array
    {
        return [
            'K' => '0',
            'G' => '0'            
        ];
    }
}
