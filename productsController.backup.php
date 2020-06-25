<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\strategies;
use App\products;
use App\accounts;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\File; 
use Validator; 
use Session;
use Redirect;
use Excel; 
use Response;
use Config;
use Image;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use App\Exports\SellerActiveExport;

class productsController extends Controller
{
    //
    private $count;
    public function __construct()
    {
        $this->middleware('auth');

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


     public function repricing()
    {
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

          
        
    }
    
    public function getTemplate()
    {
        //PDF file is stored under project/public/download/info.pdf
        $file="./templates/template.csv";
        return Response::download($file);
    }

    public function getLogs()
    {
        $logs = logs::all(); 
        return view('logs',compact('logs'));
    }

    
    public function index()
    {          
     
        $last_run = products::max('modified_at');      
        $products = products::select()->paginate(100); 
        $strategies = strategies::select()->get(); 
        $accounts = accounts::select()->get(); 
        $strategyCodes = array(); 
        $maxSellers = ceil(products::max('totalSellers'));
        $maxPrice = ceil(products::max('price'));
        $minAmount = 0;
        $maxAmount = $maxPrice;
        $minSeller = 0;
        $maxSeller = $maxSellers;

        $accountFilter = 0; 
        $strategyFilter = 0; 

        foreach($strategies as $strategy)
        {
            $strategyCodes[$strategy->id] = $strategy->code;
        }

        return view('products.index',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','minAmount','maxAmount','minSeller','maxSeller','accountFilter','strategyFilter','last_run'));
    }

    public function getProducts($collection)
    {

        $this->deleteIdentifiers(); 
        
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
                        $this->deleteProductFile($pId->id);
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
                            $price = 99.99;
                        $prod = products::where('asin',$product['asin'])->update(['strategy_id'=>$strategy->id, 'price'=>$price]);
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
                
                try{
                
                $response = $client->request('POST', $endPoint,
                [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'body' => json_encode($data)           
                ]);    
                
            
                $statusCode = $response->getStatusCode();
                }
                catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    
                    Session::flash('error_msg', "getProducts".$responseBodyAsString);
                    return redirect()->route('products');
                }
                if($statusCode!=200)
                {
                    Session::flash('error_msg', __('Import Failed'));
                    return redirect()->route('products');
                }
                        
                $body = json_decode($response->getBody()->getContents());   
                
            }
          
        }
        
        $this->searchProducts($collection); 
       
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
            $response = $client->request('POST', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'body' => json_encode($data)           
            ]); 
            $statusCode = $response->getStatusCode();
        }   
        
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            
            Session::flash('error_msg', "searchProducts".$responseBodyAsString);
            return redirect()->route('products');
        }
        
            
        if($statusCode!=200)
        {
            Session::flash('error_msg', __('Import Failed'));
            return redirect()->route('products');
        }
                    
        $body = json_decode($response->getBody()->getContents()); 


        if(count($collection)>500)
            sleep(100);
        $this->pollStatus($collection);
    }

    public static function getIranTime($date)
    {
        
        date_default_timezone_set('Pacific/Marquesas');

        $datetime = new \DateTime($date);        
        
        $la_time = new \DateTimeZone('Asia/Tehran');
        
        $datetime->setTimezone($la_time);
        
        return $datetime->format('m/d/Y H:i:s');
        
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
        $response = $client->request('GET', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
            'query' => ['campaign_id' => $campaign]         
        ]);
        
        }

        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            
            Session::flash('error_msg', "pollStatus".$responseBodyAsString);
            return redirect()->route('products');
        }
        
        $statusCode = $response->getStatusCode();
            
        if($statusCode!=200)
        {
            Session::flash('error_msg', __('Import Failed'));
            return redirect()->route('products');
        }
                    
        $body = json_decode($response->getBody()->getContents());
        if($body->searchThrottled)
        {
            die(); 
        }
        if($body->percentage<100)
        {
            sleep(60);
            $this->pollStatus($collection);   
        }
        else
        {            
            $this->getJsonResults($collection); 
        }
    }

    public function export(Request $request)
    {        
        $accountFilter = $request->accountFilter;
        $strategyFilter = $request->strategyFilter;
        $sellerFilter = $request->sellerFilter;
        $amountFilter = $request->amountFilter; 

        $filename = date("d-m-Y")."-".time()."-products.xlsx";
        return Excel::download(new ProductsExport($accountFilter,$strategyFilter,$sellerFilter,$amountFilter), $filename);
    }

    public function filter(Request $request)
    {
        $last_run = products::max('modified_at');      
        if($request->has('accountFilter'))
            $accountFilter = $request->get('accountFilter');
        if($request->has('strategyFilter'))
            $strategyFilter = $request->get('strategyFilter');  
        if($request->has('sellerFilter'))
            $sellerFilter = $request->get('sellerFilter');
        if($request->has('amountFilter'))
            $amountFilter = $request->get('amountFilter');
        
        
        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
        
        $minSeller = trim(explode('-',$sellerFilter)[0]);
        $maxSeller = trim(explode('-',$sellerFilter)[1]);        

        //now show orders
        $products = products::select();
                           

        if(!empty($accountFilter)&& $accountFilter !=0)
        {   
            $account= accounts::where('id',$accountFilter)->get()->first();          
            $products = $products->where('account',$account->store);
        }

        if(!empty($strategyFilter)&& $strategyFilter !=0)
        {            
            $products = $products->where('strategy_id',$strategyFilter);
        }

            $products = $products->whereBetween('totalSellers',[$minSeller,$maxSeller]);            
        
            $products = $products->whereBetween('price',[$minAmount,$maxAmount]);
        
        $products  = $products->paginate(100)->appends('accountFilter',$accountFilter)->appends('strategyFilter',$strategyFilter)->appends('sellerFilter',$sellerFilter)->appends('amountFilter',$amountFilter);

        $strategies = strategies::select()->get(); 
        $accounts = accounts::select()->get(); 
        $strategyCodes = array(); 
        foreach($strategies as $strategy)
        {
            $strategyCodes[$strategy->id] = $strategy->code;
        }

        $maxSellers = ceil(products::max('totalSellers'));
        $maxPrice = ceil(products::max('price'));
        return view('products.index',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','accountFilter','strategyFilter','minAmount','maxAmount','minSeller','maxSeller','last_run'));
    }

    public function getResults($collection)
    {
        $client = new client(); 
        $page = 1; 

        $endPoint = "https://v3.synccentric.com/api/v3/products";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');
        try{
        $response = $client->request('GET', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
            'query' => ['page'=>$page, 'campaign_id'=>$campaign, 'fields'=> array("large_image","asin","lowest_new_price_fba
            ","total_new_sellers_fba","title","upc"),'downloadable'=>'1','downloadable_type'=>'json']         
        ]);    
         
        $statusCode = $response->getStatusCode();
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            
            Session::flash('error_msg', "getResults".$responseBodyAsString);
            return redirect()->route('products');
        }
        if($statusCode!=200)
        {
            Session::flash('error_msg', __('Import Failed'));
            return redirect()->route('products');
        }
                    
        $body = json_decode($response->getBody()->getContents()); 
        
        $totalPages = $body->meta->last_page;
   
      

        while($page<=$totalPages)
        {
            try{
            $response = $client->request('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
                'query' => ['page'=>$page, 'campaign_id'=>$campaign, 'fields'=> array("large_image","asin","lowest_new_price_fba
                ","total_new_sellers_fba","title","upc")]         
            ]);    
            
            
            $statusCode = $response->getStatusCode();
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                $response = $e->getResponse();
                $responseBodyAsString = $response->getBody()->getContents();
                
                Session::flash('error_msg', "getResults".$responseBodyAsString);
                return redirect()->route('products');
            }
            if($statusCode!=200)
            {
                Session::flash('error_msg', __('Import Failed'));
                return redirect()->route('products');
            }
                        
            $body = json_decode($response->getBody()->getContents()); 
           
            foreach($body->data as $product)
            {
                
                

                $temp = array(); 
                $asin = $product->attributes->asin;
                $image = $product->attributes->large_image;
                $LFBAP = $product->attributes->lowest_new_price_fba;            
                $totalSellers = $product->attributes->total_new_sellers_fba;
                $title = $product->attributes->title;
                $upc = $product->attributes->upc;
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
                        $price = 99.99;
                      $insert = products::where('asin',$asin)->update(['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'price' =>$price, 'title'=>$title, 'totalSellers'=>$totalSellers]);
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
                            $price = 99.99;   

                        $insert = products::updateOrCreate(
                            ['asin'=>$asin],    
                            ['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'price' =>$price, 'title'=>$title, 'totalSellers'=>$totalSellers, 'account'=>$account, 'strategy_id'=>$strategy->id]
                        );
                    }


                    
                }
                
                catch(\Exception $ex)
                {
                    
                }

            }
            $page++; 
        }

        
        

        Session::flash('success_msg', __('Products Synced Successfully'));
        return redirect()->route('products');
    }

    public function getJsonResults($collection)
    {
        $client = new client(); 

        $endPoint = "https://v3.synccentric.com/api/v3/products";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');
        try{
        $response = $client->request('GET', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
            'query' => ['campaign_id'=>$campaign, 'fields'=> array("large_image","asin","lowest_new_price_fba
            ","total_new_sellers_fba","title","upc"),'downloadable'=>'1','downloadable_type'=>'json']         
        ]);    
        
        
        $statusCode = $response->getStatusCode();
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            
            Session::flash('error_msg', "getJsonResults".$responseBodyAsString);
            return redirect()->route('products');
        }
                    
        $body = json_decode($response->getBody()->getContents()); 

        if($body->status=="success")
            $endPoint= $body->attributes->url;

        $this->jsonFileParsing($endPoint, $collection);
        
    }

    public function jsonFileParsing($url, $collection)
    {
        $this->count=0;        
        $client = new client(); 

            try{
                $response = $client->request('GET', $url,[]);                     
                $statusCode = $response->getStatusCode();
            }
            catch (\GuzzleHttp\Exception\ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();                    
            }

            if($response->getStatusCode()!=200)
            {
                
                    sleep(10);
                    $this->jsonFileParsing($url, $collection);
            }
            else
            {
                $body = json_decode($response->getBody()->getContents()); 
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
                                $price = 99.99;
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
                                    $price = 99.99;   
        
                                $this->count++;
                                
                                $insert = products::updateOrCreate(
                                    ['asin'=>$asin],    
                                    ['upc'=>$upc,'sc_id'=>$sc_id, 'image'=>$image, 'lowestPrice'=>$LFBAP, 'price' =>$price, 'title'=>$title, 'totalSellers'=>$totalSellers, 'account'=>$account, 'strategy_id'=>$strategy->id]
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

            Session::flash('success_msg', __('Products Synced Successfully'));
            return redirect()->route('products');
    }

    public function getFile()
    {
        $filename = date("d-m-Y")."-".time()."-sa-api-export.xlsx";
        return Excel::download(new SellerActiveExport(), $filename);   
    }
   

    public function uploadSubmit(Request $request)
    {
        $input = [
            'file' => $request->file           
        ];

        $rules = [
            'file'    => 'required'  
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            return redirect()->route('products');
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['csv','xls','xlsx'];
        
            $file = $request->file('file');
          
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('imports');   
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('products');
             }
            

        }
        else
        {
            
        }
        $import = new ProductsImport;
        Excel::import($import, $filename);
        $collection = $import->data;
        
        $this->getProducts($collection);
            
        
        Session::flash('success_msg', $this->count. ' Products Imported Succsesfully');
        return redirect()->route('products');

    }

    public function deleteProduct($id)
    {        
        
    
        $sc_id =  products::where('id',$id)->get()->first();
        
        $temp = products::where('id','=',$id)->delete(); 

        try{
            $file1 = public_path('images/amazon/' . $sc_id->asin.'.jpg');            
            $files = array($file1);
            File::delete($files);
        }
        catch(\Exception $ex)
        {

        }  

        if(empty($sc_id))
            return redirect()->route('products');                          
        else
            Session::flash('success_msg', $sc_id->asin.' Was Deleted Succesfully');
        
        return redirect()->route('products');  
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
                       
    }

   

    public function deleteIdentifiers()
    {
        $client = new client(); 
   
        $endPoint = "https://v3.synccentric.com/api/v3/products/";
        
        $token = env('SC_TOKEN', '');

        $campaign = env('SC_CAMPAIGN','');

        $data = array(); 

        $data["campaign_id"] = $campaign;
         
        try{
        $response = $client->request('DELETE', $endPoint,
        [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer '.$token],
            'query' => ["campaign_id"=>$campaign]           
        ]);    

        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
    
            Session::flash('error_msg', "deleteIdentifiers".$responseBodyAsString);
            return redirect()->route('products');
        }
        
        $statusCode = $response->getStatusCode();

        $body = json_decode($response->getBody()->getContents());  

 

        if($statusCode == 200)
            return true; 
        else
            return false; 
        
                       
    }
}
