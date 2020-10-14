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
    public $id;
    
    public function __construct($collection, $id)
    {
        $this->collection = $collection; 
        $this->id = $id; 
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {                        
        
        //send to informed
        $this->submitFeed();

    }

    public function submitFeed()
    {   
        $collection = $this->collection;

        foreach($collection as $col)
        {
            $this->tmpArray[]= $col['asin'];
        }

        $client = new client();

        $filename = date("d-m-Y")."-".time()."-import.csv";
        
        Excel::store(new NewInformedExport($this->collection), $filename,'local');   
        
        $endPoint ='https://api.informed.co/v1/feed';

        $key= env('INFORMED_TOKEN', '');
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
                sleep(3600);
                $this->exportRequest();
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
        $key= env('INFORMED_TOKEN', '');
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
        $arr = $this->tmpArray;

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

        $this->createFile();
       
    }

    public function createFile()
    {        

        $filename = date("d-m-Y")."-".time()."-selleractive-export.csv";
        
        Excel::store(new NewSellerActiveExport($this->collection), $filename,'exports');   
        
        $url = URL::to('/repricing/exports/').$filename;

        $id = new_logs::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed','export_link'=> $url]);
    }

    


    
}
