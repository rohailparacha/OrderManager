<?php

namespace App\Exports;
use App\carriers; 
use App\orders;
use App\accounts;
use App\cancelled_orders;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class JonathanCancelExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
       if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id','Jonathan')
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')
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
            ->where('account_id','Jonathan')
            ->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.orders.','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')
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
