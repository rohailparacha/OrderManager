<?php

namespace App\Exports;
use App\orders;
use App\accounts;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UPSExport implements WithColumnFormatting,FromCollection,WithHeadings,ShouldAutoSize
{
    protected $storeFilter; 
    protected $daterange; 
    

    public function __construct($storeFilter,$daterange)
    {
        $this->storeFilter = $storeFilter;
        $this->daterange = $daterange;
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {        
        $storeFilter = $this->storeFilter;
        $daterange = $this->daterange;

        $startDate = explode('-',$daterange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$daterange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 
            

        if(auth()->user()->role==1)            
        {
            $orders = orders::where('isBCE',true)            
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            });                
         
            $count = orders::select()->where('isBCE',true)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }            
            
            $orders = orders::where('isBCE',true)
            ->whereIn('storeName',$strArray)            
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            });
                      

            $count = orders::select()->where('isBCE',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 

            
        }
            
    else
            $orders = array();


    if(!empty($storeFilter)&& $storeFilter !=0)
    {
        $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
        $orders = $orders->where('storeName',$storeName->store);
    }

    if(!empty($startDate)&& !empty($endDate))
    {
        $orders = $orders->whereBetween('date', [$from.' 00:00:00', $to.' 23:59:59']);
    }

    $orders = $orders->orderBy('orders.status', 'ASC')->get(); 
    

       $dataArray = array();

        foreach($orders as $order)
        {

            $dataArray[]=  [
                "Date"=> date('m/d/Y',strtotime($order->of_bce_created_at)),
                "Order Date"=> date('m/d/Y',strtotime($order->date)),                
                "Store Name"=>$order->storeName,
                "Buyer Name"=>$order->buyerName,
                "Sell Order Id"=> $order->sellOrderId,
                "Purchase Order Id"=> $order->poNumber,
                "City"=> $order->city,
                "State"=> $order->state,                                
                "Zip Code"=> $order->postalCode,                               
                "Old Tracking Number"=> $order->trackingNumber,
                "UPS Tracking Number"=> $order->upsTrackingNumber
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Date','Order Date','Store Name','Buyer Name','Sell Order Id','Purchase Order Id','City','State','Zip Code','Old Tracking Number','UPS Tracking Number'];
    }
    
    public function columnFormats(): array
    {
        return [            
            'E' => '0',
        ];
    }
}
