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
use App\User;
use App\returns;
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
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Hash;
use Auth; 
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;
use Excel;
use App\Http\Controllers\ProductReportController;

class orderController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */

    function startsWith ($string, $startString) 
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 

    public function autoship(Request $request)
    {        
        $client = new client(); 
        $pageNum =  $request->page; 
        
        $perPage =  $request->count;
        
        $offset = ($pageNum - 1) * $perPage;

        $shippedCounter = 0; 
        
        if(auth()->user()->role==1)
            $orders = orders::select()->where('status','processing')->offset($offset)->limit($perPage)->get();

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }

            $orders = orders::select()->where('status','processing')->whereIn('storeName',$strArray)->offset($offset)->limit($perPage)->get();
        }
        else
            $orders = orders::select()->where('status','processing')->where('uid',auth()->user()->id)->offset($offset)->limit($perPage)->get();
        
        
        
        
            $field = 'carrierRelatedInfo-container';
        
            $trackField = 'carrierRelatedInfo-trackingId-text';
    
            $carrierField = 'a-spacing-small widgetHeader';

            $carrierField2= 'a-spacing-small carrierRelatedInfo-mfn-carrierNameTitle';

        foreach($orders as $order)
        {                       
            $found = false;
            $trackingId = '';
            $carrier = '';
            if($order->account_id=='Samuel' || $order->account_id=='Jonathan')
            {               
                try{        
                    $client = new client(); 
                    if($order->account_id=='Samuel')
                        $endPoint = env('SAMUEL_TOKEN', '');
                    else
                        $endPoint = env('JONATHAN_TOKEN', '');

                    $response = $client->request('GET', $endPoint,
                    [
                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                        'query' => ['sellOrderId' => $order->sellOrderId,'function' => 'fetchShipping']          
                    ]);    
                    
                    $statusCode = $response->getStatusCode();
                
                    $body = json_decode($response->getBody()->getContents());    
                    
                    $carrier = $body->carrier; 
                    $trackingId = $body->trackingId;
                    
                    if(empty($carrier) || empty($trackingId))
                    {
                        
                        continue;
                    }
                    else
                    {
                        
                        if(strtolower($carrier)=='bce' || strtolower($carrier)=='bluecare express')
                        {
                            
                            $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 

                            $order = orders::where('id',$order->id)->update(['carrierName'=>$bceCarrier->id,'trackingNumber'=>$trackingId,  'newTrackingNumber'=>$trackingId, 'of_bce_created_at'=>Carbon::now(), 'isBCE'=>true ]);

                            $this->sendOrderToSheet($order->id);
                        }
                        else
                        {
                            $carrierId = carriers::where('name',$carrier)->get()->first(); 
                                
                            if(empty($carrierId))
                            {
                                $carrierId = carriers::where('alias','like','%'.$carrier.'%')->get()->first(); 
                            }

                            if(empty($carrierId))
                                continue;

                            $this->shipOrder($order->id, $trackingId, $carrierId->name, 'new');
                            
                            $order = orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId, 'status'=>'shipped']);
                            
                            $shippedCounter++;
                        }
                        
                    }
                }
                catch(\Exception $ex)
                {
                 
                  
                }
                continue;
            }
            if(empty(trim($order->poNumber)))
                continue; 
            
            $poNumbers = explode(',',$order->poNumber);

            if(count($poNumbers)>0)
            {
                foreach($poNumbers as $number)
                {
                    if($found)
                        break;
                    $ebayOrder = ebay_trackings::where('orderNumber',trim($number))->get()->first();
                    if($ebayOrder)
                    {
                        $carrier = $ebayOrder->carrierName;
                        $trackingId = $ebayOrder->trackingNumber;
                        
                        if($carrier =='Bluecare Express')
                        {
                            $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 
                            try{
                                       
                                $order = orders::where('id',$order->id)->update(['carrierName'=>$bceCarrier->id, 'trackingNumber'=>$trackingId, 'newTrackingNumber'=>$trackingId, 'converted'=>true]);   
                                $shippedCounter++;                                    
                                
                            }
                                catch(\Exception $ex)
                                {
                
                                }
                        }
                        else
                            {
                            $carrierId = carriers::where('name',$carrier)->get()->first(); 
                                
                            if(empty($carrierId))
                            {
                                $carrierId = carriers::where('alias','like','%'.$carrier.'%')->get()->first(); 
                            }

                            if(empty($carrierId))
                                continue;
                            $this->shipOrder($order->id, $trackingId, $carrierId->name, 'new'); 
                            
                            try{
                                $order = orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId, 'status'=>'shipped']);                      
                                $shippedCounter++;                   
                                $found = true;
                            }
                            catch(\Exception $ex)
                            {
                            
                            }
                        }
                    }
                    else
                    {
                        
                        try{
                            $baseUrl = "https://www.amazon.com/progress-tracker/package/ref=ppx_yo_dt_b_track_package?_encoding=UTF8";
                            
                             
                            $order_details = order_details::where('order_id',$order->id)->get();
                            
                            if(count($order_details)>1)
                            {
                                if($order->account_id=="Cindy")
                                {
                                   
                                 
                                 $response = $client->request('GET', $baseUrl.'&itemId='.$order->itemId.'&orderId='.trim($number),
                                 [   
                     
                                 ]);    
                                 
                                 
                                 $statusCode = $response->getStatusCode();
                                 
                                 $html = $response->getBody()->getContents();   
                                }
                                else
                                {                                    
                                    $response = $client->request('GET', $baseUrl.'&itemId=klpjsskrrrpoqn&orderId='.trim($number).'&shipmentId='.$this->getShipment($order->poNumber),
                                    [   
                        
                                    ]);    
                                    
                                    
                                    $statusCode = $response->getStatusCode();
                                    
                                    $html = $response->getBody()->getContents();   
                                }
                                
                            }
                            else                                
                                {
                                    $response = $client->request('GET', $baseUrl.'&itemId=klpjsskrrrpoqn&orderId='.trim($number),
                                    [   
                        
                                    ]);    
                                    
                                    
                                    $statusCode = $response->getStatusCode();
                                    
                                    $html = $response->getBody()->getContents();   
                                }
                                
                           
                            
                            $html = str_replace('&','&amp;',$html);
                            
                            $doc = new \DOMDocument();
                            
                            $internalErrors = libxml_use_internal_errors(true);
                           
                            $doc->loadHTML($html);
                             
                            try{
                                $elem = $doc->getElementById('primaryStatus');
                                $stat =  $elem->nodeValue;                             
                                if(trim($stat)=='Delayed, not yet shipped' && $order->account_id=='Cindy')
                                {
                                    $insert = cancelled_orders::updateOrCreate(
                                        ['order_id'=>$order->id,],    
                                        ['status'=>'delayed']
                                    );
                                    
                                }
                                                                    
                                if(trim($stat)=='Order cancelled' && $order->account_id=='Cindy')
                                {
                                    $insert = cancelled_orders::updateOrCreate(
                                        ['order_id'=>$order->id,],    
                                        ['status'=>'cancelled']
                                    );
                                   
                                }
                            }
                            catch(\Exception $ex)
                            {
                               
                            }
                            
                            $elements = $doc->getElementById($field);
                            
                            
                            if(empty($elements))
                                continue;
                                
                             
                            libxml_use_internal_errors($internalErrors);
                            
                            $doc->loadHTML($this->DOMinnerHTML($elements));
                            
                            
                            $finder = new \DomXPath($doc);
                            
                            $nodes = $finder->query("//*[contains(@class, '$trackField')]");
                            
                            foreach($nodes as $node)
                            {
                                $trackingId = trim(str_replace('Tracking ID:','',$node->nodeValue));            
                            }
            
                            $nodes = $finder->query("//*[contains(@class, '$carrierField')]");
                            
                            foreach($nodes as $node)
                            {
                                $carrier = trim(str_replace('Delivery by','',str_replace('Shipped with','',$node->nodeValue)));            
                            }        
                            
                            if(empty($carrier))
                            {
                                
                                $nodes = $finder->query("//*[contains(@class, '$carrierField2')]");
                                
                                foreach($nodes as $node)
                                {
                                    $carrier = trim(str_replace('Delivery by','',str_replace('Shipped with','',$node->nodeValue)));            
                                }        
                            }
                           
                         
                            if(empty(trim($trackingId)) && !empty(trim($carrier)) )
                                continue;
                            
                            $carrierId = carriers::where('name',$carrier)->get()->first(); 
                            
                            if(empty($carrierId))
                            {
                                $carrierId = carriers::where('alias','like','%'.$carrier.'%')->get()->first(); 
                            }
            
                            $amzCarrier = carriers::where('name','Amazon')->get()->first(); 
                            $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 
            
                            if($carrierId->id == $amzCarrier->id && $order->marketplace == 'Walmart' && $this->startsWith($trackingId,'TBA'))
                            {                                
                               $resp='';
                               if($order->account_id=="Cindy")
                               {    
                                    if(empty($order->of_bce_created_at))
                                        orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId,'of_bce_created_at' =>Carbon::now(),'of_bce_created_at'=>Carbon::now(),'isBCE'=>true]);
                                    else
                                        orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId,'of_bce_created_at'=>Carbon::now(),'isBCE'=>true]);

                                    $this->sendOrderToSheet($order->id);
                                    
                               }
                               else
                               {

                                $order = orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId, 'of_bce_created_at'=>Carbon::now(),'isBCE'=>true]);   
                                $this->sendOrderToSheet($order->id);
                                $shippedCounter++;              
                                $found = true; 
                                
                                //$shipmentId = $this->getShipment(trim($number));
                                            
                                //if(empty($shipmentId) || $shipmentId== 'Error')
                                  //      continue;
                                        
                                //$resp = $this->getBceResponse($order->id,$shipmentId , $trackingId, 'Walmart',1);
                               
                                //$resp = $this->insertConversionRecord($order->id,$shipmentId , $trackingId);
                                //continue; 

                                //$forward = $this->forwardEmail(trim($number));
                               
                                // if(!empty($resp) && $resp!= 'Error')
                                // {
                                //     try{
                                       
                                //         $order = orders::where('id',$order->id)->update(['carrierName'=>$bceCarrier->id, 'trackingNumber'=>$trackingId, 'newTrackingNumber'=>$resp, 'converted'=>true]);   
                                //         $shippedCounter++;              
                                //         $found = true; 
                                        
                                //     }
                                //         catch(\Exception $ex)
                                //         {
                        
                                //         }
                                //     }

                                
                                }
                                
                            }
                            else
                            {    

                                $this->shipOrder($order->id, $trackingId, $carrierId->name, 'new'); 
                                try{
                                if($order->account_id=='Cindy')
                                {
                                    $this->updateSheetTracking($trackingId, $order->sellOrderId, $carrierId->name);
                                }
                                
                                $order = orders::where('id',$order->id)->update(['carrierName'=>$carrierId->id, 'trackingNumber'=>$trackingId, 'status'=>'shipped']);     
                                
                                
                                $shippedCounter++;  
                                $found = true;                 
                                }
                                catch(\Exception $ex)
                                {
                                    
                                }
            
                                
                            }
                            
                        }
                        catch(\Exception $ex)
                        {
            
                        }  
                    }
                }
            }
        
            
                
        }   
        
        Session::flash('success_msg', __('Orders Processed Successfully'));
        
        Session::flash('count_msg', $shippedCounter." Orders are Shipped Successfully from page no. " .$pageNum);
        
        
        return redirect()->route('processedOrders');
        
    }

    public function export(Request $request)
    {        
        $storeFilter = $request->storeFilter;
        $marketFilter = $request->marketFilter;
        $stateFilter = $request->stateFilter;
        $amountFilter = $request->amountFilter; 
        $sourceFilter = $request->sourceFilter; 

        $filename = date("d-m-Y")."-".time()."-orders.xlsx";
        return Excel::download(new OrdersExport($storeFilter,$marketFilter,$stateFilter,$amountFilter,$sourceFilter), $filename);
    }

  

    public function getAmazonDetails(Request $request)
    {
        $order = orders::where('poNumber',$request->po)->get()->first();
        $client = new client(); 
        
        $field = 'carrierRelatedInfo-container';
        
            $trackField = 'carrierRelatedInfo-trackingId-text';
    
            $carrierField = 'a-spacing-small widgetHeader';

            $carrierField2= 'a-spacing-small carrierRelatedInfo-mfn-carrierNameTitle';
                             
            $trackingId = '';
            $carrier = '';

            try{
            $baseUrl = "https://www.amazon.com/progress-tracker/package/ref=ppx_yo_dt_b_track_package?_encoding=UTF8&itemId=klpjsskrrrpoqn&orderId=";

            
            $response = $client->request('GET', $baseUrl.$order->poNumber,
            [   

            ]);    
            
            
            $statusCode = $response->getStatusCode();
            
            $html = $response->getBody()->getContents();  
            
            
            $html = str_replace('&','&amp;',$html);
            
            $doc = new \DOMDocument();
            
            $internalErrors = libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            
            try{
                $elem = $doc->getElementById('primaryStatus');
                $stat =  $elem->nodeValue;                             
                var_dump($stat);
            }
            catch(\Exception $ex)
            {

            }
        
            $elements = $doc->getElementById($field);
            
            if(empty($elements))
                die();
                
            libxml_use_internal_errors($internalErrors);
            
            $doc->loadHTML($this->DOMinnerHTML($elements));
            
            
            $finder = new \DomXPath($doc);
            
            $nodes = $finder->query("//*[contains(@class, '$trackField')]");
            
            foreach($nodes as $node)
            {
                $trackingId = trim(str_replace('Tracking ID:','',$node->nodeValue));            
            }

            $nodes = $finder->query("//*[contains(@class, '$carrierField')]");
            
            foreach($nodes as $node)
            {
                $carrier = trim(str_replace('Delivery by','',str_replace('Shipped with','',$node->nodeValue)));            
            }        
            
            if(empty($carrier))
            {
                
                $nodes = $finder->query("//*[contains(@class, '$carrierField2')]");
                
                foreach($nodes as $node)
                {
                    $carrier = trim(str_replace('Delivery by','',str_replace('Shipped with','',$node->nodeValue)));            
                }        
            }

           
            echo nl2br("\n".$carrier."\n");
            echo $trackingId;
    }
    catch(\Exception $ex)
    {

    }
}

    function DOMinnerHTML(\DOMElement $element) 
    { 
        $innerHTML = ""; 
        $children  = $element->childNodes;

        foreach ($children as $child) 
        { 
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML; 
    } 

    public function upsexport(Request $request)
    {        

        $storeFilter = $request->storeFilter;
        $daterange = $request->daterange;

        $filename = date("d-m-Y")."-".time()."-ups-conversions.xlsx";
        return Excel::download(new UPSExport($storeFilter,$daterange), $filename);
    }

    public function upsfilter(Request $request)
    {
        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');
        
        if($request->has('daterange'))
            $dateRange = $request->get('daterange');  

         $startDate = explode('-',$dateRange)[0];
            $from = date("Y-m-d", strtotime($startDate));  
         $endDate = explode('-',$dateRange)[1];
            $to = date("Y-m-d", strtotime($endDate)); 


            $count =0; 

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

        $orders = $orders->orderBy('orders.status', 'ASC')->paginate(100); 
        
        $orders = $orders->appends('storeFilter',$storeFilter)->appends('daterange',$dateRange);

        return view('orders.upsconversions',compact('orders','count','stores','dateRange','storeFilter'));
    }

    public function filter(Request $request)
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
        


        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
            
        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->leftJoin('ebay_products','order_details.SKU','=','ebay_products.sku')
        ->select(['orders.*',DB::raw('IFNULL( products.lowestPrice, 0) + IFNULL( ebay_products.ebayPrice, 0) as lowestPrice'),'products.asin','ebay_products.sku']);
        
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


        
        $orders = $orders->whereBetween(DB::raw('IFNULL( products.lowestPrice, 0) + IFNULL( ebay_products.ebayPrice, 0)'),[$minAmount,$maxAmount]);

        if(!empty($stateFilter)&& $stateFilter !='0')
        {           
            $orders = $orders->where('state',$stateFilter);
        }
                
        if(auth()->user()->role==1)
            $orders = $orders->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->orderBy('date', 'ASC')->groupby('orders.id')->paginate(100);
        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = $orders->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
        }
            
        else
            $orders = $orders->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->paginate(100);
        
        $orders = $orders->appends('storeFilter',$storeFilter)->appends('stateFilter',$stateFilter)->appends('marketFilter',$marketFilter)->appends('amountFilter',$amountFilter)->appends('sourceFilter',$sourceFilter);

        
        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();

     
        
        $maxPrice = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->max('totalAmount'));
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
        $flags= flags::all();
        return view('orders.new',compact('flags','orders','stateFilter','marketFilter','sourceFilter','storeFilter','amountFilter','stores','states','maxAmount','minAmount','maxPrice'));
    }

   

    public function assignFilter(Request $request)
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
        


        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
            
        $orders = orders::leftJoin('order_details','order_details.order_id','=','orders.id')
        ->leftJoin('products','order_details.SKU','=','products.asin')
        ->leftJoin('ebay_products','order_details.SKU','=','ebay_products.sku')
        ->select(['orders.*',DB::raw('IFNULL( products.lowestPrice, 0) + IFNULL( ebay_products.ebayPrice, 0) as lowestPrice'),'products.asin','ebay_products.sku']);
        

        if(!empty($storeFilter)&& $storeFilter !=0)
        {
            $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
            $orders = $orders->where('storeName',$storeName->store);
        }
       
        if(!empty($sourceFilter)&& $sourceFilter !=0)
        {                            
            if($sourceFilter==1)
                $orders = $orders->whereNotNull('products.asin');
            elseif($sourceFilter==2)
                $orders = $orders->whereNotNull('ebay_products.sku');                                
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

        $orders = $orders->whereBetween(DB::raw('IFNULL( products.lowestPrice, 0) + IFNULL( ebay_products.ebayPrice, 0)'),[$minAmount,$maxAmount]);

        if(!empty($stateFilter)&& $stateFilter !='0')
            {           
                $orders = $orders->where('state',$stateFilter);
            }   

        if(auth()->user()->role==1)
            {
                $orders = $orders->where('status','unshipped')->where('assigned',0)->orderBy('date', 'ASC')->groupby('orders.id')->paginate(100);

                $users = User::all(); 
            }
    
        elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders->where('status','unshipped')->where('assigned',0)->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->groupby('orders.id')->paginate(100);
    
                $users = User::where('manager_id',auth()->user()->id)->get(); 
                
            }

        else
            $orders = $orders->where('status','unshipped')->where('assigned',0)->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->groupby('orders.id')->paginate(100);
        
        $orders = $orders->appends('storeFilter',$storeFilter)->appends('stateFilter',$stateFilter)->appends('marketFilter',$marketFilter)->appends('amountFilter',$amountFilter)->appends('sourceFilter',$sourceFilter);
        
        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();        
        
        $maxPrice = ceil(orders::where('status','unshipped')->max('totalAmount'));
        
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
        $flags = flags::all(); 
         
        return view('orders.assign',compact('flags','orders','users','stores','states','maxAmount','minAmount','maxPrice','stateFilter','marketFilter','storeFilter','amountFilter','sourceFilter'));
    }

    public function search(Request $request)
    {
        $query = $request->searchQuery;
        $route = $request->route; 
        
        $search = 1;

        if ($route=='transactions')
        {
                $transactions = transactions::select(['transactions.*', 'bank_accounts.name', 'accounting_categories.category'])
            ->leftJoin('bank_accounts','bank_accounts.id','=','transactions.bank_id')
            ->leftJoin('accounting_categories','accounting_categories.id','=','transactions.category_id')
            ->whereNull('transactions.category_id')
            ->where(function($test) use ($query){
                $test->where('debitAmount', 'LIKE', '%'.$query.'%');
                $test->orWhere('creditAmount', 'LIKE', '%'.$query.'%');
                $test->orWhere('description', 'LIKE', '%'.$query.'%');
            })  
            ->paginate(100);

            $banks = bank_accounts::select()->get();
            $categories = accounting_categories::select()->get();

            $startDate = transactions::whereNull('transactions.category_id')->min('date');
            $endDate = transactions::whereNull('transactions.category_id')->max('date');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  

            $dateRange = $from .' - '.$to;
            $transactions = $transactions->appends('searchQuery',$query)->appends('route', $route);
            return view('accounting.transactions', compact('transactions','banks','categories','dateRange','search','route'));
                            
        }

        else if ($route=='processedtransactions')
        {
            $transactions = transactions::select(['transactions.*', 'bank_accounts.name', 'accounting_categories.category'])
            ->leftJoin('bank_accounts','bank_accounts.id','=','transactions.bank_id')
            ->leftJoin('accounting_categories','accounting_categories.id','=','transactions.category_id')
            ->whereNotNull('transactions.category_id')
            ->where(function($test) use ($query){
                $test->where('debitAmount', 'LIKE', '%'.$query.'%');
                $test->orWhere('creditAmount', 'LIKE', '%'.$query.'%');
                $test->orWhere('description', 'LIKE', '%'.$query.'%');
            })  
            ->paginate(100);

            $banks = bank_accounts::select()->get();
            $categories = accounting_categories::select()->get();

            $startDate = transactions::whereNotNull('transactions.category_id')->min('date');
            $endDate = transactions::whereNotNull('transactions.category_id')->max('date');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  

            $dateRange = $from .' - '.$to;

           $transactions = $transactions->appends('searchQuery',$query)->appends('route', $route);
            return view('accounting.processedtransactions', compact('transactions','banks','categories','dateRange','search','route'));
        }

        else if($route == 'newOrders')
        {            
             
            if(auth()->user()->role==1)
            {            

                $orders = orders::select()->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')                
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                
                $orders = orders::select()->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->whereIn('storeName',$strArray)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
                
            }

            else
            {
            $orders = orders::select()->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->where('uid',auth()->user()->id)
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            })                
            ->orderBy('date', 'ASC')->paginate(100);
            }





                $stores = accounts::select(['id','store'])->get();
                $states = states::select()->distinct()->get();
                
                $maxAmount = ceil(orders::where('status','unshipped')->max('totalAmount'));
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
                $flags= flags::all();
                return view('orders.new',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice','search','route'));
            
        }
        else if($route == 'processedOrders')
        {

            if(auth()->user()->role==1)
            {            
                $orders = orders::select()->where('status','processing')                
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                
                $orders = orders::select()->where('status','processing')->whereIn('storeName',$strArray)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
                
            }

            else
            {
                $orders = orders::select()->where('status','processing')->where('uid',auth()->user()->id)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }
                
            foreach($orders as $order)
            {            
                $order->shippingPrice = $this->getTotalShipping($order->id);
            }
            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.processed',compact('orders','search','route'));   
            
        }

        else if($route == 'cancelledOrders')
        {
            if(auth()->user()->role==1)
            {            

                $orders = orders::select()->where('status','cancelled')                
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                
                $orders = orders::select()->where('status','cancelled')->whereIn('storeName',$strArray)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
                
            }

            else
            {
            $orders = orders::select()->where('status','cancelled')->where('uid',auth()->user()->id)
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            })                
            ->orderBy('date', 'ASC')->paginate(100);
            }
                
                foreach($orders as $order)
                {            
                    $order->shippingPrice = $this->getTotalShipping($order->id);
                }

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.cancelled',compact('orders','search','route'));
        }

        else if($route == 'shippedOrders')
        {           

            if(auth()->user()->role==1)
            {            

                $orders = orders::select()->where('status','shipped')                
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                
                $orders = orders::select()->where('status','shipped')->whereIn('storeName',$strArray)
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
                
            }

            else
            {
                $orders = orders::select()->where('status','shipped')->where('uid',auth()->user()->id)
                ->where(function($test) use ($query){
                        $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                        $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                        $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                        $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })                
                ->orderBy('date', 'ASC')->paginate(100);
            }

            foreach($orders as $order)
            {            
                $order->shippingPrice = $this->getTotalShipping($order->id);
            }
            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.shipped',compact('orders','search','route'));
        }
        else if($route == 'report')
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
    
            $orders = orders::select()
            ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
            })->orderBy('date', 'ASC')->paginate(100);
            
            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);

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
            return view('report.index',compact('orders','stores','dateRange','statusFilter','marketFilter','storeFilter','carrierArr','userFilter','users' ,'search','route'));

         
        }

        else if($route == 'blacklist')
        {

            $blacklist = blacklist::select()->where('sku', 'LIKE', '%'.$query.'%')->orderBy('date','desc')->paginate(100);

            $blacklist = $blacklist->appends('searchQuery',$query)->appends('route', $route);

            $reasons = reasons::all(); 
            return view('blacklist', compact('blacklist','reasons','search','route'));
        }

        else if($route == 'conversions')
        {
           $credits = $this->getCredits();
           
           if(auth()->user()->role==1)
            {            
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })
                ->where(function($test){
                    $test->whereNull('conversions.status');
                    $test->orWhere('conversions.status','!=','Delivered');
                }) 
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }                                                                
                
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) 
                ->where(function($test){
                    $test->whereNull('conversions.status');
                    $test->orWhere('conversions.status','!=','Delivered');
                })
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
                
            }

            else
            {
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->where('uid',auth()->user()->id)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) 
                ->where(function($test){
                    $test->whereNull('conversions.status');
                    $test->orWhere('conversions.status','!=','Delivered');
                }) 
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('uid',auth()->user()->id)
                ->count(); 
            }

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.conversions',compact('orders','credits','count','search','route'));
        }

        else if($route == 'conversions2')
        {
           $credits = $this->getCredits();
           
           if(auth()->user()->role==1)
            {            
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })           
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }                                                                
                
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) 
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
                
            }

            else
            {
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->where('uid',auth()->user()->id)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                }) 
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('uid',auth()->user()->id)
                ->count(); 
            }

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.conversions2',compact('orders','credits','count','search','route'));
        }
        else if($route == 'upsConversions')
        {
           
           
           if(auth()->user()->role==1)
            {            
                $orders = orders::select()->where('isBCE',true)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');                
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                })           
                ->orderBy('orders.status', 'ASC')->paginate(100);

                $count = orders::select()->where('isBCE',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }                                                                
                
                $orders = orders::select()->where('isBCE',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');                
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) 
                ->orderBy('orders.status', 'ASC')->paginate(100);

                $count = orders::select()->where('isBCE',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');                    
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
                
            }

            else
            {
                $orders = orders::select()->where('isBCE',true)->where('uid',auth()->user()->id)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                })                 
                ->orderBy('orders.status', 'ASC')->paginate(100);

                $count = orders::select()->where('isBCE',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');                    
                    $test->orWhere('upsTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('uid',auth()->user()->id)
                ->count(); 
            }

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            $stores = accounts::all();
            
            $startDate = orders::where('isBCE',true)->min('date');
            $endDate = orders::where('isBCE',true)->max('date');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  

            $dateRange = $from .' - '.$to;
            
            return view('orders.upsconversions',compact('orders','count','search','route','stores','dateRange'));
        }

        else if($route == 'deliveredConversions')
        {
           $credits = $this->getCredits();
           
           if(auth()->user()->role==1)
            {            
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) 
                ->where('conversions.status','Delivered')
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
            }
    
            elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }                                                                
                
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) ->where('conversions.status','Delivered')
                
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    })                 
                ->count(); 
                
            }

            else
            {
                $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->where('uid',auth()->user()->id)
                ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
                })
                ->where(function($test) use ($query){
                $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                }) ->where('conversions.status','Delivered')
                
                
                ->orderBy('date', 'ASC')->paginate(100);

                $count = orders::select()->where('converted',true)
                ->where(function($test){
                    $test->where('status','processing');
                })
                ->where(function($test) use ($query){
                    $test->where('sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('newTrackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('uid',auth()->user()->id)
                ->count(); 
            }

            $orders = $orders->appends('searchQuery',$query)->appends('route', $route);
            return view('orders.deliveredConversions',compact('orders','credits','count','search','route'));
        }

        else if($route == 'products')
        {
            $last_run = products::max('modified_at');   
            $products = products::select()
            ->where(function($test) use ($query){
                $test->where('asin', 'LIKE', '%'.$query.'%');
                $test->orWhere('upc', 'LIKE', '%'.$query.'%');
            })
            ->paginate(100); 

            $strategies = strategies::select()->get(); 
            $accounts = accounts::select()->get(); 
            $strategyCodes = array(); 
            
            $maxSellers = ceil(products::where(function($test) use ($query){
                $test->where('asin', 'LIKE', '%'.$query.'%');
                $test->orWhere('upc', 'LIKE', '%'.$query.'%');
            })->max('totalSellers'));
            
            $maxPrice = ceil(products::where(function($test) use ($query){
                $test->where('asin', 'LIKE', '%'.$query.'%');
                $test->orWhere('upc', 'LIKE', '%'.$query.'%');
            })->max('price'));

            $minAmount = 0;
            $maxAmount = $maxPrice;
            $minSeller = 0;
            $maxSeller = $maxSellers;
    
            $accountFilter = 0; 
            $strategyFilter = 0; 
    
            foreach($strategies as $strategy)
            {
                $strategyCodes[$strategy->id] = $strategy->code;
            }
            
            
            $products = $products->appends('searchQuery',$query)->appends('route', $route);
            return view('products.index',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','minAmount','maxAmount','minSeller','maxSeller','accountFilter','strategyFilter','last_run','search','route'));
      
        }
        elseif($route == 'ebayProducts')
        {
            $products = ebay_products::select()->orderBy('created_at','desc')
            ->where(function($test) use ($query){
                $test->where('sku', 'LIKE', '%'.$query.'%');
                $test->orWhere('name', 'LIKE', '%'.$query.'%');
                $test->orWhere('productId', 'LIKE', '%'.$query.'%');
            })
            ->paginate(100);
            
            $categories = categories::all(); 
            $strategies = ebay_strategies::all();
            $accounts = accounts::all();
            $categoryFilter = 0; 
            $strategyFilter = 0;
            $minAmount = 0;
            
            $strategies = ebay_strategies::all(); 
            $categories = categories::all();
            $strategyArr = array();
            $categoryArr = array();
            $accountArr = array();
            
            foreach ($strategies as $strategy)
            {
                $strategyArr[$strategy->id] = $strategy->code;
            }
    
            foreach ($categories as $category)
            {
                $categoryArr[$category->id] = $category->name;
            }
            
            foreach ($accounts as $account)
            {
                $accountArr[$account->id] = $account->store;
            }
            
    
    
    
    
            $startDate = ebay_products::min('created_at');
            $endDate = ebay_products::max('created_at');
    
            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
    
            $dateRange = $from .' - '.$to;
    
            $maxAmount = ceil(ebay_products::where(function($test) use ($query){
                $test->where('sku', 'LIKE', '%'.$query.'%');
                $test->orWhere('name', 'LIKE', '%'.$query.'%');
                $test->orWhere('productId', 'LIKE', '%'.$query.'%');
            })->max('ebayPrice'));
                    
            $maxPrice  = $maxAmount; 
            $products = $products->appends('searchQuery',$query)->appends('route', $route);
            return view('ebayProducts',compact('products','strategies','categories','minAmount','maxAmount','categoryFilter','strategyFilter','dateRange','maxPrice','strategyArr','categoryArr','accountArr','accounts','search','route'));
            
        }

        elseif($route == 'walmartProducts')
        {
            $minAmount = 0;
            $sellersFilter=0;

            $products = walmart_products::select()->orderBy('created_at','desc')
            ->where(function($test) use ($query){                
                $test->orWhere('name', 'LIKE', '%'.$query.'%');
                $test->orWhere('seller', 'LIKE', '%'.$query.'%');
                $test->orWhere('productId', 'LIKE', '%'.$query.'%');
            })
            ->paginate(100);
           
    
            $startDate = walmart_products::min('created_at');
            $endDate = walmart_products::max('created_at');
    
            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
    
            $dateRange = $from .' - '.$to;
    
            $maxAmount = ceil(walmart_products::where(function($test) use ($query){
                $test->orWhere('name', 'LIKE', '%'.$query.'%');
                $test->orWhere('seller', 'LIKE', '%'.$query.'%');
                $test->orWhere('productId', 'LIKE', '%'.$query.'%');
            })->max('price'));
                    
            $maxPrice  = $maxAmount; 
            $products = $products->appends('searchQuery',$query)->appends('route', $route);
            
            $sellers = walmart_products::distinct()->get(['seller']);
            return view('walmartProducts',compact('products','sellers','minAmount','maxAmount','sellersFilter','dateRange','maxPrice','search','route'));
            
        }

        else if($route == 'returns')
        {
         
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->whereNull('returns.status')
                ->orderBy('created_at','desc')
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
                ->whereNull('returns.status')     
                 
                ->whereIn('orders.storeName',$strArray)  
                ->orderBy('created_at','desc')           
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })    
                ->where('orders.uid',auth()->user()->id)  
                ->whereNull('returns.status')   
                ->orderBy('created_at','desc')           
                ->paginate(100);
            }

            foreach($returns as $return)
            {
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
                                $sources[]= 'N/A'; 
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

             $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('returns.return',compact('returns','accounts','stores','search','route'));
        }

        else if($route == 'refunds')
        {
         
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('returns.status','returned')
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
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })          
                ->where('returns.status','returned')
                ->whereIn('orders.storeName',$strArray)
                ->orderBy('returnDate','desc')             
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })    
                ->where('orders.uid',auth()->user()->id)  
                ->where('returns.status','returned')       
                ->orderBy('returnDate','desc')       
                ->paginate(100);
            }

            foreach($returns as $return)
            {
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
                                $sources[]= 'N/A'; 
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

             $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('returns.refund',compact('returns','accounts','stores','search','route'));
        }

        else if($route == 'completed')
        {
         
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('returns.status','refunded')
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
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    }) 
                ->where('returns.status','refunded')         
                ->whereIn('orders.storeName',$strArray)   
                ->orderBy('refundDate','desc')          
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where(function($test) use ($query){
                    $test->where('returns.sellOrderId', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.poNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('returns.trackingNumber', 'LIKE', '%'.$query.'%');
                    $test->orWhere('orders.buyerName', 'LIKE', '%'.$query.'%');
                    })    
                ->where('orders.uid',auth()->user()->id)   
                ->where('returns.status','refunded')       
                ->orderBy('refundDate','desc')      
                ->paginate(100);
            }

            foreach($returns as $return)
            {
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
                                $sources[]= 'N/A'; 
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

             $returns = $returns->appends('searchQuery',$query)->appends('route', $route);
            return view('returns.complete',compact('returns','accounts','stores','search','route'));
        }

        else if($route == 'product.report')
        {
            $productReport = new ProductReportController();
            $query = $request->searchQuery;
            return $productReport->index($request, $query);
        } 

        else if($route == 'sold.report')
        {
            $soldReport = new SoldReportController();
            $query = $request->searchQuery;
            return $soldReport->index($request, $query);
        } 


        else
        redirect()->back();
    }

    public function syncOrders()
    {
        
        if(auth()->user()->role == 1)
            $accounts = accounts::select()->get(); 
        elseif(auth()->user()->role == 2)
            $accounts = accounts::select()->where('manager_id',auth()->user()->id)->get(); 

        $oldCount = orders::select()->where('status','unshipped')->count();
        $oldCindy = orders::select()->where('flag','8')->count();
        $oldJonathan = orders::select()->where('flag','9')->count();
        $oldSamuel = orders::select()->where('flag','10')->count();


        foreach($accounts as $account)
        {
            $this->sync($account->store, $account->username, $account->password);
        }    
        
        
        $newCount = orders::select()->where('status','unshipped')->count(); 
        $newCindy = orders::select()->where('flag','8')->count();
        $newJonathan = orders::select()->where('flag','9')->count();
        $newSamuel = orders::select()->where('flag','10')->count();
        
        $orderCounter = $newCount - $oldCount;
        $cindyCnt = $newCindy - $oldCindy;
        $samuelCnt = $newSamuel - $oldSamuel;
        $jonathanCnt = $newJonathan - $oldJonathan;

        Session::flash('success_msg', __('Orders Sync Completed'));
        Session::flash('count_msg', $orderCounter." New Orders are Imported Successfully");
        Session::flash('inner_msg',"Cindy: " . $cindyCnt ." , Samuel: " . $samuelCnt ." , Jonathan: ". $jonathanCnt);

        return redirect()->route('newOrders');
    }

    public function checkPass(Request $request)
    {
        $password = $request->password; 
        $id = $request->id;
        $admin = User::where('role',1)->get()->first();
        $resp = Hash::check($password, $admin->password);

        if($resp)
        {
           return $this->cancelOrder($id);    
        }

        else
            return "failure";
        
    }



    public function cancelOrder($id)
    {
        $order  = orders::where('id',$id)->get()->first();
        
        $client = new Client();
        
        $data['SiteOrderID'] =$order->sellOrderId;
        
        $data['Site'] = $order->marketplace;

        $data['OrderStatus'] = "Cancelled";  

        $order_details = order_details::where('order_id',$id)->get(); 

        foreach($order_details as $detail)
        {
            $data2['siteItemId']=$detail->siteItemId;
        
            $data2['OrderStatus']="Cancelled";
            
            $data['orderDetails'][]= $data2;
        }

        $credential = accounts::where('store',$order->storeName)->get()->first();        
        
        $response = $client->request('PUT', 'https://rest.selleractive.com:443/api/Order',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'token' => 'test-token'],
                'body' => json_encode($data),
                'auth' => [
                    $credential->username, 
                    $credential->password
                ]
            ]);    
            
        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "deleteIssue";
                    
        $body = $response->getBody()->getContents();               
        
        $order  = orders::where('id',$id)->update(['status'=>'cancelled','poNumber'=>null, 'poTotalAmount'=>null]);
        
        $orderDetails = order_details::where('order_id',$id)->get(); 

        foreach($orderDetails as $orderDetail)
        {
            products::where('asin',$orderDetail->SKU)->increment('cancelled',$orderDetail->quantity);
        }

        if($order)
            return "success";
        else
            return "dbIssue";
        
    }

    public function shipOrder($id, $tracking, $carrier,$status)
    {
       
        $order  = orders::where('id',$id)->get()->first();

        $client = new Client();
        
        $data['SiteOrderID'] =$order->sellOrderId;
        
        $data['Site'] = $order->marketplace;

        $data['OrderStatus'] = "Shipped";  

        $order_details = order_details::where('order_id',$id)->get(); 

        foreach($order_details as $detail)
        {
            $data2['siteItemId']=$detail->siteItemId;
        
            $data2['OrderStatus']="Shipped";

            $data2['ShippingTracking']=$tracking; 
            
            if(trim($carrier)=='Fedex')
            {
                if(strlen($tracking)>12)
                    $data2['ShippingCarrier']='Fedex SmartPost';
                else
                    $data2['ShippingCarrier']=$carrier;
            }
            else
            {
                $data2['ShippingCarrier']=$carrier;
            }

            $data2['QuantityShipped'] = $detail->quantity;
            
            if($status=="new")
                $data2['DateShipped'] = date('Y-m-d\TH:i:s');

            $data2['GiftMessage'] = "Purchase Order " .$order->poNumber; 
            
            $data['orderDetails'][]= $data2;
        }

        $credential = accounts::where('store',$order->storeName)->get()->first();        
        
        $response = $client->request('PUT', 'https://rest.selleractive.com:443/api/Order',
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'token' => 'test-token'],
                'body' => json_encode($data),
                'auth' => [
                    $credential->username, 
                    $credential->password
                ]
            ]);    
            
        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "deleteIssue";
                    
        $body = $response->getBody()->getContents();               
        
        return "success";
    }

    public function sync($store, $username, $password)
    {
           
        $fulfillmentOrders = array(); 
        $jonathanOrders = array();
        $samuelOrders = array();
        $endDate = orders::where('status','unshipped')->max('date');
        
        $date = date_format(date_create($endDate), 'Y-m-d');
        
        $date = date('Y-m-d', strtotime($date . "-5 days"));

        $setting = settings::where('name','cindy')->get()->first(); 
        $samuelSetting = settings::where('name','samuel')->get()->first(); 
        $jonathanSetting = settings::where('name','jonathan')->get()->first(); 
        

        $time = date_format(date_create($endDate), 'H:i:s');
        $client = new Client();
        
        $page =1; 

        while(true)
        {                        
            $data['req.dateOrderedFrom'] = $date;            
            $data['req.page'] = $page;
            $data['req.orderStatus'] = "Unshipped";
            
           
            $response = $client->request('GET', 'https://rest.selleractive.com:443/api/Order',
            [
                'query' =>$data,
                'auth' => [
                    $username, 
                    $password
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if($statusCode!=200)
                    die("Error while getting results from API");

            $body = $response->getBody()->getContents();               
                       
            $resp = json_decode($body);
            
            if(empty($body) || count($resp)==0)
                break;
            
            foreach($resp as $order)
            {
                $qty = 0; 
                $amount = 0; 
                $temp = array();
                $details = array();
                $address = '';

                $att = 'OrderID';
                $temp['orderId'] = $order->$att;
                
                $att = 'DateOrdered';           
                $dateTime = new \DateTime ($order->$att);
                $dateTime->setTimezone(new \DateTimeZone('America/Los_Angeles'));
                $temp['date'] = $dateTime->format('Y-m-d H:i:s'); 
                
                $att = 'Site';
                $temp['marketplace'] = $order->$att;
                
                $temp['storeName']= $store;

                $att = 'SiteOrderID';
                $temp['sellOrderId'] = $order->$att;
                $att = 'Name';
                $temp['buyerName'] =$order->$att;

                $att = 'OrderStatus';
                $temp['status'] =$order->$att;

                $att = 'Phone';
                $temp['phone'] =$order->$att;               

                $att = 'Address1';
                $temp['address1'] =$order->$att;   

                $att = 'Address2';
                $temp['address2'] =$order->$att;   

                $att = 'Address3';
                $temp['address3'] =$order->$att;   

                $att = 'City';
                $temp['city'] =$order->$att;   

                $att = 'StateOrRegion';
                $temp['state'] =$order->$att;   

                $att = 'Country';
                $temp['country'] =$order->$att;   

                $att = 'PostalCode';
                $temp['postalCode'] =$order->$att;
                
                $att = 'LatestShipDate';
                $dateTime = new \DateTime ($order->$att);
                $dateTime->setTimezone(new \DateTimeZone('America/Los_Angeles'));
                $temp['dueShip'] = $dateTime->format('Y-m-d H:i:s');                 

                $att = 'LatestDeliveryDate';
                $dateTime = new \DateTime ($order->$att);
                $dateTime->setTimezone(new \DateTimeZone('America/Los_Angeles'));
                $temp['dueDelivery'] = $dateTime->format('Y-m-d H:i:s');             

                

                $att = 'OrderDetails';
                foreach($order->$att as $item)
                {
                                
                $att = 'QuantityOrdered';
                $qty += $item->$att;
                
                $att = 'TotalPrice';
                $amount+= $item->$att;                              

                }

                $temp['quantity']= $qty;
                $temp['totalAmount'] = $amount;

                $exists = orders::select()->where('orderId',$temp['orderId'])->count();
                  
                if($exists>0)
                    continue; 
              
               $orderId = orders::create($temp)->id;
                
                $att = 'OrderDetails';
                foreach($order->$att as $item)
                {
                
                    $temp2 = array(); 
                    
                    $att = 'SKU';
                    $temp2['SKU'] = $item->$att;
                    
                    $att='SiteItemID';
                    $temp2['siteItemId'] = $item->$att; 

                    $att = 'UnitPrice';
                    $temp2['unitPrice'] = $item->$att;            

                    $att = 'Title';
                    $temp2['name'] = $item->$att;
                    
                    $att = 'QuantityOrdered';
                    $temp2['quantity'] = $item->$att;                
                    
                    $att = 'TotalPrice';
                    $temp2['totalPrice'] = $item->$att;                
                    
                    $att = 'ShippingPrice';
                    $temp2['shippingPrice'] = $item->$att;

                    $temp2['order_id'] = $orderId;

                    $details[]= $temp2; 
                    $tempOrder = array();
                    $samuelOrder = array();
                    $jonathanOrder = array();
        
                    $tempOrder["itemLink"] = "https://www.amazon.com/gp/offer-listing/".$temp2['SKU'];
                    
                    $tempOrder["ASIN"] = $temp2['SKU'];        
                    
                    $tempOrder["qty"] =   $temp['quantity'];
                    $tempOrder["date"] =   $temp['date'];
                    $tempOrder["dueShip"] =   $temp['dueShip'];
                    $tempOrder["country"] =   $temp['country'];
            
                    $product = products::where('asin',$temp2['SKU'])->get()->first();
            
                   
                    $tempOrder["maxPrice"] =  empty($product->lowestPrice)?0:$product->lowestPrice * (1 +$setting->maxPrice/100) * $temp['quantity'];        
                    
                    $tempOrder["maxPrice"] = number_format((float) $tempOrder["maxPrice"], 2, '.', '');

                    $tempOrder["itemPrice"] = empty($product->lowestPrice)?0:$product->lowestPrice;
                    
                    $tempOrder["itemPrice"] = number_format((float) $tempOrder["itemPrice"], 2, '.', '');

                    $tempOrder["totalPrice"] =empty($product->lowestPrice)?0:$product->lowestPrice * $temp['quantity'];    
                    
                    $tempOrder["totalPrice"] = number_format((float) $tempOrder["totalPrice"], 2, '.', '');

                    $tempOrder["discountPayment"] = empty($product->lowestPrice)?0:$product->lowestPrice * $temp['quantity'] * (1- $setting->discount/100);
                    
                    $tempOrder["discountPayment"] = number_format((float) $tempOrder["discountPayment"], 2, '.', '');
                    
                    
                    $tempOrder["discountFactor"] = $setting->discount;
                    $tempOrder["maxFactor"] = $setting->maxPrice;

                    $tempOrder["name"] = $temp['buyerName'];
                    
                    $tempOrder["street1"] = $temp['address1'];
                    
                    $tempOrder["street2"] = $temp['address2'];
                    
                    $tempOrder["city"] =  $temp['city'];
                    
                    $tempOrder["state"] =  $temp['state'];
                    
                    $tempOrder["zipCode"] =$temp['postalCode'];
                    
                    $tempOrder["phone"] =  $temp['phone'];
                    
                    $tempOrder["storeName"] = $store;
            
                    $tempOrder["referenceNumber"] = $temp['sellOrderId'];
                    
                    if(!empty($samuelSetting))
                    {
                        $samuelOrder = $tempOrder;
                        
                        $samuelOrder["maxPrice"] =  empty($product->lowestPrice)?0:$product->lowestPrice * (1 +$samuelSetting->maxPrice/100) * $temp['quantity']; 
                        
                        $samuelOrder["discountPayment"] = empty($product->lowestPrice)?0:$product->lowestPrice * $temp['quantity'] * (1- $samuelSetting->discount/100);
                        
                        $samuelOrder["discountFactor"] = $samuelSetting->discount;
                        
                        $samuelOrder["maxFactor"] = $samuelSetting->maxPrice;
                        
                        $samuelOrders[]=$samuelOrder; 
                    }

                    if(!empty($jonathanSetting))
                    {
                        $jonathanOrder = $tempOrder;
                        
                        $jonathanOrder["maxPrice"] =  empty($product->lowestPrice)?0:$product->lowestPrice * (1 +$jonathanSetting->maxPrice/100) * $temp['quantity']; 
                        
                        $jonathanOrder["discountPayment"] = empty($product->lowestPrice)?0:$product->lowestPrice * $temp['quantity'] * (1- $jonathanSetting->discount/100);
                        
                        $jonathanOrder["discountFactor"] = $jonathanSetting->discount;
                        
                        $jonathanOrder["maxFactor"] = $jonathanSetting->maxPrice;

                        $jonathanOrders[]=$jonathanOrder; 
                    }

                    $fulfillmentOrders[]=$tempOrder;
                    
                    
            
                     products::where('asin',$temp2['SKU'])->increment('sold', $temp2['quantity'] );
                     products::where('asin',$temp2['SKU'])->increment('30days', $temp2['quantity'] );
                     products::where('asin',$temp2['SKU'])->increment('60days', $temp2['quantity'] );
                     products::where('asin',$temp2['SKU'])->increment('90days', $temp2['quantity'] );
                     products::where('asin',$temp2['SKU'])->increment('120days', $temp2['quantity'] );
                }

                try{
                    order_details::insert($details);   
                    $this->autoFlag($orderId);            
                }
                catch(\Exception $ex)
                {

                }
                
            }        
                $page++;               
        }
        
        $priorities = settings::select()->orderBy('priority','ASC')->get();
        
        $sendCindyOrders  = array();
        $sendSamuelOrders  = array();
        $sendJonathanOrders  = array();

        foreach($priorities as $priority)
        {            
            if($priority->name=='cindy')
            {
                $sendCindyOrders['data'] = $this->parseFulfillment($fulfillmentOrders,'cindy',$sendSamuelOrders,$sendJonathanOrders);
            }
            elseif($priority->name=='samuel')
            {
                $sendSamuelOrders['data'] = $this->parseFulfillment($samuelOrders,'samuel',$sendCindyOrders,$sendJonathanOrders);
            }
            elseif($priority->name=='jonathan')
            {
                $sendJonathanOrders['data'] = $this->parseFulfillment($jonathanOrders,'jonathan',$sendSamuelOrders,$sendCindyOrders);
            }
        }       
 
        $endPoint = env('CINDY_TOKEN', '');
        if(!empty($endPoint))
            $this->sendToGoogle($endPoint, $sendCindyOrders);

        $endPoint = env('SAMUEL_TOKEN', '');
        if(!empty($endPoint))
            $this->sendToGoogle($endPoint, $sendSamuelOrders);

        $endPoint = env('JONATHAN_TOKEN', '');
        if(!empty($endPoint))
            $this->sendToGoogle($endPoint, $sendJonathanOrders);
        
        
    }

    public function sendOrderToSheet($orderId)
    {
        $order = $orders::where('id',$orderId)->get()->first(); 

        if(empty($order))
            return; 

        $client = new client(); 
        $temp = array(); 
        $dt = Carbon::now();
        $temp['date'] = $dt->toDateTimeString()->format('m/d/Y');   
        $temp['orderDate'] =  Carbon::parse(date_format($order->date,'m/d/Y'));
        $temp['storeName'] = $order->storeName;
        $temp['buyerName'] = $order->buyerName;
        $temp['sellOrderId'] = $order->sellOrderId;
        $temp['poNumber'] = $order->poNumber;
        $temp['city'] = $order->city;
        $temp['state'] = $order->state;
        $temp['postalCode'] = $order->postalCode;
        $temp['trackingNumber'] = $request->trackingNumber;              

        $body['data'] = $temp;

        $endPoint = env('BCE_SYNC_TOKEN', '');

        try{                    

            $response = $client->request('POST', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode($body)          
            ]);    
            
            $statusCode = $response->getStatusCode();

            $body = json_decode($response->getBody()->getContents());    
          
        }
        catch(\Exception $ex)
        {
            
        }
    }

    public function sendToGoogle($endPoint, $orders)
    {
        $client = new client(); 

        $body = json_encode($orders); 
                

        try{                    

            $response = $client->request('POST', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => $body          
            ]);    
            
            $statusCode = $response->getStatusCode();

            $body = json_decode($response->getBody()->getContents());    
          
        }
        catch(\Exception $ex)
        {
           
        }
    }

    public function updateSheetTracking($tracking, $sellOrderId, $carrier)
    {
        
        try{
            $client = new client(); 
            
            $endPoint = env('CINDY_TOKEN', '');
            
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

    public function parseFulfillment($fulfillmentOrders, $flag, $orders2, $orders3)
    {
        $freshOrders = array();
        
        foreach($fulfillmentOrders as $order)
        {
            if (isset($freshOrders[$order['referenceNumber']]))
            {
                $freshOrders[$order['referenceNumber']]['ASIN'] = $freshOrders[$order['referenceNumber']]['ASIN'] .','. $order['ASIN']; 
                
            }            
            else
                $freshOrders[$order['referenceNumber']] = $order; 
        }

        return $this->removeDuplicates($freshOrders, $flag, $orders2, $orders3);
    }

    public function removeDuplicates($freshOrders, $flag, $orders2, $orders3)
    {        
        $finalOrders = array();
        foreach($freshOrders as $order)
        {
            $arr = explode(',',rtrim($order['ASIN'],','));

            $arr1 = array_unique($arr);
            
            if(count($arr1)==1)
            {
                $order['ASIN'] = $arr1[0];
                $finalOrders[] = $order;                
            }
                
        }

        return $this->checkCriteria($finalOrders, $flag, $orders2, $orders3);
    }

   public function checkCriteria($orders, $acc, $orders2, $orders3)
    {
        $flagnum =8; 
            
        if($acc=='cindy')
            $flagnum =8; 

        elseif($acc=='jonathan')
            $flagnum = 9; 

        elseif($acc=='samuel')
            $flagnum = 10;
        
        $googleOrders = array();

        $settings = settings::where('name',$acc)->get()->first(); 
        
        if(empty($settings) || !$settings->enabled)
            return $googleOrders;
        
        $amtCheck = $settings->amountCheck; 
        $strCheck = $settings->storesCheck; 
        $quantityRangeCheck = $settings->quantityRangeCheck; 
        $dailyAmountCheck = $settings->dailyAmountCheck; 
        $dailyOrderCheck = $settings->dailyOrderCheck; 

        $minQty = $settings->minQty; 
        $maxQty = $settings->maxQty; 

        $minAmount = $settings->minAmount; 
        $maxAmount = $settings->maxAmount; 
        
        $stores = $settings->stores; 

        $maxDailyOrders = $settings->maxDailyOrder; 
        $maxDailyAmount = $settings->maxDailyAmount; 

        $amt = 0; 

        foreach($orders as $order)
        {
            $flag = false;             

            if($strCheck)
            {
                $storesId = accounts::select()->where('store',$order["storeName"])->get()->first(); 
                if(in_array($storesId->id,json_decode($stores)))
                    $flag = true; 
                else
                {
                        $flag= false; 
                        continue;
                }
            }

            if($amtCheck)
            {
            
                if($order["totalPrice"]>=$minAmount && $order["totalPrice"]<=$maxAmount)
                    $flag = true; 
                else
                    {
                        $flag= false; 
                        continue;
                    }                              
                
            }
        
            if($quantityRangeCheck)
            {
                if($order["qty"]>=$minQty && $order["qty"]<=$maxQty)
                    $flag = true; 
                else
                   {
                        $flag= false; 
                        continue;
                    }
            }           

            $dailyOrders = orders::where('flag',$flagnum)->whereDate('flag_date',Carbon::today())->get();
            
            if($dailyAmountCheck)
            {
                foreach($dailyOrders as $order)
                {
                    $amt += $this->getDiscountPayment($order->id, $acc);                    
                }   

                if($amt> $maxDailyAmount)
                   {
                        $flag= false; 
                        continue;
                    } 
                else
                    $flag = true; 
            }
            
            if($dailyOrderCheck)
            {
                if(count($dailyOrders)>$maxDailyOrders)
                    {
                        $flag= false; 
                        continue;
                     }
                else
                    $flag = true; 
            }
            
            if($this->checkExisting($orders2, $order['referenceNumber']))
            {
                $flag=false;
                continue;
            }
           

            if($this->checkExisting($orders3, $order['referenceNumber']))
            {
                $flag=false;
                continue;
            }
            
            if($flag)
            {
                $ord = orders::where('sellOrderId',$order['referenceNumber'])->get()->first(); 
                if(!empty($ord->flag_date))
                    continue;
                $googleOrders[]= $order; 
                orders::where('sellOrderId',$order['referenceNumber'])->update(['flag'=>$flagnum,'flag_date'=>Carbon::today()]);
            }
        }

        return $googleOrders;
    }

    public function checkExisting($orders, $reference)
    {
        if(empty($orders))
            return false;
        $temp = json_encode($orders);
        $temp2 = json_decode($temp);

        foreach($temp2->data as $order)
        {   
            if(trim($order->referenceNumber)==trim($reference))
                return true;         
        }

        return false; 
    }

    public function getDiscountPayment($orderId, $flag)
    {
        $amt=0;
        $settings = settings::where('name',$flag)->get()->first();
      
        $orderDetails = order_details::where('order_id',$orderId)->get();
        foreach($orderDetails as $detail)
        {
            $product = products::where('asin',$detail->SKU)->get()->first();  
            $amt += empty($product->lowestPrice)?0:$product->lowestPrice * $detail->quantity * (1- $settings->discount/100);
        }

        return $amt;
        
    }

    public function updateOrder(Request $request)
    {  
        $type = $request->type;
        if($type == "ship")
        {
            $id = $request->id; 
            $carrier  = $request->carrier; 
            $tracking = $request->tracking; 
            $status = $request->status;
            $source = $request->source;

            $input = [
                'carrier' => $carrier,
                'tracking' => $tracking            
            ];
    
            $rules = [
                'carrier'    => 'required',
                'tracking' => 'required'  
            ];
    
            $validator = Validator::make($input,$rules);
    
            if($validator->fails())
            {
               Session::flash('error_msg', __('Please check the errors and try again.'));
               return "failure";
            }
            
            $carrierName = carriers::where('id',$carrier)->get()->first(); 
            
            if($source =='BCE')
            { 
                    $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 

                    $this->shipOrder($id, $tracking, $bceCarrier->name,$status);
                    
                   
                    $order = orders::where('id',$id)->update(['carrierName'=>$bceCarrier->id, 'newTrackingNumber'=>$tracking, 'status'=>'shipped']);
            }
            elseif($status=='new')
                {
                    $this->shipOrder($id, $tracking, $carrierName->name,$status);
                    $order = orders::where('id',$id)->update(['carrierName'=>$carrier, 'trackingNumber'=>$tracking, 'status'=>'shipped']);
                }
            else
                {
                    $this->shipOrder($id, $tracking, $carrierName->name,$status);
                    $order = orders::where('id',$id)->update(['carrierName'=>$carrier, 'trackingNumber'=>$tracking,'newTrackingNumber'=>'', 'converted'=>'0']);
                }
                

            if($order)
                return "success";
            else
                return "failure";
            
        }
        else
        {
        $id = $request->id; 
        $amount  = $request->amount; 
        $po = $request->po; 
        $status = $request->status;
        $account = $request->account; 

        $input = [
            'amount' => $amount,
            'po' => $po, 
            'account' =>$account       
        ];

        $rules = [
            'amount'    => 'required|numeric',
            'po' => 'required',
            'account' => 'required'  
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return "failure";
        }

        if($status=='update')
            $order = orders::where('id',$id)->update(['poTotalAmount'=>$amount, 'poNumber'=>$po, 'account_id'=>$account]);
        else
            $order = orders::where('id',$id)->update(['poTotalAmount'=>$amount, 'poNumber'=>$po, 'status'=>'processing', 'account_id'=>$account]);
        
        if($order)
            return "success";
        else
            return "failure";
        }
    }



    public function assign()
    {        

        if(auth()->user()->role==1)
        {
            $orders = orders::select()->where('status','unshipped')->where('assigned',0)->paginate(100);
            $users = User::all(); 
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
            
            $orders = orders::select()->where('status','unshipped')->whereIn('storeName',$strArray)->where('assigned',0)->paginate(100);

            $users = User::where('manager_id',auth()->user()->id)->get(); 
            
        }
    
        else
        {
            $orders = array(); 
        }

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->max('totalAmount'));
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
        $flags = flags::all(); 
        return view('orders.assign',compact('flags','orders','users','stores','states','maxAmount','minAmount','maxPrice'));
    }

    public function assignOrder(Request $request)
    {
        $rows  = $request->rows; 
        $userId = $request->user;
        
        foreach($rows as $order)
        {
            $id = explode('-',$order)[1];
            $uId = $userId;
            if($id =='all')
                continue;
            $upd = orders::where('id',$id)->update(['assigned'=>1, 'uid'=>$userId]);
        }
        return "success";
    }

    public function orderFlag($id, $flag)
    {
        $orders= orders::where('id',$id)->update(['flag'=>$flag]);
        return redirect()->back();        
    }

    public function orderFlagRoute($route, $id, $flag)
    {
        $orders= orders::where('id',$id)->update(['flag'=>$flag]);
        return redirect()->route($route);        
    }

  public static function getIranTime($date)
    {
        
        $datetime = new \DateTime($date);        
        
        return $datetime->format('m/d/Y H:i:s');
        
    }
    public function newOrders()
    {  
            if(auth()->user()->role==1)
            {
                $orders = orders::select()->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->orderBy('date', 'ASC')->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::select()->where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
                
            }
        
            else
            {
                $orders = orders::select()
                ->where('status','unshipped')
                ->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')
                ->where('uid',auth()->user()->id)
                ->orderBy('date', 'ASC')
                ->paginate(100);
            }

        $stores = accounts::select(['id','store'])->get();
        $states = states::select()->distinct()->get();
        
        $maxAmount = ceil(orders::where('status','unshipped')->where('flag', '!=' , '8')->where('flag', '!=' , '9')->where('flag', '!=' , '10')->max('totalAmount'));
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
            
        $flags = flags::all(); 
        return view('orders.new',compact('flags','orders','stores','states','maxAmount','minAmount','maxPrice'));
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
                    $total = $total + $price->ebayPrice;   
                }

            else
                $total = $total + $price->lowestPrice;
        }

        return $total;

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

    public function autoFlag($id)
    {
       
        try
        {
            $details = order_details::where('order_id',$id)->get();             
            $flag=0;

            foreach($details as $detail)
            {      
                $temp = blacklist::where('sku',$detail->SKU)->get()->first();
                
                if(empty($temp))
                    continue;
                
                if(trim($temp->reason)=='Qty Limit')
                    $flag='2';
                if(trim($temp->reason)=='Unavailable')
                    $flag='3';
                if(trim($temp->reason)=='Delay')
                    $flag='6';
                if(trim($temp->reason)=='Wrong Info')
                    $flag='6';
                $flg = flags::where('name',trim($temp->reason))->get()->first(); 
                if(!empty($flg))
                    $flag = $flg->id;
                    
                $update = orders::where('id',$id)->update(['flag'=>$flag]);
            }
            
           
        }
        catch(\Exception $ex)
        {
            
        }
    }

    
    public function conversions()
    {       
        $credits = $this->getCredits(); 
        
        $count =0; 

        if(auth()->user()->role==1)            
        {
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')            
            ->select(['conversions.*','orders.*'])->where('converted',true)
            ->where(function($test){
                $test->whereNull('conversions.status');
                $test->orWhere('conversions.status','!=','Delivered');
            })
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')->paginate(100);

            $count = orders::select()->where('converted',true)
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
            
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->where(function($test){
                $test->whereNull('conversions.status');
                $test->orWhere('conversions.status','!=','Delivered');
            })
            ->orderBy('orders.status', 'DESC')->paginate(100);

            $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 

            
        }
            
        else
            $orders = array();
        

        return view('orders.conversions',compact('orders','credits','count'));
    }

    public function conversions2()
    {               
        
        $count =0; 

        if(auth()->user()->role==1)            
        {
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')            
            ->select(['conversions.*','orders.*'])->where('converted',true)            
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('orders.status', 'DESC')->paginate(100);
            
         
            $count = orders::select()->where('converted',true)
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
            
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
          
            ->orderBy('orders.status', 'DESC')->paginate(100);

            $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 

            
        }
            
        else
            $orders = array();
                
        return view('orders.conversions2',compact('orders','count'));
    }

    public function upsConversions()
    {               
        
        $count =0; 

        if(auth()->user()->role==1)            
        {
            $orders = orders::where('isBCE',true)            
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->orderBy('orders.status', 'ASC')->paginate(100);
            
         
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
            }) 
            ->orderBy('orders.status', 'ASC')->paginate(100);            

            $count = orders::select()->where('isBCE',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 

            
        }
            
        else
            $orders = array();

        $stores = accounts::all();         
        
        $startDate = orders::where('isBCE',true)->min('date');
        $endDate = orders::where('isBCE',true)->max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;

        return view('orders.upsconversions',compact('orders','count','stores','dateRange'));
    }

    public function conversionssync()
    {            
        $client = new client(); 
        
        $endPoint = env('BCE_SYNC_TOKEN', '');
        
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
            return redirect()->route('conversions2');
        }
        
        $statusCode = $response->getStatusCode();
            
        
        if($statusCode!=200)
        {
            Session::flash('error_msg', __('Trackings Syncing Failed'));
            return redirect()->route('conversions2');
        }
                    
        $body = json_decode($response->getBody()->getContents());
        
        Session::flash('success_msg', $body->count. __(' Trackings Synced'));
        return redirect()->route('conversions2');        
    }

    public function deliveredConversions()
    {       
        $credits = $this->getCredits(); 
        
        $count =0; 

        if(auth()->user()->role==1)            
        {
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->where('conversions.status','Delivered')
            ->orderBy('orders.status', 'DESC')->paginate(100);

            $count = orders::select()->where('converted',true)
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
            
            $orders = orders::leftJoin('conversions','orders.id','conversions.order_id')->select(['conversions.*','orders.*'])->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('orders.status','processing');
                $test->orWhere('orders.status','shipped');
            }) 
            ->where('conversions.status','Delivered')
            ->orderBy('orders.status', 'DESC')->paginate(100);

            $count = orders::select()->where('converted',true)->whereIn('storeName',$strArray)
            ->where(function($test){
                $test->where('status','processing');                
            })->count(); 

            
        }
            
        else
            $orders = array();
        

        return view('orders.deliveredConversions',compact('orders','credits','count'));
    }

    



    public function cancelledOrders()
    {
        if(auth()->user()->role==1)
        {
            $orders = orders::select()->where('status','cancelled')->orderBy('date', 'ASC')->paginate(100);
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
            
            $orders = orders::select()->where('status','cancelled')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
            
        }
    
        else
        {
            $orders = orders::select()
            ->where('status','cancelled')
            ->where('uid',auth()->user()->id)
            ->orderBy('date', 'ASC')
            ->paginate(100);
        }

        foreach($orders as $order)
        {

            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        return view('orders.cancelled',compact('orders'));
    }

    public function processedOrders()
    {
        
        if(auth()->user()->role==1)
            $orders = orders::select()->where('status','processing')->orderBy('date', 'ASC')->paginate(100);

        elseif(auth()->user()->role==2)
            {
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();

                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                
                $orders = orders::select()->where('status','processing')->whereIn('storeName',$strArray)->orderBy('date', 'ASC')->paginate(100);
            }
        
        else
            
            $orders = orders::select()->where('status','processing')->where('uid',auth()->user()->id)->orderBy('date', 'ASC')->paginate(100);
        
            foreach($orders as $order)
            {
    
                $order->shippingPrice = $this->getTotalShipping($order->id);
            }
        return view('orders.processed',compact('orders'));
    }


   public function shippedOrders()
    {
       
        if(auth()->user()->role==1)
        {
            $orders = orders::select()->where('status','shipped')
            ->whereNotNull('poNumber')
            ->whereNotNull('trackingNumber')
            ->orderBy('date', 'ASC')->paginate(100);
        }

        elseif(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }
            
            $orders = orders::select()->where('status','shipped')
            ->whereIn('storeName',$strArray)
            ->whereNotNull('poNumber')
            ->whereNotNull('trackingNumber')
            ->orderBy('date', 'ASC')->paginate(100);
            
        }
    
        else
        {
            $orders = orders::select()->where('status','shipped')
            ->where('uid',auth()->user()->id)
            ->whereNotNull('poNumber')
            ->whereNotNull('trackingNumber')
            ->orderBy('date', 'ASC')->paginate(100);            
        }
     

        foreach($orders as $order)
        {

            $order->shippingPrice = $this->getTotalShipping($order->id);
        }

        return view('orders.shipped',compact('orders'));
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

    public function insertConversionRecord($orderId, $shipmentId,$trackingNumber)
    {
        $order = orders::where('id',$orderId)->get()->first();
        
        if(empty($order) || empty($order->id))
            return "Error";   

        $insert = conversions::updateOrCreate(
            ['order_id'=>$orderId],    
            ['carrier'=>'Amazon','tracking'=>$trackingNumber,'shipmentLink'=>"https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&itemId=&orderId=".$order->poNumber."&packageIndex=0&shipmentId=".$shipmentId]
        );
        
      
        return;
    }

    public function getBceResponse($orderId, $shipmentId,$trackingNumber, $channel, $type)
    {
        $endPoint = env('BCE_URL', '');
        
        $token = env('BCE_TOKEN', '');
        
        $client = new client();         
        
        $order = orders::where('id',$orderId)->get()->first();
        
        if(empty($order) || empty($order->id))
            return "Error";        

        $data = array(); 
    
        $data["Address"]["Name"] = $order->buyerName;
        $data["Address"]["Line1"] = $order->address1;
        $data["Address"]["Line2"] = $order->address2;
        $data["Address"]["City"] =  $order->city;
        $data["Address"]["State"] =  $order->state;
        $data["Address"]["ZIPCode"] =  $order->postalCode;

        if($type==1)
        {
            $data["TrackingLink"] = "https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&itemId=&orderId=".$order->poNumber."&packageIndex=0&shipmentId=".$shipmentId;
        }
        else
        {
            $data["TrackingLink"] = "https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&orderId=".$order->poNumber."&packageIndex=0&itemId=".$shipmentId;
        }

        $data["TrackingNumber"] = $trackingNumber;
        $data["SaleChannel"] = $channel;

        
        $body2 = "[".json_encode($data)."]"; 
    
        $response = $client->request('POST', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'bearer '.$token],
            'body' => $body2           
        ]);    
        

        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "Error";
                    
        $body = json_decode($response->getBody()->getContents());       
        
        foreach($body as $bd)
        {
            if(empty($bd->ErrorMessage))
                {
                    if(!empty($bd->ProxyTrackingReference))
                        return $bd->ProxyTrackingReference;
                    else
                        return "Error";
                }
            else
                return "Error";
        }

        return "Error";

    }

    public function getBceResponseAlt($orderId, $shipmentId,$trackingNumber, $channel)
    {
        
        $order = orders::where('id',$orderId)->get()->first();
        
        $client = new client();

        if(empty($order) || empty($order->id))
            return "Error";
            
        try{
        $trackingLink = "https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&itemId=&orderId=".$order->poNumber."&packageIndex=0&shipmentId=".$shipmentId;
        
        $htmlCode = file_get_contents($trackingLink);    
        
        $data = array(); 

  
        $data["TrackingUrl"] = $trackingLink;
        $data["TrackingPageHtml"] = $htmlCode;
        $data["__RequestVerificationToken"] = 'CfDJ8JV8MBF4p35HlJ7SgO0k5mVpAD_ej5Nf9pEtNNDa0p8uTJnGOkY8khSlk9DnK95ANPSKhtJLVNTuH5VFfyG5NXRjhcw7FH8MMuj4noavNeiatSnXYjlcrgqHYKhq7teYYDBLBmxAru-yop4Id6NHoe0';

        $endPoint = 'https://bluecare.express/Tracking/AddInfo';

        $promise = $client->requestAsync('POST', $endPoint,
        [
            'headers' => [],
            'form_params' => $data,
            'timeout' => 7
        ]);    
       
            
        $promise->then(
                function (ResponseInterface $res) {
                   return $res;                                     
                },
                function (RequestException $e) {
                   return $e;
                }
            );
    
        $response1 = $promise->wait();

        $status = $response1->getStatusCode();  
        
        
        
        if($status!=200)
            return "Error";
                
        
            $body = $response1->getBody();

            $doc = new \DOMDocument();
            
            $internalErrors = libxml_use_internal_errors(true);
            
            $doc->loadHTML($body);
                        
            libxml_use_internal_errors($internalErrors);
                        
            $finder = new \DomXPath($doc);
            
            $nodes = $finder->query("//*[contains(@class, 'alert')]");
            
            foreach($nodes as $node)
            {
                $val= $node->nodeValue;
                if(stripos($val, 'The Bluecare Express tracking number is')!== false)
                {
                    $tracking = str_replace('The Bluecare Express tracking number is','',$val);
                    
                     return trim(str_replace('.','',$tracking));
                }
            }

            return "Error";
        }
        catch(\Exception $ex)
        {
            
            return "Error";
        }
    }

    public function newBCE()
    {
        
        $prod = products::with('orderDetails')->get(['id']);
        echo json_encode($prod);
        
    }



    public function getManualBce(Request $request)
    {

        $orderId = $request->orderId;
        $shipmentId= $request->shipmentId;
        $itemId= $request->itemId;
        $trackingNumber = $request->trackingNumber;
        $channel = $request->channel;
        $carrier = $request->carrier;

        $endPoint = env('BCE_URL', '');
        
        $token = env('BCE_TOKEN', '');
        
        $client = new client(); 
        
        $order = orders::where('id',$orderId)->get()->first();
        
        if(empty($order) || empty($order->id))
            return "Error";

        $data = array(); 

        $data["Address"]["Name"] = $order->buyerName;
        $data["Address"]["Line1"] = $order->address1;
        $data["Address"]["City"] =  $order->city;
        $data["Address"]["State"] =  $order->state;
        $data["Address"]["ZIPCode"] =  $order->postalCode;
        $data["TrackingLink"] = "https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&itemId=".$itemId."&orderId=".$order->poNumber."&packageIndex=0&shipmentId=".$shipmentId;
        $data["TrackingNumber"] = $trackingNumber;
        $data["SaleChannel"] = $channel;

        
        $body2 = "[".json_encode($data)."]"; 
    
        $response = $client->request('POST', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'bearer '.$token],
            'body' => $body2           
        ]);    
        

        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "Error";
                    
        $body = json_decode($response->getBody()->getContents());       
        
        foreach($body as $bd)
        {
            if(empty($bd->ErrorMessage))
                {
                    if(!empty($bd->ProxyTrackingReference))
                        {
                            try{
                                $car = carriers::where('name',$carrier)->get()->first();
                                $order = orders::where('id',$order->id)->update(['carrierName'=>$car->id, 'trackingNumber'=>$trackingNumber, 'newTrackingNumber'=>$bd->ProxyTrackingReference, 'converted'=>true]);                    
                                }
                                catch(\Exception $ex)
                                {
                                    return "Error";
                                }
                            return $bd->ProxyTrackingReference;
                        }
                    else
                        return "Error";
                }
            else
                return "Error";
        }

        return "Error";

    }

    public function getManualBceAlt(Request $request)
    {

        $orderId = $request->orderId;
        $shipmentId= $request->shipmentId;
        $trackingNumber = $request->trackingNumber;
        $channel = $request->channel;
        $carrier = $request->carrier;

        $order = orders::where('id',$orderId)->get()->first();
        
        $client = new client();

        if(empty($order) || empty($order->id))
            return "Error";
            
        try{
        $trackingLink = "https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&itemId=&orderId=".$order->poNumber."&packageIndex=0&shipmentId=".$shipmentId;
        
        $htmlCode = file_get_contents($trackingLink);    

        $data = array(); 

  
        $data["TrackingUrl"] = $trackingLink;
        $data["TrackingPageHtml"] = $htmlCode;
        $data["__RequestVerificationToken"] = 'CfDJ8JV8MBF4p35HlJ7SgO0k5mVpAD_ej5Nf9pEtNNDa0p8uTJnGOkY8khSlk9DnK95ANPSKhtJLVNTuH5VFfyG5NXRjhcw7FH8MMuj4noavNeiatSnXYjlcrgqHYKhq7teYYDBLBmxAru-yop4Id6NHoe0';

        $endPoint = 'https://bluecare.express/Tracking/AddInfo';

        $promise = $client->requestAsync('POST', $endPoint,
        [
            'headers' => [],
            'form_params' => $data,
            'timeout' => 7
        ]);    
       
            
        $promise->then(
                function (ResponseInterface $res) {
                   return $res;                                     
                },
                function (RequestException $e) {
                   return $e;
                }
            );
    
        $response1 = $promise->wait();

        $status = $response1->getStatusCode();  
        

        if($status!=200)
            return "Error";
                
        
            $body = $response1->getBody();

            $doc = new \DOMDocument();
            
            $internalErrors = libxml_use_internal_errors(true);
            
            $doc->loadHTML($body);
                        
            libxml_use_internal_errors($internalErrors);
                        
            $finder = new \DomXPath($doc);
            
            $nodes = $finder->query("//*[contains(@class, 'alert')]");
            
            foreach($nodes as $node)
            {
                $val= $node->nodeValue;
                if(stripos($val, 'The Bluecare Express tracking number is')!== false)
                {
                    $tracking = str_replace('The Bluecare Express tracking number is','',$val);
                    
                     return trim(str_replace('.','',$tracking));
                }
            }

            return "Error";
        }
        catch(\Exception $ex)
        {
            return "Error";
        }
    }

    
    public function getShipment($orderId)
    {
        $client = new client(); 

        $accounts = gmail_accounts::get();

        foreach($accounts as $account)
        {       
            $response = $client->request('GET', $account->url,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'query' => ['orderId' => $orderId, 'orderType'=>$account->accountType]         
            ]); 

            
            $body = json_decode($response->getBody()->getContents());
            
            if($body->status!=200)
                continue;
                        
            if(!empty($body->shipmentId))
                return $body->shipmentId;

            else
                continue; 
            
        }   
        
        return "Error";
    }

    public function forwardEmail($orderId)
    {
        try{
        $client = new client(); 

        $accounts = gmail_accounts::get();

        foreach($accounts as $account)
        {           
                  
            $promise = $client->requestAsync('GET', $account->bceurl,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'query' => ['orderId' => $orderId],
                'timeout' => 7       
            ]); 

            
            $promise->then(
                function (ResponseInterface $res) {
                return $res;                                     
                },
                function (RequestException $e) {
                return $e;
                }
            );
    
            $response1 = $promise->wait();

            $body = json_decode($response1->getBody()->getContents());
                        
            if($body->status!=200)
                continue;
                        
            else
                return "success";            
            
        }   
        
        return "Error";
    }
        catch(\Exception $ex)
        {
            return "Error";
        }
    }

    
    public function details($id) 
    {
        
        $order = orders::where('id',$id)->get()->first();        
        
        if(empty($order))
            abort(404);

        $carrier = carriers::where('id',$order->carrierName)->select(['name'])->get()->first(); 
        
        if(auth()->user()->role==2)
        {
            $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
            $strArray  = array();

            foreach($stores as $str)
            {
                $strArray[]= $str->store;
            }

            $order = orders::where('id',$id)->whereIn('storeName',$strArray)->get()->first();

            if(empty($order))
                abort(404);
        }
        else
        {
            if(!auth()->user()->role==1 && $order->uid!=auth()->user()->id)
                    abort(404);
        }
            
        
        if(!empty($carrier))
            $order->carrier =  $carrier->name;
        else
            $order->carrier="";

        $details = order_details::select()->where('order_id',$id)->paginate(500);

        foreach($details as $detail)        
        {
            $c = products::where('asin',$detail->SKU)->select(['image','upc'])->first();

            if(!empty($c))
            {
                $detail->image = $c->image;
                $detail->upc = $c->upc;
                $detail->src = 'Amazon';
            }
            else
            {
                $d = ebay_products::where('sku',$detail->SKU)->select(['primaryImg','productIdType','productId'])->first();

                if(!empty($d))
                {
                    $detail->image = $d->primaryImg;
                    if($d->productIdType=='UPC')
                        $detail->upc = $d->productId;
                    $detail->src = 'Ebay';
                }   
            }
                
        }

        $carriers = carriers::all();
        $accounts = gmail_accounts::all(); 
        return view('orders.details',compact('details','order','carriers','accounts'));
    }

    
}
