<?php
use App\ebay_trackings;
use App\carriers;
use App\walmart_products;
use App\orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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