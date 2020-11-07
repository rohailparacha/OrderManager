<?php

namespace App\Exports;
use App\orders;
use App\order_details;
use App\settings;
use App\products;
use App\accounts;
use App\ebay_products;
use App\flags;
use DB;
use App\states;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class SaleFreaksOrdersExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{    
    protected $flag; 

    public function __construct($flag)
    {
        $this->flag = $flag; 
    }

    public function collection()
    {        
      
        $dataArray = array();    

        $flag = $this->flag; 
        
        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),'products.asin'])->where('status','unshipped')          
        ->groupBy('orders.id')        
        ->where('flag',$flag);
        
        if(auth()->user()->role==1|| auth()->user()->role==2)
            $orders = $orders->where('status','unshipped')->orderBy('date', 'ASC')->groupby('orders.id')->get();
        else
            $orders = $orders->where('status','unshipped')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->get();
        
        if($flag == '22')
            $setting = settings::where('name','salefreaks1')->get()->first();     
        
        if($flag == '23')
            $setting = settings::where('name','salefreaks2')->get()->first();     
        
        if($flag == '24')
            $setting = settings::where('name','salefreaks3')->get()->first();     
        
        if($flag == '25')
            $setting = settings::where('name','salefreaks4')->get()->first();     
        
        if($flag == '26')
            $setting = settings::where('name','salefreaks5')->get()->first();     
        
        foreach($orders as $order)
        {                      
            $order_details = order_details::where('order_id',$order->id)->selectRaw("*, SUM(quantity) as total_quantity")->groupBy('SKU')->get();

            if(count($order_details)>1)
                continue;

            
            $product = products::where('asin',$order_details[0]->SKU)->get()->first(); 

            if(empty($product)) 
                continue; 
            
            
            $max_price =  empty($product->lowestPrice)?0:$product->lowestPrice * (1 +$setting->maxPrice/100);
            $price =   empty($product->lowestPrice)?0:$product->lowestPrice * $order->quantity;


            $temp=
            [
            'Sales Record Number'=> $order->sellOrderId,
            'Order Number'=> $order->sellOrderId,
            'Buyer Username'=>'1111"',
            'Buyer Name'=>$order->buyerName,
            'Buyer Email'=>'1111"',
            'Buyer Note'=>'1111"',
            'Buyer Address 1'=>$order->address1,
            'Buyer Address 2'=>$order->address2,
            'Buyer City'=>$order->city,
            'Buyer State'=> $order->state,
            'Buyer Zip'=> $order->postalCode,
            'Buyer Country'=> $order->country,
            'Ship To Name'=>$order->buyerName,
            'Ship To Phone'=>$order->phone,
            'Ship To Address 1'=>$order->address1,
            'Ship To Address 2'=>$order->address2,
            'Ship To City'=>$order->city,
            'Ship To State'=>$order->state,
            'Ship To Zip'=> $order->postalCode,
            'Ship To Country'=> $order->country,
            'Item Number'=>'1111"',
            'Item Title'=>'1111"',
            'Custom Label'=>$order_details[0]->SKU, 
            'Sold Via Promoted Listings'=>'1111"',
            'Quantity' =>  $order->quantity,
            'Sold For'=>'$'.number_format((float)$max_price , 2, '.', ''), 
            'Shipping And Handling' =>'$0.00',
            'Seller Collected Tax' =>'$0.00',
            'eBay Collected Tax' =>'$0.00',
            'Electronic Waste Recycling Fee' =>'$0.00',
            'Mattress Recycling Fee' =>'$0.00',
            'Additional Fee' =>'$0.00',
            'Total Price'=>'$'.number_format((float)$max_price , 2, '.', ''), 
            'eBay Collected Tax and Fees Included in Total'=>'1111"',
            'Payment Method'=>'1111"',
            'Sale Date' =>date_format(date_create($order->date), 'M-d-y'),
            'Paid On Date' =>date_format(date_create($order->date), 'M-d-y'),
            'Ship By Date' =>date_format(date_create($order->dueShip), 'M-d-y'),
            'Minimum Estimated Delivery Date' =>date_format(date_create($order->date), 'M-d-y'),
            'Maximum Estimated Delivery Date' =>date_format(date_create($order->dueDelivery), 'M-d-y'),
            'Shipped On Date'=>'1111"',
            'Feedback Left'=>'1111"',
            'Feedback Received'=>'1111"',
            'My Item Note'=>'1111"',
            'PayPal Transaction ID'=>'1111"',
            'Shipping Service'=>'1111"',
            'Tracking Number'=>'1111"',
            'Transaction ID'=> $order->sellOrderId,
            'Variation Details'=>'1111"',
            'Global Shipping Program'=>'1111"',
            'Global Shipping Reference ID'=>'1111"',
            'Click And Collect'=>'1111"',
            'Click And Collect Reference Number'=>'1111"',
            'eBay Plus'=>'1111"',
            'Authenticity Verification Program'=>'1111"',
            'Authenticity Verification Status'=>'1111"',
            'Authenticity Verification Outcome Reason'=>'1111"',
            'Tax City'=>'1111"',
            'Tax State'=>'1111"',
            'Tax Zip'=>'1111"',
            'Tax Country'=>'1111"'
            ];

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
        return 
        [           
            'Sales Record Number','Order Number','Buyer Username','Buyer Name','Buyer Email','Buyer Note','Buyer Address 1','Buyer Address 2','Buyer City','Buyer State','Buyer Zip','Buyer Country','Ship To Name','Ship To Phone','Ship To Address 1','Ship To Address 2','Ship To City','Ship To State','Ship To Zip','Ship To Country','Item Number','Item Title','Custom Label','Sold Via Promoted Listings','Quantity','Sold For','Shipping And Handling','Seller Collected Tax','eBay Collected Tax','Electronic Waste Recycling Fee','Mattress Recycling Fee','Additional Fee','Total Price','eBay Collected Tax and Fees Included in Total','Payment Method','Sale Date','Paid On Date','Ship By Date','Minimum Estimated Delivery Date','Maximum Estimated Delivery Date','Shipped On Date','Feedback Left','Feedback Received','My Item Note','PayPal Transaction ID','Shipping Service','Tracking Number','Transaction ID','Variation Details','Global Shipping Program','Global Shipping Reference ID','Click And Collect','Click And Collect Reference Number','eBay Plus','Authenticity Verification Program','Authenticity Verification Status','Authenticity Verification Outcome Reason','Tax City','Tax State','Tax Zip','Tax Country'
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
