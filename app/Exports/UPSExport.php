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
    protected $option; 

    public function __construct($storeFilter,$daterange, $option)
    {
        $this->storeFilter = $storeFilter;
        $this->daterange = $daterange;
        $this->option = $option; 
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {        
        $storeFilter = $this->storeFilter;
        $daterange = $this->daterange;
        $option = $this->option;

        $startDate = explode('-',$daterange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$daterange)[1];
        $to = date("Y-m-d", strtotime($endDate)); 
            

        $count =0; 

        if($option == 1)
        {
            if(auth()->user()->role==1)            
            {
                $orders = orders::where('isBCE',true)            
                ->where(function($test){
                    $test->where('orders.status','processing');
                })
                ->whereNull('upsTrackingNumber')
                ->where(function($rest){
                    $rest->where('trackingNumber','like','TBA%');
                    $rest->orWhere('trackingNumber','like','BCE%');
                })
                
                ->orderBy('orders.date', 'ASC');
               
                      
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
                })
                 ->whereNull('upsTrackingNumber')
                ->where(function($rest){
                    $rest->where('trackingNumber','like','TBA%');
                    $rest->orWhere('trackingNumber','like','BCE%');
                })
                ->orderBy('orders.date', 'ASC');
                          
  
    
                
            }
                
            else
                    $orders = array();
    
        }

        elseif($option == 2)
        {
            if(auth()->user()->role==1)            
            {
                $orders = orders::where('isBCE',true)            
                ->where(function($test){
                    $test->where('orders.status','processing');
                })
                ->whereNotNull('upsTrackingNumber')
                ->where(function($rest){
                    $rest->where('trackingNumber','like','TBA%');
                    $rest->orWhere('trackingNumber','like','BCE%');
                })
                
                ->orderBy('orders.date', 'ASC');
               
             
    
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
                })
                 ->whereNotNull('upsTrackingNumber')
                ->where(function($rest){
                    $rest->where('trackingNumber','like','TBA%');
                    $rest->orWhere('trackingNumber','like','BCE%');
                })
                ->orderBy('orders.date', 'ASC');                                      
    
                
            }
                
            else
                    $orders = array();
    
        }
       
        $stores = accounts::all();         


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
