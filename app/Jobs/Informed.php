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
use App\Jobs\Repricing;
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
    
    public function __construct($offset, $recordId)
    {
        $this->offset = $offset; 
        $this->recordId = $recordId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {                
        $this->exportRequest();
        logs::where('id',$this->recordId)->where('status','In Progress')->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);
    }

    public function updatePrices($offset, $limit)
    {        
        try
        {  
            logs::where('id',$this->recordId)->update(['stage'=>'SellerActive']);  

            $endPoint = 'https://rest.selleractive.com:443/api/Inventory';
            
            $client = new Client();                
            
            $products = products::offset($offset)->limit($limit)->get();
            
            foreach($products as $product)
            {
                $detail = products::leftJoin('accounts','products.account','=','accounts.store')
                ->select(['products.*','accounts.lagTime'])
                ->orderBy('account')
                ->where('products.id',$product->id)->get()->first(); 
                               
                
                $qty='0';
                if($product->lowestPrice==0)
                    $qty='0';
                else
                    $qty=empty($product->quantity)?'100':$product->quantity;
                    
                $blacklist = blacklist::all();
                
                foreach($blacklist as $bl)
                {
                    if(strtolower(trim($bl->sku))==strtolower(trim($product->asin)))
                    {
                        if($product->lowestPrice>0)
                        {
                            $qty=$bl->allowance;
                            break;
                        }                    
                    }                    
                }

                $credential = accounts::where('store',$product->account)->get()->first();        
                $remaining = $this->getRateLimit($credential);

                if($remaining==0)
                {
                    sleep(60);
                    $this->updatePrices($offset, $limit);
                }
                $data['SKU'] =$product->asin;
                $data['Quantity'] = $qty;
                $data['Price'] = $product->price;

                $data['Locations'] = array();
                $temp2['LocationName'] = 'My Warehouse';
                $temp2['SKU'] = $product->asin;
                $temp2['Quantity'] = $qty;
                $data['Locations'][]= $temp2; 
                
                $data['ProductSites'] = array();
                $temp['Site'] = "Walmart";        
                $temp['Price'] = $product->price;
                $temp['LeadtimeToShip'] = $detail->lagTime;
                $temp['FloorPrice'] = 0;
                $temp['CeilingPrice'] = 0;
                $data['ProductSites'][]= $temp;     
                
                $maxListing=empty($product->maxListingBuffer)?'2':$product->maxListingBuffer;

            
            
            try
            {
                $promise = $client->requestAsync('PUT', $endPoint,
                [
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'token' => 'test-token'],
                    'body' => json_encode($data),
                    'auth' => [
                        $credential->username, 
                        $credential->password
                    ]
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
                $responseBodyAsString = $response1->getBody()->getContents();             
                
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();             
                                                            
            }  

            }                               
        }
        catch(\Exception $ex)
        {
            
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
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
        } 
    }

    public function getRateLimit($credential)
    {
        $client = new client(); 
        $endPoint = "https://rest.selleractive.com:443/api/RateLimitStatus";
        try
        {
            $promise = $client->requestAsync('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'token' => 'test-token'],                
                'auth' => [
                    $credential->username, 
                    $credential->password
                ]
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
            $responseBodyAsString = $response1->getBody()->getContents(); 
            $remaining= json_decode($responseBodyAsString)->Remaining; 
            return $remaining;          
            
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();             
                                                        
        }  

        return 0;
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
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
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
            logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                       
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
                    logs::where('id',$this->recordId)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Failed','error'=>$responseBodyAsString]);                                                     
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

       //$this->updatePrices($this->offset, 5000);
    }
    
}
