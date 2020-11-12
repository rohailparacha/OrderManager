<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\orders;
use App\accounts;
use App\carriers; 
use App\order_details;
use App\gmail_accounts;
use App\informed_settings;
use App\User;
use App\Exports\ReportExport;
use Excel; 
use DB;
use Carbon\Carbon;

class reportsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

     
    public function dailyReport(Request $request)
    {        
        $date = $request->datepicked;
        $settings = informed_settings::all();
        $accounts = accounts::all(); 
        $labels= array(); 
        $datasets = array();
        
        $sold=0; 
        $cancelled = 0; 
        $totalOrders = 0; 

        foreach($settings as $setting)
        {   
            $labels[]= $setting->minAmount."-".$setting->maxAmount;
        }

        $labels[]= 'Cancelled';

        $colors = ['red','orange','blue'];
        $cnt =0; 
        foreach($accounts as $account)
        {
            
            $label = $account->store;
            $data = array();
            
            foreach($settings as $setting)
            {
                 $col = order_details::join('orders','order_details.order_id','orders.id')
                ->leftJoin('products','order_details.SKU','products.asin')
                ->select(DB::raw('IFNULL(SUM(order_details.quantity),0) As qty'))
                ->whereBetween('products.lowestPrice',[$setting->minAmount,$setting->maxAmount])
                ->where('storeName',$account->store);

                if(empty($date))
                {
                    $col = $col->whereDate('orders.date', Carbon::today())->get()->first();
                }
                else
                {
                    $col = $col->whereDate('orders.date', Carbon::parse($date))->get()->first();
                }
                
                $data[]= $col->qty;   
            }

            $col = orders::select(DB::raw('IFNULL(SUM(orders.quantity),0) As qty'))
            ->where('status','cancelled')
            ->where('storeName',$account->store);
            
            if(empty($date))
            {
                $col = $col->whereDate('date', Carbon::today())->get()->first();
            }
            else
            {
                $col = $col->whereDate('date', Carbon::parse($date))->get()->first();
            }
            
            $data[]= $col->qty;  
            $cancelled+=$col->qty;
            
             $col = order_details::join('orders','order_details.order_id','orders.id')
                ->select(DB::raw('IFNULL(SUM(order_details.quantity),0) As qty'))
                ->where('storeName',$account->store);
                
            if(empty($date))
            {
                $col = $col->whereDate('date', Carbon::today())->get()->first();
            }
            else
            {
                $col = $col->whereDate('date', Carbon::parse($date))->get()->first();
            }
            
            $sold+=$col->qty;
            
           

            $backgroundColor = $colors[$cnt];
            $cnt++;                    

            $datasets[]= ['label'=>$label, 'data'=>$data, 'backgroundColor'=>$backgroundColor, 'pointStyle' => 'rect','borderWidth'=>'0'];
            
        }
        
        $ttlOrd = orders::select(); 
            if(empty($date))
            {
                $ttlOrd = $ttlOrd->whereDate('orders.date', Carbon::today());
            }
            else
            {
                $ttlOrd = $ttlOrd->whereDate('orders.date', Carbon::parse($date));
            }
            
        $totalOrders=$ttlOrd->count();
            
        return view('report.dailyReport',compact('settings','labels','datasets','date','sold','cancelled','totalOrders'));
    }
    
    public function index()
    {
        $startDate = orders::min('date');
        $endDate = orders::max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;
        $storeFilter = 0;
        $marketFilter = 0;
        $statusFilter = 0;
        $userFilter = 0; 

        $orders = orders::select()->orderBy('date', 'DESC')->paginate(100);

        foreach($orders as $order)
        {            
            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        $stores = accounts::select(['id','store'])->get();         
        $users = User::select(['id','name'])->get(); 

        $carriers = carriers::all(); 
        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }

        $accounts = gmail_accounts::all(); 
        return view('report.index',compact('accounts','orders','stores','dateRange','statusFilter','marketFilter','storeFilter','userFilter','carrierArr', 'users'));
    }

    public function export(Request $request)
    {
        $dateRange = $request->daterange;
        $storeFilter = $request->storeFilter;
        $marketFilter = $request->marketFilter;
        $statusFilter = $request->statusFilter;
        $userFilter = $request->userFilter; 

        $filename = date("d-m-Y")."-".time()."-report.xlsx";
        return Excel::download(new reportExport($dateRange,$storeFilter,$marketFilter,$statusFilter,$userFilter), $filename);
    }

    public function filter(Request $request)
    {
        if($request->has('daterange'))
        $dateRange = $request->get('daterange');
        $startDate = explode('-',$dateRange)[0];
        $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$dateRange)[1];
        $to = date("Y-m-d", strtotime($endDate));  
        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');
        if($request->has('marketFilter'))
            $marketFilter = $request->get('marketFilter');  
        if($request->has('statusFilter'))
            $statusFilter = $request->get('statusFilter');
        if($request->has('userFilter'))
            $userFilter = $request->get('userFilter');
        
        //now show orders
        $orders = orders::select();
        if(!empty($startDate)&& !empty($endDate))
        {
            $orders = $orders->whereBetween('date', [$from, $to]);
        }

        if(!empty($storeFilter)&& $storeFilter !=0)
        {
            $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
            $orders = $orders->where('storeName',$storeName->store);
        }

        if(!empty($userFilter)&& $userFilter !=0)
        {            
            $orders = $orders->where('uid',$userFilter);
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
        
        $orders  = $orders->orderBy('date', 'DESC')->paginate(100)->appends('daterange',$dateRange)->appends('storeFilter',$storeFilter)->appends('statusFilter',$statusFilter)->appends('marketFilter',$marketFilter)->appends('userFilter',$userFilter); 

        foreach($orders as $order)
        {            
            $order->shippingPrice = $this->getTotalShipping($order->id);
        }


        $stores = accounts::select(['id','store'])->get();

        $carriers = carriers::all(); 

        $users = User::select(['id','name'])->get(); 

        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }

        $accounts = gmail_accounts::all(); 

        return view('report.index',compact('accounts','orders','stores','dateRange','statusFilter','marketFilter','storeFilter','userFilter','carrierArr','users'));
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

     public function search(Request $request)
    {
        $query = $request->searchQuery;
        $route = $request->route; 
        
        $search = 1;

        $startDate = orders::min('date');
        $endDate = orders::max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  


        if($route == 'duplicate-record')
        {
            
            $poNumber = 0;
          
            $orders = orders::select()
            ->where(function($test) use ($query){
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                $test->orWhere('sellOrderId', 'LIKE', '%'.$query.'%');
            })->groupBy('poNumber')->where('poNumber','!=','')->paginate(100);
            
            $orders = $orders;
           
            $stores = accounts::select(['id','store'])->get();         
            
            $carriers = carriers::all(); 
            $carrierArr = array(); 
            foreach($carriers as $carrier)
            {
                $carrierArr[$carrier->id]= $carrier->name; 
            }
    
            
            $users = User::all();
            foreach($orders as $order)
            {            
                $order->shippingPrice = $this->getTotalShipping($order->id);
            } 
            $accounts = gmail_accounts::all(); 
            return view('report.duplicate',compact('orders','stores','dateRange','statusFilter','marketFilter','storeFilter','carrierArr','userFilter','users' ,'search','route','accounts','query'));

         
        }
        else
        redirect()->back();
    }
    
    

      public function searchfilter(Request $request)
    {
        
        $search = 1;
        $route = 'duplicate-record'; 
        if($request->has('poNumber'))
            $poNumber = $request->get('poNumber');
        
        //now show orders
        $orders = orders::select('poNumber');
        
         if(!empty($poNumber)&& $poNumber !=0)
        {            
            $orders = $orders->where('poNumber',$poNumber);
        }

        
       
        
        $orders  = $orders->orderBy('id', 'DESC')->groupBy('poNumber')
        ->havingRaw('COUNT(*) > 1')->where('poNumber','!=','')->paginate(100); 

        foreach($orders as $order)
        {            
            $order->shippingPrice = $this->getTotalShipping($order->id);
        }


        $stores = accounts::select(['id','store'])->get();

        $carriers = carriers::all(); 

        $users = User::select(['id','name'])->get(); 

        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }

        $accounts = gmail_accounts::all(); 

        return view('report.duplicate',compact('accounts','orders','stores','userFilter','carrierArr','users','poNumber','search','route'));
    }
    public function duplicateRecord()
    {
        $startDate = orders::min('date');
        $endDate = orders::max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;
        $storeFilter = 0;
        $marketFilter = 0;
        $statusFilter = 0;
        $userFilter = 0; 

        
        $orders = orders::select()->orderBy('id', 'DESC')->groupBy('poNumber')
        ->havingRaw('COUNT(*) > 1')->where('poNumber','!=','')->paginate(100);

       
        foreach($orders as $order)
        {            
            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        $stores = accounts::select(['id','store'])->get();         
        $users = User::select(['id','name'])->get(); 

        $carriers = carriers::all(); 
        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }

        $accounts = gmail_accounts::all(); 
        return view('report.duplicate',compact('accounts','orders','stores','dateRange','statusFilter','marketFilter','storeFilter','userFilter','carrierArr', 'users'));
    }
}
