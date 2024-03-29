<?php

namespace App\Jobs;
use App\categories;
use Excel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use DB;
use Illuminate\Http\Request;
use App\logs;
use App\log_batches;
use App\products;
use App\order_details; 
use App\amazon_settings; 
use App\accounts;
use App\sc_accounts;
use App\Jobs\Repricing;
use App\Jobs\Informed;
use App\Exports\InformedExport;
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
    private $offset;
    private $prodCount;
    public $batchId;
    public $token; 
    public $campaign; 
    public $scid; 
    
 
    public $flag;

    public function __construct($collection, $status, $flag)
    {        
        $this->collection = $collection; 
        $this->status = $status; 
        $this->flag = $flag;
        $this->offset = -1;      
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {            
        $scaccounts = sc_accounts::all(); 
        $status = $this->status; 
        $flag = $this->flag;

        $scid="";
        foreach($scaccounts as $sc)
        {
            $tempers = json_decode($sc->products); 
            if(in_array($flag,$tempers))
            {
                $scid = $sc->id;
                break;
            }
                
        }

        if(empty($scid))
        {
            echo ('Sync Centric Account Not Found - Terminating');
            die(); 
        }
        
        
        $sc_credentials = sc_accounts::where('id',$scid)->get()->first(); 
        
        $this->scid = $sc_credentials->name;
        $this->token = $sc_credentials->token;
        $this->campaign = $sc_credentials->campaign;            

        $setting = amazon_settings::get()->first();   
        $flag = $this->flag;
        if($flag==1)
        {
            $prd = products::whereIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->orWhere('created_at', '>', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
    
        }
        else
        {
            $prd = products::whereNotIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->Where('created_at', '<=', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
        }

      

        $collection = $this->collection; 
        $this->prodCount = $prd->count();
        
        if($status=='new')
            $this->newProducts($collection);
        else
            $this->repricing();        
       
        $this->recordId=0;
        

        $status = $this->status; 
        
        $collection = $this->collection; 
        
        
    }

    public function repricing()
    {
        $flag = $this->flag; 
        
        if($flag==1)
            $this->recordId = logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress','action'=>'Primary Items - Repricing','scaccount'=> $this->scid]);
        elseif ($flag == 2)
            $this->recordId = logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress','action'=>'Secondary Items - Repricing','scaccount'=> $this->scid]);        

        $setting = amazon_settings::get()->first();   
           
        if($flag==1)
        {
            $prd = products::whereIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->orWhere('created_at', '>', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
    
        }
        else
        {
            $prd = products::whereNotIn('asin', function($query) use($setting){
                $query->select('SKU')
                ->from(with(new order_details)->getTable())
                ->join('orders','order_details.order_id','orders.id')
                ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
                ->groupBy('SKU')
                ->havingRaw('count(*) >= ?', [$setting->soldQty]);
                })
                ->Where('created_at', '<=', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
        }
        $products = $prd->get();
        
        $dataArray = array(); 

        foreach($products as $product)
        {
            try{
            $asin = $product->asin;
            $account = $product->account;            
            $action = 'add';
            $dataArray[]= ['asin'=>$asin, 'account'=>$account, 'action'=>$action];    
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

        $this->recordId = logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress','action'=>'Add Products']);

        $temp = $collection->chunk(5000);

        foreach($temp as $col)
        {
            $this->newProd($col);
            $this->getProducts($col);
        }        
       
        logs::where('id',$this->recordId)->where('status','In Progress')->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);
        
    }

    public function newProd($col)
    {
        foreach($col as $product)
        {                
                
                if(strtolower($product['action'])=='delete')
                {
                    logs::where('id',$this->recordId)->update(['action'=>'Delete Products']);

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
        }

    }

    public function getProducts($collection)
    {   
        $this->offset = $this->offset + 1;

        $this->deleteIdentifiers();         
        
        $iteration = 0;                 
         
        $temp = $collection->chunk(1000);
        
        foreach($temp as $col)
        {
        
            logs::where('id',$this->recordId)->update(['stage'=>'SyncCentric']);    
            $client = new client(); 
            
            $endPoint = "https://v3.synccentric.com/api/v3/products";
            
            $status = $this->status; 
            $flag = $this->flag; 
            
            $token = $this->token;
            $campaign = $this->campaign;


            $data = array(); 

            $data["campaign_id"] = $campaign;
            
            $identifiers = array();
            foreach($col as $product)
            {
                if(strtolower($product['action'])=='add')
                {
                    if(empty(trim($product['asin'])))
                        continue;
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
        $sc_id =products::where('id','=',$id)->get()->first();

        try{
            $file1 = public_path('images/amazon/' . $sc_id->asin.'.jpg');            
            $files = array($file1);
            File::delete($files);
        }
        catch(\Exception $ex)
        {

        } 
        $temp = products::where('id','=',$id)->delete();      
        return $temp;         
    }

    public function searchProducts($collection)
    {
        $client = new client(); 
        
        $endPoint = "https://v3.synccentric.com/api/v3/product_search";
        
        $token = $this->token;
        $campaign = $this->campaign;
        
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
        
        $token = $this->token;
        $campaign = $this->campaign;

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

        catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);   
      
        }          
        
    }

    public function getJsonResults($collection)
    {
  
        $client = new client(); 

        $endPoint = "https://v3.synccentric.com/api/v3/products";
        
        $token = $this->token;
        $campaign = $this->campaign;

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
        try
        {
            $filename = basename($url);
            $extension =  File::extension($url);
            Image::make($url)->save(public_path('images/amazon/' . $sku.'.jpg'));
        }
        catch(\Exception $ex)
        {

        }
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
                                
                                $insert = products::where('asin',$asin)->update(['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'title'=>$title, 'totalSellers'=>$totalSellers]);
                   
                                if($this->status=='new')
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
                                    
                                                break;
                                            }
                                    }
                                                        
            
                                    $this->count++;
                                    
                                    $insert = products::updateOrCreate(
                                        ['asin'=>$asin],    
                                        [
                                            'upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'title'=>$title, 'totalSellers'=>$totalSellers, 'account'=>$account, 'created_at' => Carbon::now()->toDateString() 
                                        ]
                                    );    
                                                                                 
                                        $this->saveImage($asin,$image);     
                                }
            
            
                                
                            }
                            
                            catch(\Exception $ex)
                            {
                                
                            }
            
                        }
                        
                        
           
                        $this->submitFeed();
                       
                        
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
        
        $token = $this->token;
        $campaign = $this->campaign;

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

  
    public function submitFeed()
    {   
        $client = new client();

        $filename = date("d-m-Y")."-".time()."-import.csv";
        
        if($this->status=='new')
            Excel::store(new InformedExport( $this->prodCount + ($this->offset * 5000), $this->flag), $filename,'local');      
        else
            Excel::store(new InformedExport($this->offset * 5000, $this->flag), $filename,'local');   
        
        $endPoint ='https://api.informed.co/v1/feed';

        $key= env('INFORMED_TOKEN', '');
        try{
            
            $this->batchId = log_batches::insertGetId(['log_id'=>$this->recordId,'name'=>'Batch - '.($this->offset +1),'date_started'=>date('Y-m-d H:i:s'),'stage'=>'Informed','status'=>'In Progress']);   

            $promise = $client->requestAsync('POST', $endPoint,
            [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'x-api-key' => $key],
            'body' => fopen(storage_path('app/'.$filename), 'r')
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
            $body =  json_decode($response1->getBody()->getContents());

            $id= $body->FeedSubmissionID;
            $this->getFeedStatus($id);               
           
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();             
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
        }     
    
    }

    public function getFeedStatus($id)
    {   
        $client = new client();

        $endPoint ='https://api.informed.co/v1/feed/submissions/'.$id;
        $key= env('INFORMED_TOKEN', '');
        try{
            $promise = $client->requestAsync('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'x-api-key' => $key]
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
            $body =  json_decode($response1->getBody()->getContents());

           $status = $body->Status;
           $percentage = $body->ProcessedPercent;
           
           if($status=='Completed' || $status== 'CompletedWithErrors')
           {
            
            log_batches::where('id',$this->batchId)->update(['totalItems'=>$body->SuccessCount+$body->ErrorCount, 'successItems'=>$body->SuccessCount,'errorItems'=> $body->ErrorCount]);    

            if($this->status=='new')            
                Informed::dispatch(( $this->prodCount + ($this->offset * 5000)),$this->recordId, $this->batchId, $this->flag)->onConnection('informed')->onQueue('informed')->delay(now()->addMinutes(60));

            else
                 Informed::dispatch($this->offset * 5000,$this->recordId, $this->batchId,  $this->flag)->onConnection('informed')->onQueue('informed')->delay(now()->addMinutes(60));
           }
           else
           { 
               sleep(30);
               $this->getFeedStatus($id);              
           }
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();             
            log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
        }           
        
    }

    
}
