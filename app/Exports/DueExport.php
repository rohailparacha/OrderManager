<?php

namespace App\Exports;

use App\orders;
use App\accounts;
use App\order_details;
use App\carriers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DueExport implements FromCollection,WithHeadings,ShouldAutoSize
{

    protected $dateRange; 
    protected $storeFilter; 
    protected $accountFilter;

    public function __construct($dateRange,$storeFilter,$accountFilter)
    {
        $this->dateRange = $dateRange;
        $this->storeFilter = $storeFilter;
        $this->accountFilter = $accountFilter;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

 public static function getIranTime($date)
    {
        
        $datetime = new \DateTime($date);        
        
        return $datetime->format('m/d/Y H:i:s');
        
    }
    public function getTotalShipping($id)
    {

        $details = order_details::where('order_id',$id)->get(); 
        $total = 0; 
        foreach($details as $detail)
        {
            $price = $detail->shippingPrice; 

            if(empty($price))
                $total = $total + 0; 

            else

                $total = $total + $price;
        }

        return $total;

    }

    public function collection()
    {
        $carriers = carriers::all(); 
        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }
        
        $dateRange = $this->dateRange;
        $storeFilter = $this->storeFilter;
        $accountFilter = $this->accountFilter;
       

        $startDate = explode('-',$dateRange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$dateRange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 

        $orders = orders::select();

        if(auth()->user()->role==1)
                $orders = orders::select()->where(function($test){
                    $test->where('status', 'processing');
                    $test->orWhere('status', 'unshipped');
                }) ;

        elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();

                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::select()->where(function($test){
                    $test->where('status', 'processing');
                    $test->orWhere('status', 'unshipped');
                }) ->whereIn('storeName',$strArray);
            }        
        else
            $orders = orders::select()->where(function($test){
                $test->where('status', 'processing');
                $test->orWhere('status', 'unshipped');
            })->where('uid',auth()->user()->id);
        
        if(!empty($startDate)&& !empty($endDate))
        {
            $orders = $orders->whereBetween('date', [$from, $to]);
        }

        if(!empty($storeFilter)&& $storeFilter !=0)
        {
            $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
            $orders = $orders->where('storeName',$storeName->store);
        }
        
        if(!empty($accountFilter)&& $accountFilter !='0')
        {            
            
            $orders = $orders->where('account_id',$accountFilter);
        }
            
        
        foreach($orders as $order)
        {

            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        $orders = $orders->get();

        foreach($orders as $order)
        {

            $dataArray[]=  [
                "Date"=> $this->getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')),
                "Due Ship"=> $this->getIranTime(date_format(date_create($order->dueShip), 'm/d/Y H:i:s')),
                "Marketplace"=>$order->marketplace,
                "Store Name"=>$order->storeName,
                "Account"=>$order->account_id,
                "Buyer Name"=>$order->buyerName,
                "Sell Order ID"=>$order->sellOrderId,
                "Quantity"=>$order->quantity,
                "Sell Total"=> number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', ''),
                "Purchase Order ID"=>$order->poNumber,
                "Purchase Total"=> number_format((float)$order->poTotalAmount, 2, '.', ''),
                "Carrier Name"=>empty($order->carrierName)?"":$carrierArr[$order->carrierName],
                'Remaining Days'=>\Carbon\Carbon::now()->diffInDays( \Carbon\Carbon::parse($order->dueShip)->format('Y-m-d'),false )." days",
                "Tracking Number"=>$order->trackingNumber,
                "Status"=>$order->status
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Date','Due Ship','Marketplace','Store Name','Account','Buyer Name','Sell Order ID','Quantity','Sell Total','Purchase Order ID','Purchase Total','Carrier Name','Remaining Days','Tracking Number','Status'
        ];
    }

    
}
