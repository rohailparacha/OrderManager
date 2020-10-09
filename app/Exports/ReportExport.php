<?php

namespace App\Exports;

use App\orders;
use App\accounts;
use App\order_details;
use App\carriers;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportExport implements FromCollection,WithHeadings,ShouldAutoSize
{

    protected $dateRange; 
    protected $storeFilter; 
    protected $marketFilter; 
    protected $statusFilter; 
    protected $userFilter; 

    public function __construct($dateRange,$storeFilter,$marketFilter,$statusFilter, $userFilter)
    {
        $this->dateRange = $dateRange;
        $this->storeFilter = $storeFilter;
        $this->marketFilter = $marketFilter;
        $this->statusFilter = $statusFilter;
        $this->userFilter = $userFilter;
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
        $marketFilter = $this->marketFilter;
        $statusFilter = $this->statusFilter;
        $userFilter = $this->userFilter;

        $startDate = explode('-',$dateRange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$dateRange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 

        $orders = orders::select();

        if(!empty($startDate)&& !empty($endDate))
        {
            $orders = $orders->whereBetween('date', [$from, $to]);
        }

        if(!empty($userFilter)&& !empty($userFilter))
        {
            $orders = $orders->where('uid',$userFilter);
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
                $orders = $orders->where('marketplace','AmazonUK');
            elseif($marketFilter==3)
                $orders = $orders->where('marketplace','eBay');
            elseif($marketFilter==4)
                $orders = $orders->where('marketplace','Magento');
            elseif($marketFilter==5)
                $orders = $orders->where('marketplace','Volusion');
            elseif($marketFilter==6)
                $orders = $orders->where('marketplace','AmazonCA');
            elseif($marketFilter==7)
                $orders = $orders->where('marketplace','Yahoo');
            elseif($marketFilter==8)
                $orders = $orders->where('marketplace','UltraCart');
            elseif($marketFilter==9)
                $orders = $orders->where('marketplace','Walmart');            
        }

        if(!empty($statusFilter)&& $statusFilter !=0)
        {
            if($statusFilter==1)
                $orders = $orders->where('status','Unshipped');
            elseif($statusFilter==2)
                $orders = $orders->where('status','Pending');
            elseif($statusFilter==3)
                $orders = $orders->where('status','Processing');
            elseif($statusFilter==4)
                $orders = $orders->where('status','Cancelled');            
            elseif($statusFilter==5)
                $orders = $orders->where('status','Shipped');
        }

        $orders= $orders->orderBy('date', 'DESC')->get();
        
        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        foreach($orders as $order)
        {

            $dataArray[]=  [
                "Date"=> $this->getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')),
                "Marketplace"=>$order->marketplace,
                "Store Name"=>$order->storeName,
                "Buyer Name"=>$order->buyerName,
                "Sell Order ID"=>$order->sellOrderId,
                "Sell Total"=> number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', ''),
                "Purchase Order ID"=>$order->poNumber,
                "Purchase Total"=> number_format((float)$order->poTotalAmount, 2, '.', ''),
                "Carrier Name"=>empty($order->carrierName)?"":$carrierArr[$order->carrierName],
                "Tracking Number"=>$order->trackingNumber,
                "Status"=>$order->status
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Date','Marketplace','Store Name','Buyer Name','Sell Order ID','Sell Total','Purchase Order ID','Purchase Total','Carrier Name','Tracking Number','Status'
        ];
    }

    
}
