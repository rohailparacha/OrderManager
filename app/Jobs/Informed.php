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
use App\blacklist;
use App\order_details; 
use App\amazon_settings; 
use App\log_batches;
use App\Jobs\Repricing;
use App\Jobs\SellerActive;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File; 
 use Redirect;
use Response;
use Image;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Carbon\Carbon;

class Informed implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $offset;
    public $recordId;
    public $batchId;     
    public $flag;
    
    public function __construct($offset, $recordId, $batchId, $flag)
    {
        $this->offset = $offset; 
        $this->recordId = $recordId;
        $this->batchId = $batchId;
        $this->flag = $flag;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {                        
        
        $this->exportRequest();        
        log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);           


    }

    
    public function updatePrices($offset, $limit)
    { 
        $name = log_batches::where('id',$this->batchId)->get()->first(); 
        $id = log_batches::insertGetId(['log_id'=>$this->recordId,'name'=>$name->name,'date_started'=>date('Y-m-d H:i:s'),'stage'=>'SellerActive','status'=>'In Progress']);   
        
        $accounts = accounts::all();         
        
        foreach($accounts as $account)
        {
            SellerActive::dispatch($offset,$limit, $account->id, $id, $this->flag)->onConnection('informed')->onQueue('informed');   
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
            
            log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);    
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
            log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);    
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
            log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);    
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
                    log_batches::where('id',$this->batchId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                                    
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
      
        foreach($data as $product)
        {
            try{            
            $update = products::where('asin',$product['SKU'])->where('lowestPrice','!=',0)->update(['price'=>$product['CURRENT_PRICE']]);
            }
            catch(\Exception $ex)
            {

            }
        }

       $this->updatePrices($this->offset, 5000);
    }
    
}
