<?php

namespace App\Http\Controllers;
use App\accounts;
use App\settings;
use App\carriers; 
use App\orders;
use App\cancelled_orders;
use App\flags;
use App\states;
use App\products; 
use App\returns;
use App\gmail_accounts;
use App\ebay_products; 
use App\order_details;
use App\Exports\SaleFreaksExport;
use App\Exports\SaleFreaksOrdersExport;
use DB;
use App\Exports\SaleFreaksBceExport;
use App\Exports\SaleFreaksCancelExport;
use App\Imports\SaleFreaksImport;
use Session;
use Redirect;
use Validator;
use GuzzleHttp\Client;
use Excel;
use File;
use Response;

use Illuminate\Http\Request;

class saleFreaks2Controller extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stores = accounts::all();
        $settings = settings::where('name','salefreaks2')->get()->first();
        return view('saleFreaks2Settings',compact('stores','settings'));
    }

    public function getLowestPrice($id)
    {

        $details = order_details::where('order_id',$id)->get(); 
        $total = 0; 
        foreach($details as $detail)
        {
            $price  = products::select('lowestPrice')->where('asin',$detail->SKU)->get()->first(); 

            if(empty($price))
                {
                    $total = $total + 0; 
                    $price  = ebay_products::select('ebayPrice')->where('sku',$detail->SKU)->get()->first(); 
                    if(empty($price))
                    {
                        $total = $total + 0; 
                    }
                    else
                    $total = $total + ($price->ebayPrice * $detail->quantity);   
                }

            else
                $total = $total + ($price->lowestPrice * $detail->quantity);
        }

        return $total;

    }

    public function getCredits()
    {
        $endPoint = env('BCE_URL_ACC', '');
        
        $token = env('BCE_TOKEN', '');

        $client  = new client(); 
        
           
        
        try{
        
        $response = $client->request('GET', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'bearer '.$token],            
        ]); 
        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "Error";
                    
        $body = json_decode($response->getBody()->getContents());       
        return $body->RemainingBalance;
        }
        catch(\Exception $ex)
        {
            return "Not Available";
        }

        
        
        
    }

    public function saleFreaks2export(Request $request)
    {
        $storeFilter = $request->storeFilter;
        $marketFilter = $request->marketFilter;
        $stateFilter = $request->stateFilter;
        $amountFilter = $request->amountFilter; 
        $sourceFilter = $request->sourceFilter; 
        $daterange = $request->daterange; 
        $filename = date("d-m-Y")."-".time()."-autofulfill-orders.xlsx";
        return Excel::download(new SaleFreaksExport($storeFilter,$marketFilter,$stateFilter,$amountFilter,$sourceFilter, $daterange,'23'), $filename);
    }

    public function saleFreaks2OrderExport(Request $request)
    {        
        $filename = date("d-m-Y")."-".time()."-autofulfill-orders.csv";
        return Excel::download(new SaleFreaksOrdersExport('23'), $filename);
    }


    

    public function export(Request $request)
    {        
        
        $fileName = date("d-m-Y")."-".time()."-sales-freaks-1-auto-fulfillment-orders.csv";  
        return Excel::download(new SaleFreaksBceExport(), $fileName);      
    }

    public function orderCancelledExport(Request $request)
    {        
        
        $fileName = date("d-m-Y")."-".time()."-sales-freaks-1-cancelled-orders.csv";
        return Excel::download(new SaleFreaksCancelExport(), $fileName);             
    }

    public function autofulfillProcessed()
    {    
        if(auth()->user()->role==1)
            $orders = orders::select()->where('status','processing')->where('account_id','SaleFreaks2')->orderBy('date', 'ASC')->paginate(100);

        elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();

                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::select()->where('status','processing')->where('account_id','SaleFreaks2')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
            }
        
        else
            
            $orders = orders::select()->where('status','processing')->where('account_id','SaleFreaks2')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->paginate(100);
        
            foreach($orders as $order)
            {
               $order->shippingPrice = $this->getTotalShipping($order->id);
            }

        $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
        foreach ($orders as $order)
        {
            $bcecheck = orders::select()->where('converted',false)->where('account_id','SaleFreaks2')
            ->where('marketPlace','Walmart')
            ->where('id',$order->id)
            ->where('carrierName',$amzCarrier->id)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('status', 'DESC')->get()->first();

            if(!empty($bcecheck))
                $order->bce = 'BCE';

            $cancelcheck = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id','SaleFreaks2')
            ->where('cancelled_orders.order_id',$order->id)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.created_at AS ordercreatedate','cancelled_orders.id AS cancelledId'])
            ->get()->first(); 

            if(!empty($cancelcheck))
                $order->cancel = 'Cancel';

        }
            
        
        return view('SaleFreaks2.processed',compact('orders'));
        
    }

    public function autoFulfillFilter(Request $request)
    {
        
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
        //now show orders

        if($request->has('daterange'))
        $dateRange = $request->get('daterange');  

        $startDate = explode('-',$dateRange)[0];
            $from = date("Y-m-d", strtotime($startDate));  
        $endDate = explode('-',$dateRange)[1];
            $to = date("Y-m-d", strtotime($endDate)); 

        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
            
        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->leftJoin('ebay_products','order_details.SKU','=','ebay_products.sku')
        ->select(['orders.*',DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0)) as lowestPrice'),'products.asin','ebay_products.sku'])        
        ;
        
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

        if(!empty($startDate)&& !empty($endDate))
            {
                $orders = $orders->whereBetween('assignDate', [$from.' 00:00:00', $to.' 23:59:59']);
            }
        
        $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'>=',$minAmount);
        $orders = $orders->having(DB::raw('sum(IFNULL( products.lowestPrice * order_details.quantity, 0))'),'<=',$maxAmount);

        if(!empty($stateFilter)&& $stateFilter !='0')
        {           
            $orders = $orders->where('state',$stateFilter);
        }
                
        if(auth()->user()->role==1)
            $orders = $orders->where('flag','23')->where('status','unshipped')->orderBy('assignDate', 'ASC')->groupby('orders.id')->paginate(100);
        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = $orders->where('flag','23')->where('status','unshipped')->whereIn('storeName',$strArray)->orderBy('assignDate', 'ASC')->paginate(100);
        }
            
        else
            $orders = $orders->where('flag','23')->where('status','unshipped')->where('uid',auth()->user()->id)->orderBy('assignDate', 'ASC')->groupby('orders.id')->paginate(100);
        
        $orders = $orders->appends('storeFilter',$storeFilter)->appends('stateFilter',$stateFilter)->appends('marketFilter',$marketFilter)->appends('amountFilter',$amountFilter)->appends('sourceFilter',$sourceFilter);

        
        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();

     
        
        $maxPrice = ceil(orders::where('status','unshipped')->where('flag','23')->max('totalAmount'));
        foreach($orders as $order)
        {

            $order->shippingPrice = $this->getTotalShipping($order->id);
            

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
        $flags= flags::select()->whereNotIn('id',['16','23','8','9','10'])->get();  
        $route = 'saleFreaks2new';
        return view('SaleFreaks2.new',compact('flags','orders','stateFilter','marketFilter','sourceFilter','storeFilter','amountFilter','stores','states','maxAmount','minAmount','maxPrice','dateRange','route'));
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

    public function autoFulfill()
    {  
            if(auth()->user()->role==1)
            {
                $orders = orders::select()->where('status','unshipped')->orderBy('assignDate', 'ASC')->where('flag','23')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::select()->where('status','unshipped')->whereIn('storeName',$strArray)->where('flag','23')->orderBy('assignDate', 'ASC')->paginate(100);
                
            }
        
            else
            {
                $orders = orders::select()
                ->where('status','unshipped')
                ->where('flag','23')
                ->where('uid',auth()->user()->id)
                ->orderBy('assignDate', 'ASC')
                ->paginate(100);
            }

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag','23')->max('totalAmount'));

        $minAmount = 0; 
        $maxPrice = $maxAmount;

        foreach($orders as $order)
        {
            $order->lowestPrice = $this->getLowestPrice($order->id);
            $order->shippingPrice = $this->getTotalShipping($order->id);
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
        $flags= flags::select()->whereNotIn('id',['16','23','8','9','10'])->get();  
        $startDate = orders::where('status','unshipped')->where('flag','23')->min('assignDate');
        $endDate = orders::where('status','unshipped')->where('flag','23')->max('assignDate');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  
        $dateRange = $from .' - ' .$to;
        return view('SaleFreaks2.new',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','dateRange'));
    }


    public function autofulfillconversions()
    {       
        
        $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
        if(auth()->user()->role==1)            
        {
            $orders = orders::select()->where('converted',false)->where('account_id','SaleFreaks2')
            ->where('marketPlace','Walmart')
            ->where('carrierName',$amzCarrier->id)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('status', 'DESC')->paginate(100);
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
            ->where('account_id','SaleFreaks2')->whereIn('storeName',$strArray)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('status', 'DESC')->paginate(100);
                     
        }
            
        else
            $orders = array();
        

        return view('SaleFreaks2.bce',compact('orders'));
    }

    

    
    public function updateBCE(Request $request)
    {  
       
            $id = $request->id; 
            $bce  = $request->bce; 

            $input = [
                'bce' => $bce        
            ];
    
            $rules = [
                'bce'    => 'required' 
            ];
    
            $validator = Validator::make($input,$rules);
    
            if($validator->fails())
            {
               Session::flash('error_msg', __('Please check the errors and try again.'));
               return "failure";
            }
                        
            
            $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 
  
                   
            $order = orders::where('id',$id)->update(['carrierName'=>$bceCarrier->id, 'newTrackingNumber'=>$bce,'converted'=>true]);
            
            $ord = orders::where('id',$id)->get()->first(); 
            
            if(!empty($ord))
                $this->updateSheetTracking($bce, $ord->sellOrderId, 'Bluecare Express');
            else
                return "failure";

            if($order)
                return "success";
            else
                return "failure";
            
  
    }

    public function updateSheetTracking($tracking, $sellOrderId, $carrier)
    {
        
        try{
        
            $client = new client(); 
            $endPoint = env('SALEFREAKS2_TOKEN', '');

            $response = $client->request('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'query' => ['tracking' => $tracking,'sellOrderId' => $sellOrderId,'carrier' => $carrier,'function' => 'sheetUpdate']          
            ]);    
            
            $statusCode = $response->getStatusCode();
        
            $body = json_decode($response->getBody()->getContents());    
        }
        catch(\Exception $ex)
        {

        }
    }

     
    public function autoFulfillProcess(Request $request)
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
                $filename = $request->file->storeAs('.', \Str::random(40) . '.' . $file->getClientOriginalExtension(),['disk' => 'imports']);   
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('saleFreaks2new');
             }
            

        }
        else
        {
            
        }
        $import = new SaleFreaksImport('23');
        
        Excel::import($import, $filename,'imports');
        $collection = $import->data;

        Session::flash('success_msg', $collection.' Orders Processed Successfully');
        return redirect()->route('saleFreaks2new');

    }
    

    public function autofulfillCancel()
    {        
        
        if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id','SaleFreaks2')
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
            ->where('account_id','SaleFreaks2')
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
        
        return view('SaleFreaks2.cancel',compact('orders'));
    }


    public function deleteCancelled($id)
    {
        
        $ord = cancelled_orders::where('id','=',$id)->get()->first();    
        $order = orders::where('id','=',$ord->order_id)->get()->first();

        if($order->poNumber != $order->afpoNumber)
        {
            cancelled_orders::where('id','=',$id)->delete();    
            return redirect()->route('saleFreaks2cancel')->withStatus(__('Order successfully deleted.'));
        }
        else
        {
            return redirect()->route('saleFreaks2cancel')->withStatus(__('Order could not be deleted.'));
        }

        
    }

    public function deleteConversion($id)
    {
        
        
        $order = orders::where('id','=',$id)->update(['trackingNumber'=>'','carrierName'=>'']);

        if($order)
        {             
            return redirect()->route('saleFreaks2bce')->withStatus(__('Order successfully deleted.'));
        }
        else
        {
            return redirect()->route('saleFreaks2bce')->withStatus(__('Order could not be deleted.'));
        }

        
    }

    public function storeSettings(Request $request)
    {
        $pricecheck = false;
        $storecheck = false;
        $qtyrangecheck = false;
        $dailyamtcheck = false;
        $dailyordercheck = false;
        $showinlist = false;
        $priority = 0; 
        $maxDailyOrder = 0; 
        $maxDailyAmount =0;
        $discount = 0; 
        $maxPrice =0;

        $stores=array();
        
            $input = [
                'discount' => $request->discount,
                'maxPrice' => $request->maxPrice            
            ];
    
            $rules = [
                'discount'    => 'required|numeric',
                'maxPrice' => 'required|numeric'  
            ];
        

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator);
        }
        
        if(!empty($request->pricecheck))
            $pricecheck = true;

        if(!empty($request->showinlist))
            $showinlist = true;  
        
        if(!empty($request->dailyamtcheck))
            $dailyamtcheck = true;
        
        if(!empty($request->dailyordercheck))
            $dailyordercheck = true;
        
        if(!empty($request->storecheck))
            $storecheck = true;
            
        if(!empty($request->qtyRangeCheck))
            $qtyrangecheck = true;
        
        if(!empty($request->amountFilter))
            $amountFilter = $request->amountFilter;

        if(!empty($request->qtyRangeFilter))
            $qtyRangeFilter = $request->qtyRangeFilter;
        
        if(!empty($request->stores) && count($request->stores)>0)
            $stores = $request->stores;

        if(!empty($request->discount))
            $discount = $request->discount;

        if(!empty($request->maxPrice))
            $maxPrice = $request->maxPrice;

        if(!empty($request->maxDailyOrder))
            $maxDailyOrder = $request->maxDailyOrder;

        if(!empty($request->maxDailyAmount))
            $maxDailyAmount = $request->maxDailyAmount;
            
        if(!empty($request->priority))
            $priority = $request->priority;

        $switch = $request->switch; 

        if($switch=='Disable')
            $enabled = 1;
        else
            $enabled = 0;
        
        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);

        $minQty = trim(explode('-',$qtyRangeFilter)[0]);
        $maxQty = trim(explode('-',$qtyRangeFilter)[1]);

        $settings = settings::where('name','saleFreaks2')->get()->first();

        if(empty($settings))
            settings::insert(['minAmount'=>$minAmount,'maxAmount'=>$maxAmount,
            'quantityRangeCheck'=>$qtyrangecheck,'minQty'=>$minQty,'maxQty'=>$maxQty,
            'amountCheck'=>$pricecheck,'stores'=>json_encode($stores),'storesCheck'=>$storecheck, 'discount'=>$discount, 'maxPrice'=>$maxPrice ,'maxDailyOrder'=>$maxDailyOrder, 'maxDailyAmount'=>$maxDailyAmount,'dailyAmountCheck'=>$dailyamtcheck, 'dailyOrderCheck'=>$dailyordercheck,'name'=>'saleFreaks2','priority'=>$priority,'enabled'=>$enabled,'listCheck'=>$showinlist]);
        else
            settings::where('name','saleFreaks2')->where('id',$settings->id)->update(['minAmount'=>$minAmount,'maxAmount'=>$maxAmount,
            'quantityRangeCheck'=>$qtyrangecheck,'minQty'=>$minQty,'maxQty'=>$maxQty,'amountCheck'=>$pricecheck,'stores'=>json_encode($stores),'storesCheck'=>$storecheck, 'discount'=>$discount, 'maxPrice'=>$maxPrice,'maxDailyOrder'=>$maxDailyOrder, 'maxDailyAmount'=>$maxDailyAmount,'dailyAmountCheck'=>$dailyamtcheck, 'dailyOrderCheck'=>$dailyordercheck,'name'=>'saleFreaks2','priority'=>$priority,'enabled'=>$enabled,'listCheck'=>$showinlist]);

        Session::flash('success_msg', __('Settings successfully updated'));
        return redirect()->route('saleFreaks2Setting');

    }

    public function search(Request $request)
    {
        $query = $request->searchQuery;
        $route = $request->route; 
        
        $search = 1;

        if($route == 'saleFreaks2new')
        {            
             
            if(auth()->user()->role==1)
            {            

                $orders = orders::select()->where('flag','23')->where('status','unshipped')                
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('assignDate', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                
                $orders = orders::select()->where('flag','23')->where('status','unshipped')->whereIn('storeName',$strArray)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('assignDate', 'ASC')->paginate(100);
                
            }

            else
            {
            $orders = orders::select()->where('flag','23')->where('status','unshipped')->where('uid',auth()->user()->id)
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            })                
            ->orderBy('assignDate', 'ASC')->paginate(100);
            }





                $stores = accounts::select(['id','store'])->get();
                $states = states::select()->distinct()->get();
                
                $maxAmount = ceil(orders::where('status','unshipped')->where('flag','23')->max('totalAmount'));
                $minAmount = 0; 
                $maxPrice = $maxAmount;

                foreach($orders as $order)
                {
                    $order->lowestPrice = $this->getLowestPrice($order->id);
                    $order->shippingPrice = $this->getTotalShipping($order->id);
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
                $flags= flags::select()->whereNotIn('id',['16','23','8','9','10'])->get();  
                $startDate = orders::where('status','unshipped')->where('flag','23')->min('assignDate');
                $endDate = orders::where('status','unshipped')->where('flag','23')->max('assignDate');

                $from = date("m/d/Y", strtotime($startDate));  
                $to = date("m/d/Y", strtotime($endDate));  
                $dateRange = $from .' - ' .$to;
                return view('SaleFreaks2.new',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','search','route','dateRange'));
            
        }

        else if ($route=='saleFreaks2cancel')
        {
            if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('account_id','SaleFreaks2')
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            })
            ->where('afpoNumber', 'LIKE', '%'.$query.'%')            
            ->orderBy('orders.status', 'DESC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.id AS cancelledId'])
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
            ->where('account_id','SaleFreaks2')
            ->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.orders.','shipped');
            }) 
            ->where('afpoNumber', 'LIKE', '%'.$query.'%')
            ->orderBy('orders.status', 'DESC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.id AS cancelledId'])
            ->paginate(100);
        }
            
            else
                $orders = array();
            
            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.cancel',compact('orders','search','route'));
        }        

        else if ($route=='saleFreaks2bce')
        {

            $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
        if(auth()->user()->role==1)            
        {
            $orders = orders::select()->where('converted',false)->where('account_id','SaleFreaks2')
            ->where('marketPlace','Walmart')
            ->where('carrierName',$amzCarrier->id)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->where(function($test) use ($query){
                $test->where('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
            }) 
            ->orderBy('status', 'DESC')->paginate(100);
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
            ->where('account_id','SaleFreaks2')->whereIn('storeName',$strArray)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->where(function($test) use ($query){
                $test->where('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
            }) 
            ->orderBy('status', 'DESC')->paginate(100);

         
            
        }
            
        else
            $orders = array();

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.bce',compact('orders','search','route'));
        }  

        else if ($route=='saleFreaks2processed')
        {
            if(auth()->user()->role==1)
                $orders = orders::select()->where('status','processing')
                ->where('account_id','SaleFreaks2')
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })  
                ->orderBy('date', 'ASC')->paginate(100);

            elseif(auth()->user()->role==2)
                {
                    $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                    $strArray  = array();

                    foreach($stores as $str)
                    {
                        $strArray[]= $str->store;
                    }
                    
                    $orders = orders::select()->where('status','processing')
                    ->where(function($test) use ($query){
                        $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                        $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                        $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })  
                    ->where('account_id','SaleFreaks2')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
                }
            
            else
                
                $orders = orders::select()->where('status','processing')
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })  
                ->where('account_id','SaleFreaks2')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->paginate(100);
            
                foreach($orders as $order)
                {
                $order->shippingPrice = $this->getTotalShipping($order->id);
                }

                $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
                foreach ($orders as $order)
                {
                    $bcecheck = orders::select()->where('converted',false)->where('account_id','SaleFreaks2')
                    ->where('marketPlace','Walmart')
                    ->where('id',$order->id)
                    ->where('carrierName',$amzCarrier->id)
                    ->where('status','processing')
                    ->where('trackingNumber','like','TBA%')
                    ->orderBy('status', 'DESC')->get()->first();

                    if(!empty($bcecheck))
                        $order->bce = 'BCE';

                    $cancelcheck = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
                    ->where('account_id','SaleFreaks2')
                    ->where('cancelled_orders.order_id',$order->id)
                    ->where(function($test){
                        $test->where('orders.status','processing');
                        $test->orWhere('orders.status','shipped');
                    }) 
                    ->orderBy('orders.status', 'DESC')
                    ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.created_at AS ordercreatedate','cancelled_orders.id AS cancelledId'])
                    ->get()->first(); 

                    if(!empty($cancelcheck))
                        $order->cancel = 'Cancel';

                }
                    
            
            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.processed',compact('orders','search','route'));
        }  
        elseif($route=='saleFreaks2return')
        {
            
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->where('account_id','SaleFreaks2')
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->select(['orders.*','returns.*'])
                ->orderBy('created_at','desc')
                ->whereNull('returns.status')                
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
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])      
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })           
                ->whereIn('orders.storeName',$strArray)      
                ->whereNull('returns.status')     
                ->where('account_id','SaleFreaks2')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('orders.uid',auth()->user()->id)  
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('account_id','SaleFreaks2')           
                ->whereNull('returns.status')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.return',compact('returns','accounts','stores','dateRange','search','route'));
            
            
        }
        elseif($route=='saleFreaks2refund')
        {
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','returned') 
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })    
                    ->where('account_id','SaleFreaks2') 
                ->orderBy('returnDate','desc')
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
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])                
                ->whereIn('orders.storeName',$strArray)
                ->where('account_id','SaleFreaks2')    
                ->where('returns.status','returned') 
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })  
                ->orderBy('returnDate','desc')       
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('orders.uid',auth()->user()->id)  
                ->where('returns.status','returned')  
                ->where('account_id','SaleFreaks2')
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->orderBy('returnDate','desc')                    
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.refund',compact('returns','accounts','stores','dateRange','search','route'));
        }
        elseif($route=='saleFreaks2completed')
        {
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','refunded') 
                ->where('account_id','SaleFreaks2')      
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->orderBy('refundDate','desc')
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
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])         
                ->where('returns.status','refunded')  
                ->where('account_id','SaleFreaks2')             
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->whereIn('orders.storeName',$strArray)  
                ->orderBy('refundDate','desc')           
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','refunded')    
                ->where('account_id','SaleFreaks2')  
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('orders.uid',auth()->user()->id)  
                ->orderBy('refundDate','desc')              
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('SaleFreaks2.complete',compact('returns','accounts','stores','dateRange','search','route'));
        }

        else
        redirect()->back();
    }

}
