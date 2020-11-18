<?php

namespace App\Http\Controllers;
use App\orders;
use App\order_details;
use App\blacklist;
use App\settings;
use App\walmart_products;
use App\flags;
use App\reasons;
use DB;
use Carbon\Carbon;
use App\amazon_settings;
use App\User;
use App\temp_trackings;
use App\returns;
use App\order_settings;
use App\states;
use App\cancelled_orders;
use App\accounts;
use App\gmail_accounts;
use App\carriers;
use App\strategies;
use App\products;
use App\ebay_products;
use App\ebay_trackings;
use App\ebay_strategies;
use App\transactions; 
use App\bank_accounts; 
use App\accounting_categories;
use App\categories;
use App\conversions;
use App\Exports\OrdersExport;
use App\Exports\UPSExport;
use App\Exports\AutoFulfillExport;
use App\Imports\AttributeImport;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Hash;
use Auth; 
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;
use Excel;
use Response;
use App\Http\Controllers\ProductReportController;

class newOrdersController extends Controller
{

    
     /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getCount($id)
    {
        $details = order_details::where('order_id',$id)->selectRaw("*, SUM(quantity) as total_quantity")->groupBy('SKU')->get();
        return count($details);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function newOrdersMultiItems()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag','0')
                ->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1')
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                
                ->where('flag','0')
                ->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1')
                ->groupBy('orders.id')                             
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')    
                ->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1')   
                ->groupBy('orders.id')                            
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);
            
            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get(); 
        
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.multi',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }
    

    
    public function newOrdersChecked()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag','0')                
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                
                ->where('flag','0')                
                ->groupBy('orders.id')                             
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')                    
                ->groupBy('orders.id')                            
                ->where('uid',auth()->user()->id);
                
            }

        $price1 = order_settings::get()->first()->price1; 
        $price2 = order_settings::get()->first()->price2; 
        
        $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'<=','1')
                    ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'!=','0')                    
                   ->where(function($test){
                        $test->whereNull('products.category');
                        $test->orWhere('products.category','!=','Movie');
                        
                    })        
                                   
                    ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)                    
                    ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'>=','2');       

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get(); 
        
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.checked',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }

    public function newOrdersZero()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag','0')
                ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0')
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')
                ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0')
                ->groupBy('orders.id')                            
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')    
                ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0')
                ->groupBy('orders.id')                            
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.zero',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }

   

    

    public function checkOrder($id)
    {
        orders::where('id',$id)->update(['isChecked'=>true]);    
        $details = order_details::where('order_id',$id)->get();
        foreach($details as $detail)
        {
            products::where('asin',$detail->SKU)->update(['checked'=>true]);
        }   

        $sellOrderId = orders::where('id',$id)->get()->first()->sellOrderId; 
        return redirect()->route('newOrders')->withStatus(__('Order '.$sellOrderId.' Is Checked Successfully.'));
    }

    public function flagOrder(Request $request)
    {
        $id = $request->idOrder;
        
        $flag = $request->flag;
        
        orders::where('id',$id)->update(['isChecked'=>true, 'flag'=>$flag]);        
        
        $details = order_details::where('order_id',$id)->get();
        
        foreach($details as $detail)
        {
            products::where('asin',$detail->SKU)->update(['checked'=>true]);
        }   

        $sellOrderId = orders::where('id',$id)->get()->first()->sellOrderId; 

        return redirect()->route('newOrders')->withStatus(__('Order '.$sellOrderId.' Is Flagged Successfully.'));
    }

    public function newOrdersMinus()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag','0')
                ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2')
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')
                ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2')
                ->groupBy('orders.id')                            
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')    
                ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2')
                ->groupBy('orders.id')                            
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);
            
            
            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.minus',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }

    public function newOrdersMovie()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('products.category','Movie')
                ->where('flag','0')                
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')        
                ->where('products.category','Movie')        
                ->groupBy('orders.id')                               
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')       
                ->where('products.category','Movie')             
                ->groupBy('orders.id')                        
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);
            
            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.movie',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }

    public function newOrdersFood()
    {              
        
            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('products.category','Food')
                ->where('flag','0')                
                ->groupBy('orders.id');
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')        
                ->where('products.category','Food')        
                ->groupBy('orders.id')                               
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')       
                ->where('products.category','Food')             
                ->groupBy('orders.id')                        
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.food',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }

    public function settings()
    {
        $settings = order_settings::get()->first(); 
        return view('orders.orderSettings',compact('settings'));
    }

    public function storeSettings(Request $request)
    {
        $price1 = 0; 
        $price2 =0;

        $stores=array();
        
            $input = [
                'price1' => $request->price1,
                'price2' => $request->price2            
            ];
    
            $rules = [
                'price1'    => 'required|numeric',
                'price2' => 'required|numeric'  
            ];
        

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator);
        }
        
        $settings = order_settings::get()->first(); 

        if(empty($settings))
            order_settings::insert(['price1'=>$request->price1,'price2'=>$request->price2]);
        else
            order_settings::where('id',$settings->id)->update(['price1'=>$request->price1,'price2'=>$request->price2]);

        Session::flash('success_msg', __('Settings successfully updated'));
        return redirect()->route('pricingSettings');

    }

    public function newOrdersPrice1()
    {              
        $price1 = order_settings::get()->first()->price1; 

            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')                                                
                ->where('flag','0')                
                ->groupBy('orders.id')
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1)
                ;
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')                        
                ->groupBy('orders.id')            
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1)
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')       
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1)
                ->groupBy('orders.id')                        
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.price1',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }
    public function newOrdersPrice2()
    {              
        $price1 = order_settings::get()->first()->price1; 
        $price2 = order_settings::get()->first()->price2; 

            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')                                                
                ->where('flag','0')                
                ->groupBy('orders.id')
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
                ;
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')                        
                ->groupBy('orders.id')            
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')       
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
                ->groupBy('orders.id')                        
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();  
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);

        return view('orders.price2',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
    }
    public function newOrdersExpensive()
    {                      
        $price2 = order_settings::get()->first()->price2; 

            if(auth()->user()->role==1)
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')                                                
                ->where('flag','0')                
                ->groupBy('orders.id')
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2)
                ;
            }                        

            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')                        
                ->groupBy('orders.id')            
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2)
                ->whereIn('storeName',$strArray);
                
            }
        
            else
            {
                $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
                ->leftJoin('products','order_details.SKU','=','products.asin')
                ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),DB::raw("SUM(order_details.quantity) as total_quantity"),'products.asin'])
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])
                ->where('flag','0')       
                ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2)
                ->groupBy('orders.id')                        
                ->where('uid',auth()->user()->id);
                
            }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
            
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get();  
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.expensive',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','accounts','statecheck','disabledStates'));
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

    public function assignMovie(Request $request)
    {
        $input = [
            'file' => $request->file           
        ];

        $rules = [
            'file'    => 'required'  
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            return redirect()->route('products');
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['csv','xls','xlsx'];
        
            $file = $request->file('file');
          
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('imports'); 
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('products');
             }
            

        }
        else
        {
            
        }
        $import = new AttributeImport('Movie');
        
        Excel::import($import, $filename);              
                
        Session::flash('success_msg', 'Attribute imported successfully');
        return redirect()->route('newOrdersMovie');

    }

    public function assignFood(Request $request)
    {
        $input = [
            'file' => $request->file           
        ];

        $rules = [
            'file'    => 'required'  
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            return redirect()->route('products');
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['csv','xls','xlsx'];
        
            $file = $request->file('file');
          
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('imports'); 
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('products');
             }
            

        }
        else
        {
            
        }
        $import = new AttributeImport('Food');
        
        Excel::import($import, $filename);              
                
        Session::flash('success_msg', 'Attribute imported successfully');
        return redirect()->route('newOrdersFood');

    }

    public function getTemplate()
    {        
        $file="./templates/assign_attribute.csv";
        return Response::download($file);
    }
   
    public function filter(Request $request)
    {        
        $price1 = order_settings::get()->first()->price1; 
        $price2 = order_settings::get()->first()->price2; 

        $storeFilter = '';
        $marketFilter = '';
        $stateFilter = '';
        $amountFilter = '';
        $sourceFilter = '';

        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');
        if($request->has('marketFilter'))
            $marketFilter = $request->get('marketFilter');  
        if($request->has('stateFilter'))
            $stateFilter = $request->get('stateFilter');
        if($request->has('amountFilter'))
            $amountFilter = $request->get('amountFilter');
        if($request->has('sourceFilter'))
            $sourceFilter = $request->get('sourceFilter');
        
        $page = $request->get('page'); 

        if(!empty($amountFilter))
        {
            $minAmount = trim(explode('-',$amountFilter)[0]);
            $maxAmount = trim(explode('-',$amountFilter)[1]);            
        }
        else
        {
            $maxAmount = ceil(orders::where('status','unshipped')->max('totalAmount'));
            $minAmount = 0; 
        }

        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->leftJoin('ebay_products','order_details.SKU','=','ebay_products.sku')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) + IFNULL( ebay_products.ebayPrice * order_details.quantity, 0) as lowestPrice'),'products.asin','ebay_products.sku']);
        
        if($page =='multi')
            {
                $route = 'newOrdersMultiItems';
                $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1');
            }
        elseif($page=='price1')
        {
            $route = 'newOrdersPrice1';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1);
        }
        elseif($page=='price2')
        {
            $route = 'newOrdersPrice2';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2);
        }
        elseif($page=='expensive')
        {
            $route = 'newOrdersExpensive';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2);
        }
        elseif($page=='zero')
        {
            $route = 'newOrdersZero';
            $orders = $orders->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0');
        }
        elseif($page=='food')
        {
            $route = 'newOrdersFood';
            $orders = $orders->where('products.category','Food');
        }
        elseif($page=='movie')
        {
            $route = 'newOrdersMovie';
            $orders = $orders->where('products.category','Movie');
        }
        elseif($page=='minus')
        {
            $route = 'newOrdersMinus';
            
            $orders = $orders->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2');
        
        }
        elseif($page=='checked')
        {
            $route = 'newOrdersChecked';

            $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'<=','1')
            ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'!=','0')
           ->where(function($test){
                        $test->whereNull('products.category');
                        $test->orWhere('products.category','!=','Movie');
                        
                    })                         
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
            ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'>=','2')            
            ;
            
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
                $orders = $orders->where('marketplace','eBay');
            elseif($marketFilter==3)
                $orders = $orders->where('marketplace','Walmart');
                      
        }

        if(!empty($sourceFilter)&& $sourceFilter !=0)
        {                            
            if($sourceFilter==1)
                $orders = $orders->whereNotNull('products.asin');
            elseif($sourceFilter==2)
                $orders = $orders->whereNotNull('ebay_products.sku');                                
        }

        if(!empty($amountFilter))
        {
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>=',$minAmount);
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$maxAmount);

        }
        
        if(!empty($stateFilter)&& $stateFilter !='0')
        {           
            $orders = $orders->where('state',$stateFilter);
        }
                
        if(auth()->user()->role==1)
            $orders = $orders->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')
            ->groupBy('orders.id')            
            ->orderBy('dueShip', 'ASC')->where('isChecked',true)->groupby('orders.id')->paginate(100);
        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = $orders->where('isChecked',true)->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')
                ->groupBy('orders.id')                
                
                ->whereIn('storeName',$strArray)->orderBy('dueShip', 'ASC')->paginate(100);
        }
            
        else
            $orders = $orders->where('isChecked',true)->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','0')
            
            ->groupBy('orders.id')
            
            ->where('uid',auth()->user()->id)->orderBy('dueShip', 'ASC')->groupby('orders.id')->paginate(100);
        
        if(!empty($storeFilter))
            $orders = $orders->appends('storeFilter',$storeFilter);

        if(!empty($stateFilter))
            $orders = $orders->appends('stateFilter',$stateFilter);  
            
        if(!empty($marketFilter))
            $orders = $orders->appends('marketFilter',$marketFilter);

        if(!empty($amountFilter))
            $orders = $orders->appends('amountFilter',$amountFilter);
        
        if(!empty($sourceFilter))
            $orders = $orders->appends('sourceFilter',$sourceFilter);  

        if(!empty($page))
            $orders = $orders->appends('page',$page);
        
        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();

     
        
        $maxPrice = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')->where('flag','!=','0')->max('totalAmount'));
        foreach($orders as $order)
        {
            
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);

            

            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }     
        
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get(); 

        
        $accounts = settings::where('listCheck',true)->get();
        
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);

        return view("orders.$page",compact('flags','orders','stateFilter','marketFilter','sourceFilter','storeFilter','amountFilter','stores','states','maxAmount','minAmount','maxPrice','accounts','route','statecheck','disabledStates'));
    }

    public function search(Request $request)
    {        
        $price1 = order_settings::get()->first()->price1; 
        $price2 = order_settings::get()->first()->price2; 
        
        $query = $request->searchQuery;
        $route = $request->route; 
        
        $search = 1;

        $ord = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),'products.asin'])->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '16')->whereNotIn('flag', ['17','22','23','24','25','26'])->where('flag', '!=' , '10')            
        ->groupBy('orders.id');

        if(auth()->user()->role==1)
        {            

            $orders = $ord                              
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            })   ;             
           
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
                            
            
            $orders = $ord->whereIn('storeName',$strArray)
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            });            
            
        }

        else
        {
            $orders = $ord->where('uid',auth()->user()->id)
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            });  
        }

        if($route =='newOrdersMultiItems')
        {
            $page = 'multi';
            $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'>','1');
        }
        elseif($route=='newOrdersPrice1')
        {
            $page = 'price1';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',0)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price1);
        }
        elseif($route=='newOrdersPrice2')
        {
            $page = 'price2';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price1)
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2);
        }
        elseif($route=='newOrdersExpensive')
        {
            $page = 'expensive';
            $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>',$price2);
        }
        elseif($route=='newOrdersZero')
        {
            $page = 'zero';
            $orders = $orders->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'0');
        }
        elseif($route=='newOrdersFood')
        {
            $page = 'food';
            $orders = $orders->where('products.category','Food');
        }
        elseif($route=='newOrdersMovie')
        {
            $page = 'movie';
            $orders = $orders->where('products.category','Movie');
        }

        elseif($route=='newOrdersChecked')
        {
            $page = 'checked';

            $orders = $orders->having(DB::raw("COUNT(DISTINCT order_details.SKU)"),'<=','1')
            ->having(DB::raw("sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'!=','0')
           ->where(function($test){
                        $test->whereNull('products.category');
                        $test->orWhere('products.category','!=','Movie');
                        
                    })                         
            ->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$price2)
            ->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'>=','2');       

        }
        elseif($page=='newOrdersMinus')
        {
            $page = 'minus';

            $orders = $orders->having(DB::raw("((orders.totalAmount + sum(IFNULL( order_details.shippingPrice, 0))) * 0.85) - sum(IFNULL( products.lowestPrice * order_details.quantity, 0))"),'<','2');        

        }

        $orders = $orders->where('isChecked',true)->orderBy('dueShip', 'ASC')->paginate(100);

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->max('totalAmount'));
        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->shippingPrice = $this->getTotalShipping($order->id);
            $order->itemcount = $this->getCount($order->id);
            
            $sources = array();
                $order_details = order_details::where('order_id',$order->id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'N/A'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $order->source = $b[0];
                    else
                        $order->source = 'Mix';
                }
        }
        $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
        $flags = flags::select()->whereNotIn('id',['16','17','8','9','10','22','23','24','25','26'])->get(); 
        $accounts = settings::where('listCheck',true)->get();
        $settings = settings::where('name','jonathan')->get()->first();    
        $statecheck = $settings->statesCheck;
        $disabledStates = json_decode($settings->states);
        return view('orders.'.$page,compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','search','route','accounts','statecheck','disabledStates'));
            
        
    }

    public function orderTrackingLinks()
    {
        $orders = temp_trackings::join('orders','orders.sellOrderId','temp_trackings.sellOrderId')->select(['orders.*', 'temp_trackings.trackingLink As tlLink'])->where('temp_trackings.status','pending')->paginate(100);
        $accounts = gmail_accounts::all();
        return view('orders.orderTrackingLinks',compact('accounts','orders'));
    }

    
}
