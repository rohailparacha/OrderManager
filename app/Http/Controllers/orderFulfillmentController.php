<?php

namespace App\Http\Controllers;
use App\accounts;
use App\settings;
use App\carriers; 
use App\orders;
use App\cancelled_orders;
use Session;
use Redirect;
use Validator;
use GuzzleHttp\Client;
use Excel;
use File;
use Response;

use Illuminate\Http\Request;

class orderFulfillmentController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stores = accounts::all();
        $settings = settings::get()->first();
        return view('orderFulfillmentSettings',compact('stores','settings'));
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

    

    public function export(Request $request)
    {        
        
        $fileName = date("d-m-Y")."-".time()."-auto-fulfillment-orders.txt";
        $data ='Order Number; TBA Tracking Number'. PHP_EOL;
        File::put(public_path($fileName),$data); 
        
        if(auth()->user()->role==1)            
        {
            $orders = orders::select()->where('converted',true)->where('flag','8')
            ->where(function($test){
                $test->where('status','processing');
                $test->orWhere('status','shipped');
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
            
            $orders = orders::select()->where('converted',true)->where('flag','8')->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');
                $test->orWhere('status','shipped');
            }) 
            ->orderBy('status', 'DESC')->paginate(100);
            
        }
            
        else
            $orders = array();


        foreach($orders as $order)
        {           
            $data =$order->afpoNumber.'; '.$order->trackingNumber. PHP_EOL;
            File::append(public_path($fileName),$data);            
        }

        
       
        return Response::download(public_path($fileName));
    }

    public function orderCancelledExport(Request $request)
    {        
        
        $fileName = date("d-m-Y")."-".time()."-cancelled-orders.txt";
        $data ='Order Number; Status'. PHP_EOL;
        File::put(public_path($fileName),$data); 
        
        if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('flag','8')
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
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
            ->where('flag','8')
            ->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.orders.','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.id AS cancelledId'])
            ->paginate(100);
        }
            
        else
            $orders = array();


        foreach($orders as $order)
        {           
            $data =$order->afpoNumber.'; '.$order->orderStatus. PHP_EOL;
            File::append(public_path($fileName),$data);            
        }

        
       
        return Response::download(public_path($fileName));
    }

    public function autofulfillconversions()
    {       
        
        $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
        if(auth()->user()->role==1)            
        {
            $orders = orders::select()->where('converted',false)->where('flag','8')
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
            ->where('flag','8')->whereIn('storeName',$strArray)
            ->where('status','processing')
            ->where('trackingNumber','like','TBA%')
            ->orderBy('status', 'DESC')->paginate(100);

         
            
        }
            
        else
            $orders = array();
        

        return view('orders.orderFulfillmentConversions',compact('orders'));
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
            $endPoint = env('GAPI_TOKEN', '');

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

    public function autoFulfillProcess()
    {
        
        $client = new client(); 
       
        $endPoint = env('GAPI_TOKEN', '');
        
        try{
        $response = $client->request('GET', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'query' => ['function' => 'processOrders']           
        ]);
        
        }

        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            
            Session::flash('error_msg', $responseBodyAsString);
            return redirect()->route('autoFulfill');
        }
        
        $statusCode = $response->getStatusCode();
            
        
        if($statusCode!=200)
        {
            Session::flash('error_msg', __('Orders Processing Failed'));
            return redirect()->route('autoFulfill');
        }
                    
        $body = json_decode($response->getBody()->getContents());
        
        Session::flash('success_msg', $body->count. __(' Orders Processed'));
        return redirect()->route('autoFulfill');
    }

    public function autofulfillCancel()
    {        
        
        if(auth()->user()->role==1)            
        {
            $orders = cancelled_orders::leftJoin('orders','cancelled_orders.order_id','=','orders.id')
            ->where('flag','8')
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
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
            ->where('flag','8')
            ->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.orders.','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')
            ->select(['orders.*','cancelled_orders.status AS orderStatus','cancelled_orders.id AS cancelledId'])
            ->paginate(100);
        }
            
        else
            $orders = array();
        
        return view('orders.orderFulfillmentCancel',compact('orders'));
    }


    public function deleteCancelled($id)
    {
        
        $ord = cancelled_orders::where('id','=',$id)->get()->first();    
        $order = orders::where('id','=',$ord->order_id)->get()->first();

        if($order->poNumber != $order->afpoNumber)
        {
            cancelled_orders::where('id','=',$id)->delete();    
            return redirect()->route('autofulfillCancel')->withStatus(__('Order successfully deleted.'));
        }
        else
        {
            return redirect()->route('autofulfillCancel')->withStatus(__('Order could not be deleted.'));
        }

        
    }

    public function deleteConversion($id)
    {
        
        
        $order = orders::where('id','=',$id)->update(['trackingNumber'=>'','carrierName'=>'']);

        if($order)
        {             
            return redirect()->route('autofulfillconversions')->withStatus(__('Order successfully deleted.'));
        }
        else
        {
            return redirect()->route('autofulfillconversions')->withStatus(__('Order could not be deleted.'));
        }

        
    }

    public function storeSettings(Request $request)
    {
        $pricecheck = false;
        $storecheck = false;
        $qtyrangecheck = false;
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
            
        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);

        $minQty = trim(explode('-',$qtyRangeFilter)[0]);
        $maxQty = trim(explode('-',$qtyRangeFilter)[1]);

        $settings = settings::get()->first();

        if(empty($settings))
            settings::insert(['minAmount'=>$minAmount,'maxAmount'=>$maxAmount,
            'quantityRangeCheck'=>$qtyrangecheck,'minQty'=>$minQty,'maxQty'=>$maxQty,
            'amountCheck'=>$pricecheck,'stores'=>json_encode($stores),'storesCheck'=>$storecheck, 'discount'=>$discount, 'maxPrice'=>$maxPrice]);
        else
            settings::where('id',$settings->id)->update(['minAmount'=>$minAmount,'maxAmount'=>$maxAmount,
            'quantityRangeCheck'=>$qtyrangecheck,'minQty'=>$minQty,'maxQty'=>$maxQty,'amountCheck'=>$pricecheck,'stores'=>json_encode($stores),'storesCheck'=>$storecheck, 'discount'=>$discount, 'maxPrice'=>$maxPrice]);

        Session::flash('success_msg', __('Settings successfully updated'));
        return redirect()->route('orderFulfillmentSetting');

    }
}
