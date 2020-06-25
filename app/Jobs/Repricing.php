<?php

namespace App\Jobs;
use App\categories;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\strategies;
use App\logs;
use App\products;
use App\accounts;
use App\Jobs\Repricing;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File; 
 use Redirect;
use Response;
use Image;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class Repricing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $status;
    public $collection;
    private $recordId; 
    
    public function __construct($collection, $status)
    {
        //
        $this->collection = $collection; 
        $this->status = $status; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {                
        $status = $this->status; 

        $collection = $this->collection; 
        
        if($status=='new')
            $this->newProducts($collection);
        else
            $this->repricing();        
       
        $this->recordId=0;
    }

    public function repricing()
    {
      
        $this->recordId = logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress']);
        
        $products = products::all();
        
        $strategies = strategies::all(); 
        
        
        $stArr = array();
        foreach($strategies as $strategy)
        {
            $stArr[$strategy->id] = $strategy->code;
        }

        $dataArray = array(); 
        foreach($products as $product)
        {
            try{
            $asin = $product->asin;
            $account = $product->account;
            $strategy = $stArr[$product->strategy_id];
            $action = 'add';
            $dataArray[]= ['asin'=>$asin, 'account'=>$account, 'strategy'=>$strategy, 'action'=>$action];    
            }
            catch(\Exception $ex)
            {

            }
            
        }
        
        
        $collection = collect($dataArray);

        $temp = $collection->chunk(5000);
        foreach($temp as $col)
        {
            $this->getProducts($col);
        }        
        
        logs::where('id',$this->recordId)->where('status','In Progress')->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);

    }

    public function newProducts($collection)
    {

        $this->recordId = logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress']);

        $temp = $collection->chunk(5000);

        foreach($temp as $col)
        {
            $this->getProducts($col);
        }        
       
        logs::where('id',$this->recordId)->where('status','In Progress')->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);
        
    }

    public function getProducts($collection)
    {
        $this->deleteIdentifiers();         
        $iteration = 0;                 

        $temp = $collection->chunk(1000);
        
        foreach($temp as $col)
        {
           
            foreach($col as $product)
            {                
                
                if(strtolower($product['action'])=='delete')
                {
                    $pId = products::where('asin',$product['asin'])->get()->first();                    
                    
                    if(!empty($pId))
                    {
                        $prod  = $this->deleteProductFile($pId->id);
                        if($prod)
                        {
                            logs::where('id',$this->recordId)->increment('identifiers');
                            logs::where('id',$this->recordId)->increment('successItems');
                        }
                            
                        else
                        {
                            
                            logs::where('id',$this->recordId)->increment('identifiers');
                            logs::where('id',$this->recordId)->increment('errorItems');
                        
                        }
                            
                    }
                    else
                    {
                        logs::where('id',$this->recordId)->increment('identifiers');
                        logs::where('id',$this->recordId)->increment('errorItems');
                    }
                    
                    
                    
                }

                elseif(strtolower($product['action'])=='modify')
                {
                    $strategy = strategies::where('code',$product['strategy'])->get()->first();                
                    
                    if(empty($strategy))
                    continue; 

                    try{
                    
                        $productUpdate = products::where('asin',$product['asin'])->get()->first();

                        if($strategy->type==1)
                            $price = ($productUpdate->lowestPrice + $strategy->value) / (1 - $strategy->breakeven/100);
                        else
                            $price = ($productUpdate->lowestPrice * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
                        
                        if($productUpdate->lowestPrice==0)                            
                            $prod = products::where('asin',$product['asin'])->update(['strategy_id'=>$strategy->id]);
                        else
                            $prod = products::where('asin',$product['asin'])->update(['strategy_id'=>$strategy->id, 'price'=>$price]);
                            
                        if($prod)
                        {
                            logs::where('id',$this->recordId)->increment('identifiers');
                            logs::where('id',$this->recordId)->increment('successItems');
                        }
                            
                        else
                        {
                            
                            logs::where('id',$this->recordId)->increment('identifiers');
                            logs::where('id',$this->recordId)->increment('errorItems');
                        
                        }

                    }   
                    catch(\Exception $ex)
                    {

                    }
                }
            }

        

            $client = new client(); 
            
            $endPoint = "https://v3.synccentric.com/api/v3/products";
            
            $token = env('SC_TOKEN', '');

            $campaign = env('SC_CAMPAIGN','');

            $data = array(); 

            $data["campaign_id"] = $campaign;
            
            $identifiers = array();
            foreach($col as $product)
            {
                if(strtolower($product['action'])=='add')
                {
                    $identifier['identifier']= $product['asin'];
                    $identifier['type']= 'asin'; 
                    $identifiers[] = $identifier;
                }            
            }
    
       
            if(!empty($identifiers) && count($identifiers)>0)
            {
                $data["identifiers"] = $identifiers;
                $iteration++;
                try{
                    $promise = $client->requestAsync('POST', $endPoint,
                    [
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                    'body' => json_encode($data)           
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

                }
                catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents(); 
                    logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
                }                
                        
                
                
            }
          
        }

        if( $iteration>0)
        {            
            $this->searchProducts($collection); 
        }
            
       
    }

    public function deleteProductFile($id)
    {        

        $temp = products::where('id','=',$id)->delete();        
        $sc_id =products::where('id','=',$id)->get()->first();

        try{
            $file1 = public_path('images/amazon/' . $sc_id->asin.'.jpg');            
            $files = array($file1);
            File::delete($files);
        }
        catch(\Exception $ex)
        {

        } 
        return $temp;         
    }

    public function searchProducts($collection)
    {
       
        $client = new client(); 
        
        $endPoint = "https://v3.synccentric.com/api/v3/product_search";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');

        $data = array(); 

        $data["campaign_id"] = $campaign;

        try{            

            $promise = $client->requestAsync('POST', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'body' => json_encode($data)           
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
            $statusCode = $response1->getStatusCode();   
            $responseBodyAsString = $response1->getBody()->getContents();
            if($statusCode!=200)
            {
               logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);         
               
            }
        }   
        
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                     
        }
        
       
        if(count($collection)>500)
            sleep(60);

        $this->pollStatus($collection);

    }

    public function pollStatus($collection)
    {
     
        $client = new client(); 
        
        $endPoint = "https://v3.synccentric.com/api/v3/product_search/status";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');

        $data = array(); 

        $data["campaign_id"] = $campaign;
        
        try{
     
            $promise = $client->requestAsync('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'query' => ['campaign_id' => $campaign]         
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
            $statusCode = $response1->getStatusCode();   
            
            if($statusCode!=200)
            {
                logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>'Issue in polling part']);     
                return redirect()->route('products');      
            }

            $body = json_decode($response1->getBody()->getContents());

            if($body->searchThrottled)
            {
                logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>'Search Throttled. Try tomorrow or upgrade the plan']);         
                return redirect()->route('products');               
            }

            if($body->percentage<100 || $body->convertedItems != $body->totalItems)
            {
                sleep(60);
                $this->pollStatus($collection);   
            }
            else
            {     
                $record = logs::where('id',$this->recordId)->get()->first();       
                
                $identifiers = $record->identifiers;
                
                $errorItems = $record->errorItems;
                
                $successItems = $record->successItems;
                
                if(empty($identifiers))
                    $identifiers = 0; 
                
                if(empty($errorItems))
                    $errorItems = 0; 
                
                if(empty($successItems))
                    $successItems = 0; 
                
                logs::where('id',$this->recordId)->update(['identifiers'=>$body->totalItems + $identifiers,'errorItems'=>$body->errorItems + $errorItems,'successItems'=>$successItems + $body->listingsReturned]);
                $this->getJsonResults($collection); 
            }
        
        }

        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);   
      
        }

                    
        
    }

    public function getJsonResults($collection)
    {
  
        $client = new client(); 

        $endPoint = "https://v3.synccentric.com/api/v3/products";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');
        try{
        
            $promise = $client->requestAsync('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'query' => ['campaign_id'=>$campaign, 'fields'=> array("large_image","asin","lowest_new_price_fba
                ","total_new_sellers_fba","title","upc"),'downloadable'=>'1','downloadable_type'=>'json']         
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
            $statusCode = $response1->getStatusCode();                    

            $body = json_decode($response1->getBody()->getContents()); 

            if($body->status=="success")
                $endPoint= $body->attributes->url;
            
            $this->jsonFileParsing($endPoint, $collection);

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);               
        }
                    
      
        
    }

    public function saveImage($sku, $url)
    {
        // try
        // {
        //     $filename = basename($url);
        //     $extension =  File::extension($url);
        //     Image::make($url)->save(public_path('images/amazon/' . $sku.'.jpg'));
        // }
        // catch(\Exception $ex)
        // {

        // }
    }

    public function jsonFileParsing($url, $collection)
    {
     
        $this->count=0;        
        $client = new client(); 

            try{
                $promise = $client->requestAsync('GET', $url,[]);  
                    
                $promise->then(
                    function (ResponseInterface $res) {
                       return $res;                                     
                    },
                    function (RequestException $e) {
                       return $e;
                    }
                );
        
                $response1 = $promise->wait();
                $statusCode = $response1->getStatusCode();   
                
            if($statusCode!=200)
            {
                
                    sleep(10);
                    $this->jsonFileParsing($url, $collection);
            }
            else
            {
                $body = json_decode($response1->getBody()->getContents()); 
                $errors = "";
                try{
                $errors = json_encode($body->errors);
                }
                catch(\Exception $ex)
                {

                }
                    if(empty(trim($errors)))
                    {
                        
                        foreach($body as $product)
                        {                        
                            $temp = array(); 
                            $asin = $product->asin;
                            $image = $product->large_image;
                            $LFBAP = $product->lowest_new_price_fba;            
                            $totalSellers = $product->total_new_sellers_fba;
                            $title = $product->title;
                            $upc = $product->upc;
                            $sc_id = $product->id; 
            
                            
                            try{                    
                                $exists = products::where('asin',$asin)->get()->first();
            
                                if(!empty($exists))
                                {
                                    
                                //update  
                                $strategy = strategies::where('id',$exists->strategy_id)->get()->first();
                                if($strategy->type==1)
                                        $price = ($LFBAP + $strategy->value) / (1 - $strategy->breakeven/100);
                                else
                                        $price = ($LFBAP * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
                                
                                if($LFBAP==0)                                    
                                    $insert = products::where('asin',$asin)->update(['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'title'=>$title, 'totalSellers'=>$totalSellers]);
                                else
                                    $insert = products::where('asin',$asin)->update(['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'price' =>$price, 'title'=>$title, 'totalSellers'=>$totalSellers]);

                                $this->saveImage($asin,$image);        
                                }
                                else
                                {
                                    //insert
                                    $account = "";
                                    $code= "";
                                    
                                    
                                    
                                    foreach($collection as $product)
                                    {
                                            if($product['asin']==$asin)
                                            {
                                                $account = $product['account'];
                                                $code = $product['strategy'];
                                    
                                                break;
                                            }
                                    }
                                    
                                
                                    $strategy = strategies::where('code',$code)->get()->first(); 
                    
                                    if($strategy->type==1)
                                        $price = ($LFBAP + $strategy->value) / (1 - $strategy->breakeven/100);
                                    else
                                        $price = ($LFBAP * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
                                    
                                    if($LFBAP==0)
                                        $price = 14.99;   
            
                                    $this->count++;
                                    
                                    $insert = products::updateOrCreate(
                                        ['asin'=>$asin],    
                                        ['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'price' =>$price, 'title'=>$title, 'totalSellers'=>$totalSellers, 'account'=>$account, 'strategy_id'=>$strategy->id, 'created_at' => Carbon::now()->toDateString() ]
                                    );
                                    $this->saveImage($asin,$image);        
                                }
            
            
                                
                            }
                            
                            catch(\Exception $ex)
                            {
                                
                            }
            
                        }
                        
                        

                       
                        
                    }
                    
                    else
                    {
                        sleep(10);
                        $this->jsonFileParsing($url, $collection);
                    }


            } 
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();   
                    logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                    
            }


           
    }

    public function deleteIdentifiers()
    {
        $client = new client(); 
   
        $endPoint = "https://v3.synccentric.com/api/v3/products/";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');

        $data = array(); 

        $data["campaign_id"] = $campaign;
        
        $status = 0;
        try{

            $promise = $client->requestAsync('DELETE', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'query' => ["campaign_id"=>$campaign]           
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
            {
                Session::flash('error_msg', "deleteIdentifiers - ".$responseBodyAsString);
                return redirect()->route('products');
            }
                      

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();           
        }      
                     
    }
    
}
