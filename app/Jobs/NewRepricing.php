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
use App\new_logs;
use URL;
use App\products;
use App\accounts;
use App\blacklist;
use App\order_details; 
use App\Exports\NewInformedExport;
use App\Exports\NewSellerActiveExport;
use App\amazon_settings; 
use App\log_batches;
use App\Jobs\Repricing;
use App\Jobs\SellerActive;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File; 
use Redirect;
use Response;
use Excel;
use Image;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class NewRepricing implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $collection;
    public $tmpArray;
    public $type; 
    public $id;
    public $originalCollection;

    public $infToken; 
    
    public function __construct($collection, $id, $type)
    {
        $this->collection = $collection; 
        $this->id = $id; 
        $this->type= $type;
        $this->originalCollection= $collection;
    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function deleteProduct($asin)
    {            
        $sc_id =  products::where('asin',$asin)->get()->first();
        
        $temp = products::where('id','=',$sc_id->id)->delete(); 

        try{
            $file1 = public_path('images/amazon/' . $sc_id->asin.'.jpg');            
            $files = array($file1);
            File::delete($files);
        }
        catch(\Exception $ex)
        {

        }  
    }

    public function handle()
    {                          

        if($this->type=='delete')
        {
            foreach($this->originalCollection as $product)
            {
                $this->deleteProduct($product['asin']);
            }
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']); 
            return;
        }
        $accounts = accounts::leftJoin('informed_accounts','accounts.infaccount_id','informed_accounts.id')->get();
        
        if(empty($this->originalCollection))
        {
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);    
            return;                   
        }

        foreach($accounts as $account)
        {
            $response = array();
            $collection = $this->originalCollection;
            $this->infToken = $account->token; 
            foreach($collection as $key => $col)
            {
                if($this->type=='old')
                {
                    $prod = products::where('asin',$col['asin'])->get()->first();
                    if(empty($prod))
                        continue; 
                    if($prod->account==$account->store)
                        $response[]=$col;
                }
                else
                {
                    if($col['store']==$account->store)
                        $response[]=$col;
                }
                
            }   
            $this->collection = $response;
            
            $this->submitFeed();
        }
        
        sleep(3600);
        foreach($accounts as $account)
        {
            $response = array();
            $collection = $this->originalCollection;
            $this->infToken = $account->token; 
            foreach($collection as $key => $col)
            {
                if($this->type=='old')
                {
                    $prod = products::where('asin',$col['asin'])->get()->first();
                    if(empty($prod))
                        continue; 
                    if($prod->account==$account->store)
                        $response[]=$col;
                }
                else
                {
                    if($col['store']==$account->store)
                        $response[]=$col;
                }
                
            }   
            $this->collection = $response;

            $this->exportRequest();
        }

        $this->createFile();
       
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

    public function submitFeed()
    {   
        $collection = $this->collection;

        $type = $this->type;
        
        
        foreach($collection as $col)
        {
            if(is_numeric($col['lowestPrice']))             
            {
                if($this->type=='old')
                    $update = products::where('asin',$col['asin'])->update(['lowestPrice'=>$col['lowestPrice']]);
                else 
                {
                    $insert = products::insert(['asin'=>$col['asin'],'lowestPrice'=>$col['lowestPrice'], 'upc'=>$col['id'], 'title'=>$col['title'], 'image'=>$col['image1'], 'account'=>$col['store'], 'image2'=>$col['image2'], 'brand'=>$col['brand'], 'description'=>$col['description'],'type'=>$col['type'],'created_at' => Carbon::now()->toDateString()]);
                     $this->saveImage($col['asin'],$col['image1']);
                }
                                    
            }
        }

        $client = new client();

        $filename = date("d-m-Y")."-".time()."-import.csv";
        
        Excel::store(new NewInformedExport($this->collection), $filename,'local');   
        
        $endPoint ='https://api.informed.co/v1/feed';

        $key= $this->infToken;
        try{
                        
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
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);                       
        }     
    
    }

    public function getFeedStatus($id)
    {   
        $client = new client();

        $endPoint ='https://api.informed.co/v1/feed/submissions/'.$id;
        $key= $this->infToken;
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
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);                     
        }           
        
    }

  
    
    public function exportRequest()
    {
        $client = new client();

        $endPoint ='https://api.informed.co/v1/export';
        $key= $this->infToken;
        try{
            $promise = $client->requestAsync('POST', $endPoint,
            [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'x-api-key' => $key],
            'query' =>  ['userDataExportType'=>'MinMax']
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
            $id = $body->ExportRequestID;

            $this->getExportStatus($id);

    
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();          
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);                                   
        } 
    }

   


    public function getExportStatus($id)
    {   
        $client = new client();

        $endPoint ='https://api.informed.co/v1/export/requests/'.$id;
        $key= $this->infToken;
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
          
           
           if($status=='Completed')
           {
                $id = $body->ExportID;
                $this->getExportDownloadLink($id);
           }
           else
           {
               sleep(30);
               $this->getExportStatus($id);
           }

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();      
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);                   
        }    
        
       
        
    }

    public function getExportDownloadLink($id)
    {   
        $client = new client();

        $endPoint ='https://api.informed.co/v1/export/downloadlink/'.$id;
        $key= $this->infToken;
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

           $this->getResults($body->DownloadLink);

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();             
            new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);
        }    
                
    }

    public function getResults($link)
    {

        $client = new client();             
            try{
                $filename = date("d-m-Y")."-".time()."-prod.csv";
                $promise = $client->requestAsync('GET', $link,['save_to' => public_path() . '/'.$filename]);  
                    
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
                $this->csvToArray(public_path() . '/'.$filename);
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();            
                    new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);           
            }

            
    }

    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        $this->updateDatabase($data);
    }

    public function updateDatabase($data)
    {

        $arr = array(); 
        $collection = $this->collection; 
        
        foreach($collection as $col)
        {
            if(is_numeric($col['lowestPrice']))             
            {                 
                $arr[]= $col['asin'];
            }
        }
        

        foreach($data as $product)
        {
            if(in_array($product['SKU'],$arr))
            {                                  
                try{            
                    $update = products::where('asin',$product['SKU'])->where('lowestPrice','!=',0)->update(['price'=>$product['CURRENT_PRICE']]);
                    }
                    catch(\Exception $ex)
                    {
        
                    }
            }                        
        }        
       
    }

    public function failed()
    {
        new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed']);  
    }

    public function createFile()
    {        

        $filename = date("d-m-Y")."-".time()."-selleractive-export.csv";
        
        Excel::store(new NewSellerActiveExport($this->collection), $filename,'exports');   

        $url = URL::to('/repricing/exports/')."/".$filename;

        $id = new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed','export_link'=> $url]);
    }

    


    
}
