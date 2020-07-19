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
use App\log_batches;
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

class SellerActive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $account;
    public $products;
    public $id; 
    public function __construct($products, $account, $id)
    {
        $this->products = $products; 
        $this->account = $account;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {                
        $this->updatePrices($this->products, $this->account);
        $lastId = accounts::max('id');
        if($this->account->id == $lastId)
            log_batches::where('id',$this->id)->update(['date_completed'=>date('Y-m-d H:i:s'),'status'=>'Completed']);           
    }

    public function updatePrices($products, $account)
    {        
        try
        {              
            $endPoint = 'https://rest.selleractive.com:443/api/Inventory';
            
            $client = new Client();  

            $credential = $account;              
            
            foreach($products as $product)
            {
                if(strtolower(trim($product->account))!=strtolower(trim($account->store)))
                    continue; 

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

                

                $remaining = $this->getRateLimit($credential);

                if($remaining['remaining']<100)
                {
                    sleep($remaining['reset']);
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
                
                $response = $client->request('PUT', $endPoint,
                [
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'token' => 'test-token'],
                    'body' => json_encode($data),
                    'auth' => [
                        $credential->username, 
                        $credential->password
                    ]
                ]);    
                                               
                $status = $response->getStatusCode(); 
                $responseBodyAsString = $response->getBody()->getContents();             
               
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
            $temp = array();
            $remaining= json_decode($responseBodyAsString)->Remaining;
            $reset= json_decode($responseBodyAsString)->Reset; 
            
            $temp['remaining'] = $remaining; 
            $temp['reset'] = $reset; 

            return $temp;          
            
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();             
                                                        
        }  

        $temp['remaining'] = 0; 
        $temp['reset'] = 3600; 

        return $temp;  
    }

  
    
}
