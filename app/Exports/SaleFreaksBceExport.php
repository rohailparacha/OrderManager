<?php

namespace App\Exports;
use App\carriers; 
use App\orders;
use App\accounts;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;


class SaleFreaksBceExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $flag; 

    public function __construct($flag)
    {
        $this->flag = $flag; 
    }

    public function collection()
    {
        //
        $flag =  $this->flag;  
        $accName = '';
              
        if($flag == '22')
            $accName = 'SaleFreaks1';
        
        if($flag == '23')
            $accName = 'SaleFreaks2';
        
        if($flag == '24')
            $accName = 'SaleFreaks3';  
        
        if($flag == '25')
            $accName = 'SaleFreaks4';
        
        if($flag == '26')
            $accName = 'SaleFreaks5';

        $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
        if(auth()->user()->role==1)            
        {
            $orders = orders::select()->where('converted',false)->where('account_id',$accName)
            ->where('marketPlace','Walmart')
            ->where('carrierName',$amzCarrier->id)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('of_bce_created_at', 'ASC')->paginate(100);
        }
    
        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();
    
            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
            
            $orders = orders::select()->where('converted',false)
            ->where('marketPlace','Walmart')
            ->where('carrierName',$amzCarrier->id)
            ->where('account_id',$accName)->whereIn('storeName',$strArray)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('of_bce_created_at', 'ASC')->paginate(100);
    
         
            
        }
            
        else
            $orders = array();

            foreach($orders as $order)
            {
                
                $temp = array();
                $temp =  [                    
                    "Order Number"=> $order->afpoNumber,
                    "TBA Tracking Number"=>$order->trackingNumber                           
                ];
    
                $dataArray[]= $temp;
            }
            
            return collect($dataArray);
    }

    public function headings(): array
    {
        return [
            'Order Number','TBA Tracking Number'
        ];
    }

   

}
