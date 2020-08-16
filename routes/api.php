<?php
use App\ebay_trackings;
use App\carriers;
use App\walmart_products;
use App\orders;
use App\categories; 
use App\conversions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
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

        $insert = orders::where('sellOrderId',$record['sellOrderId'])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>$record['poTotalAmount'],
        'poNumber'=>$record['poNumber'],
        'itemId'=>$record['itemId'],
        'afpoNumber'=>$record['afpoNumber'],
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

Route::post('samuel_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;

    foreach($records as $record)
    {        
        if(empty(trim($record['poNumber'])))
            continue;

        $insert = orders::where('sellOrderId',$record['sellOrderId'])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>floatval($record['poTotalAmount']) * 0.93,
        'poNumber'=>$record['poNumber'],        
        'afpoNumber'=>$record['afpoNumber'],
        'account_id'=>'Samuel',        
        'status'=>'processing'
        ]);


        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
});

Route::post('jonathan_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;

    foreach($records as $record)
    {        
        if(empty(trim($record['poNumber'])))
            continue;

        $insert = orders::where('sellOrderId',$record['sellOrderId'])
        ->whereNull('poNumber')
        ->update([
        'poTotalAmount'=>$record['poTotalAmount'],
        'poNumber'=>$record['poNumber'],        
        'afpoNumber'=>$record['afpoNumber'],
        'account_id'=>'Jonathan',        
        'status'=>'processing'
        ]);


        if($insert)
            $success++;
    }

    return response()->json([
        'count' => $success
    ],201);
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

Route::post('bce_update', function(Request $request) {
    
    $success=0;
    $records = $request->data;

    foreach($records as $record)
    {        
        if(empty(trim($record['sellOrderId'])))
            continue;

        $order = orders::where('sellOrderId',$record['sellOrderId'])->get()->first(); 

        if(empty($order)|| $order->status=='shipped')
            continue;
        
        if(!empty($record['tracking']))
        {
            $bceCarrier = carriers::where('name','UPS')->get()->first();

            $insert = orders::where('sellOrderId',$record['sellOrderId'])        
            ->update([
            'upsTrackingNumber'=>$record['tracking'],            
            'carrierName'=>$bceCarrier->id
            ]);
                        
            if($insert)
                    $success++;
                                
        }

        
    }

    return response()->json([
        'count' => $success
    ],201);
});

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


