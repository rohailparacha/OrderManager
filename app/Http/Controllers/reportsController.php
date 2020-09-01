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


class reportsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dailyReport()
    {
        $settings = informed_settings::all();
        $labels= array(); 
        $datasets = array(); 
        foreach($settings as $setting)
        {   
            $labels[]= $setting->minAmount."-".$setting->maxAmount;
        }

        return view('report.dailyReport','settings');
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
}
