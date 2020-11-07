<?php

namespace App\Exports;
use App\carriers; 
use App\orders;
use App\accounts;
use App\cancelled_orders;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SaleFreaksCancelExport implements FromCollection,WithHeadings,ShouldAutoSize
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
        $flag =  $this->flag;  

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
            
       if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id',$accName)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('cancelled_orders.created_at', 'ASC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.created_at AS ordercreatedate','cancelled_orders.id AS cancelledId'])
            ->paginate(100);
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
            
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id',$accName)
            ->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.orders.','shipped');
            }) 
            ->orderBy('cancelled_orders.created_at', 'ASC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.created_at AS ordercreatedate','cancelled_orders.id AS cancelledId'])
            ->paginate(100);
        }
            
        else
            $orders = array();

            foreach($orders as $order)
            {
                
                $temp = array();
                $temp =  [                    
                    "Order Number"=> $order->afpoNumber,
                    "Status"=>$order->orderStatus                           
                ];
    
                $dataArray[]= $temp;
            }
            
            return collect($dataArray);
    }

    public function headings(): array
    {
        return [
            'Order Number','Status'
        ];
    }

   

}
