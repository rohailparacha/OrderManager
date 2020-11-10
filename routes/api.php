<?php
use App\ebay_trackings;
use App\carriers;
use App\walmart_products;
use App\orders;
use App\categories; 
use App\flags;
use App\conversions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\temp_trackings;
use GuzzleHttp\Client;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('ebay_trackings', function(Request $request) {
    
    $success=0;
    $trackings = $request->data;
    
    foreach($trackings as $tracking)
    {        
        $insert = ebay_trackings::updateOrCreate(
            ['orderNumber'=>$tracking['orderNumber']],    
            ['trackingNumber'=>$tracking['trackingNumber'],'carrierName'=>$tracking['carrierName']]
        );

        if($insert)
            $success++;
    }

    return response()->json("Inserted / Updated ".$success." records", 201);
    
});

Route::post('update_bce', function(Request $request) {
    
    $success=0;
    $trackings = $request->data;

    foreach($trackings as $tracking)
    {        
        
        $orderNumber = $tracking['orderNumber'];
        $carrierName = $tracking['carrierName'];
        $trackingNumber = $tracking['trackingNumber'];
        $origTracking = $tracking['origTracking'];
        
        $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 

        if(!empty($bceCarrier))
        {
            $order = orders::where('poNumber','LIKE','%'.$orderNumber.'%')->update(['carrierName'=>$bceCarrier->id, 'trackingNumber'=>$origTracking, 'newTrackingNumber'=>$trackingNumber, 'converted'=>true]);
        }
        
        if($order)
            $success++;
    }
    
    return response()->json("Updated ".$success." records", 201);
    
});

Route::post('autofulfill_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;
    
    foreach($records as $record)
    {        
        if(empty(trim($record['poNumber'])))
            continue;

        $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>$record['poTotalAmount'],
        'poNumber'=>$record['poNumber'],
        'itemId'=>$record['itemId'],
        'afpoNumber'=>$record['afpoNumber'],
        'trackingLink'=>$record['trackingLink'],
        'account_id'=>'Cindy',
        'status'=>'processing'
        ]);


        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});

Route::post('vaughn_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;

    foreach($records as $record)
    {        
        if(empty(trim($record['poNumber'])))
            continue;

        $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>floatval($record['poTotalAmount']),
        'poNumber'=>$record['poNumber'], 
        'itemId'=>$record['itemId'],      
        'afpoNumber'=>$record['afpoNumber'],
        'trackingLink'=>$record['trackingLink'],
        'account_id'=>'Vaughn',        
        'status'=>'processing'
        ]);


        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});



Route::post('jonathan2_update', function(Request $request) {
    
    $success=0;
    $recordsData = $request->data;

    $flagNum = flags::where('name','Jonathan Cancelled')->get()->first(); 
    
    foreach($recordsData as $record)
    {      
        
        if(trim(strtolower($record['status']))=='cancelled')
        {
         
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])            
            ->update([
            'flag'=> $flagNum->id,
            ]);
            
        }
        if(empty(trim($record['poNumber'])))
        {
            if($insert)
                $success++;
            continue;
        }
            
        
        try{

        if(empty(trim($record['trackingNumber'])) || empty(trim($record['carrier'])) )
        {
               
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
            ->where('status','!=','shipped')
            ->update([
            'poTotalAmount'=>$record['poTotalAmount'],
            'poNumber'=>$record['poNumber'],        
            'afpoNumber'=>$record['afpoNumber'],
            'trackingLink'=>$record['trackingLink'],        
            'account_id'=>'Jonathan2',        
            'status'=>'processing',
            'itemId'=>$record['itemId']
            ]);
            
        }
        else
        {
            
        $carrier = carriers::where('name',$record['carrier'])->get()->first(); 
                            
        if(empty($carrier))
        {
            $carrier = carriers::where('alias','like','%'.$record['carrier'].'%')->get()->first(); 
        }

        if(empty($carrier))
            continue; 
               
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
            ->where('status','!=','shipped')
            ->update([
            'poTotalAmount'=>$record['poTotalAmount'],
            'poNumber'=>$record['poNumber'],        
            'afpoNumber'=>$record['afpoNumber'],
            'trackingNumber'=>$record['trackingNumber'], 
            'carrierName'=>$carrier->id,        
            'trackingLink'=>$record['trackingLink'],        
            'account_id'=>'Jonathan2',        
            'status'=>'processing',
            'itemId'=>$record['itemId']
            ]);
            
        }

        }
        catch(\Exception $ex)
        {
                
        }

        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});


Route::post('jonathan_update', function(Request $request) {
    
    $success=0;
    $recordsData = $request->data;

    $flagNum = flags::where('name','Jonathan Cancelled')->get()->first(); 
    
    foreach($recordsData as $record)
    {      
        
        if(trim(strtolower($record['status']))=='cancelled')
        {
         
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])            
            ->update([
            'flag'=> $flagNum->id,
            ]);
            
        }
        if(empty(trim($record['poNumber'])))
        {
            if($insert)
                $success++;
            continue;
        }
            
        
        try{

        if(empty(trim($record['trackingNumber'])) || empty(trim($record['carrier'])) )
        {
               
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
            ->where('status','!=','shipped')
            ->update([
            'poTotalAmount'=>$record['poTotalAmount'],
            'poNumber'=>$record['poNumber'],        
            'afpoNumber'=>$record['afpoNumber'],
            'trackingLink'=>$record['trackingLink'],        
            'account_id'=>'Jonathan',        
            'status'=>'processing',
            'itemId'=>$record['itemId']
            ]);
            
        }
        else
        {
            
        $carrier = carriers::where('name',$record['carrier'])->get()->first(); 
                            
        if(empty($carrier))
        {
            $carrier = carriers::where('alias','like','%'.$record['carrier'].'%')->get()->first(); 
        }

        if(empty($carrier))
            continue; 
            
            
            
               
            $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
            ->where('status','!=','shipped')
            ->update([
            'poTotalAmount'=>$record['poTotalAmount'],
            'poNumber'=>$record['poNumber'],        
            'afpoNumber'=>$record['afpoNumber'],
            'trackingNumber'=>$record['trackingNumber'], 
            'carrierName'=>$carrier->id,        
            'trackingLink'=>$record['trackingLink'],        
            'account_id'=>'Jonathan',        
            'status'=>'processing',
            'itemId'=>$record['itemId']
            ]);
            
        }

        }
        catch(\Exception $ex)
        {
                
        }

        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});



Route::post('yaballe_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;

    foreach($records as $record)
    {        
        if(empty(trim($record['poNumber'])))
            continue;

        $insert = orders::where('sellOrderId',explode('--',$record['sellOrderId'])[0])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>$record['poTotalAmount'],
        'poNumber'=>$record['poNumber'],        
        'afpoNumber'=>$record['afpoNumber'],
        'trackingLink'=>$record['trackingLink'],
        'account_id'=>'Yaballe',        
        'status'=>'processing',
        'itemId'=>$record['itemId']
        ]);


        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});

Route::post('order_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;
    

    foreach($records as $record)
    {        
        if(empty(trim($record['sellOrderId'])))
            continue;
        
        $carrier = carriers::where('name',$record['carrier'])->get()->first();

        if(empty($carrier))
        {
            $carrier = carriers::where('alias','like','%'.$record['carrier'].'%')->get()->first(); 
        }

        if(empty($carrier))
            continue; 

        $insert = orders::where('sellOrderId',$record['sellOrderId'])
        ->update([
        'trackingNumber'=>$record['trackingNumber'],
        'carrierName'=>$carrier->id
        ]);

        $order = orders::where('sellOrderId',$record['sellOrderId'])->get()->first();

        if(empty($order))
            continue;

        if($record['status']=='cancelled' || $record['status']=='delayed')
        {            
            $insert = cancelled_orders::updateOrCreate(
                ['order_id'=>$order->id,],    
                ['status'=>$record['status']]
            );
        }


        if($insert)
        {
            temp_trackings::where('sellOrderId',$record['sellOrderId'])->update(['status'=>'success']);
            $success++;
        }
            
    }

    return response()->json([
        'count' => $success
    ],201);
});



Route::get('fetch_orders', function(Request $request) {
    $trackings = temp_trackings::where('status','pending')->get();    

    return response()->json([
        'trackings' => $trackings
    ],200);
});

Route::post('walmart_product', function(Request $request) {
    
    $success=0;
    $products = $request->data;
    
    foreach($products as $product)
    {     
        $insert = walmart_products::updateOrCreate(
            ['productId'=>$product['attributeValue']],    
            ['name'=>$product['title'],'productIdType'=>$product['attributeType'],'seller'=>$product['sellerName'],'link'=>$product['productURL'],'image'=>$product['imageURL'],'price'=>$product['price']]
        );

        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
    
});

Route::get('conversions', function(Request $request) {
    
    $success=0;
  $conversions = conversions::leftJoin('orders','conversions.order_id','orders.id')
    ->select(['conversions.*','orders.sellOrderId','orders.buyerName','orders.address1','orders.address2','orders.city','orders.state','orders.postalCode','orders.country'])
        ->where(function($test){
        $test->whereNull('conversions.status');
        $test->orWhere('conversions.status','!=','Delivered');
    })->get(); 

    return response()->json([
        'conversions' => $conversions
    ],200);
    
});



Route::post('bce_update','UPSController@index');

Route::post('updateConversion', function(Request $request) {
    
    $success=0;
    
    $bceCarrier = carriers::where('name','Bluecare Express')->get()->first(); 

    $order = orders::where('sellOrderId',$request->sellOrderId)->get()->first();

    if(!$order->converted)    
    {
        $insert = orders::where('sellOrderId',$request->sellOrderId)
        ->update([
        'trackingNumber'=>$request->trackingNumber,
        'newTrackingNumber'=>$request->newTrackingNumber,
        'converted'=>true,                
        'carrierName'=>$bceCarrier->id,
        'of_bce_created_at'=>Carbon::now()
        ]);
        
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
        $temp['newTrackingNumber'] = $request->newTrackingNumber;              
        

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

    $insert = conversions::where('order_id',$order->id)->update(['status'=>$request->status]); 
    return response()->json([
        'status' => 'success'
    ],201);
});

Route::post('fullShipment', function(Request $request) {
        
     
        $data = array(); 
    
        $data["Address"]= $request->Address;
        $data["SaleChannel"]= $request->SaleChannel;
        $data["TrackingLink"]= $request->TrackingLink;
        $data["TrackingNumber"]= $request->TrackingNumber;
        $data["TrackingPageHtml"]= $request->TrackingPageHtml;
        
        $client = new client(); 

        $endPoint ="https://www.bluecare.express/api/FullTrackingPage";
        
        $token = '0ZVCBZNDV5ohLQSbIeTOzSkGN9RFtUtLS9Z0H8vQK7RMbB82';

        $response = $client->request("POST", $endPoint,
        [
            "headers" => ["Content-Type" => "application/json", "Accept" => "application/json", "Authorization" => "bearer ".$token],
            "body" => json_encode($data)
        ]);    
        

        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
            return "Error";
  
        $body = json_decode($response->getBody()->getContents());       
                
        return response()->json([
            'response' => $body
        ],200);
      
});


