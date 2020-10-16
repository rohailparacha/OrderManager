<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\strategies;
use App\logs;
use App\log_batches;
use App\amazon_settings;
use Carbon\Carbon;
use App\products;
use App\order_details;
use URL;
use App\new_logs;
use DB;
use App\accounts;
use App\Jobs\Repricing;
use App\Jobs\NewRepricing;
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
use App\Imports\NewProductsImport;
use App\Exports\ProductsExport;
use App\Imports\PricesImport;
use App\Imports\WMProductsImport;
use App\Exports\AsinsExport;
use App\Exports\SellerActiveExport;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class productsController extends Controller
{
    //
    private $count;
    private $recordId; 
    public function __construct()
    {
        $this->middleware('auth');

    }
        
    
    public function getLogs()
    {
        $logs = logs::select()->orderBy('date_started', 'desc')->paginate(100);
        
         foreach($logs as $log)
        {
            if(!empty($log->date_started))
            {
                $log->date_started = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_started, 'UTC')
            ->setTimezone('America/Los_Angeles');     
            }
            
            if(!empty($log->date_completed))
            {
                $log->date_completed = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_completed, 'UTC')
            ->setTimezone('America/Los_Angeles');       
            }

            $log->cnt = log_batches::where('log_id',$log->id)->where('status','In Progress')->count(); 


            
        }
        return view('logs',compact('logs'));
    }

    public function getLogsSecondary()
    {
        $logs = new_logs::select()->orderBy('date_started', 'desc')->paginate(100);
        
         foreach($logs as $log)
        {
            if(!empty($log->date_started))
            {
                $log->date_started = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_started, 'UTC')
            ->setTimezone('America/Los_Angeles');     
            }
            
            if(!empty($log->date_completed))
            {
                $log->date_completed = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_completed, 'UTC')
            ->setTimezone('America/Los_Angeles');       
            }
            
        }
        return view('logsSecondary',compact('logs'));
    }
    
    public function getLogBatches(Request $request)
        {
            $id = $request['id'];
            $logs = log_batches::where('log_id',$id)->orderBy('date_started','asc')->get();
             foreach($logs as $log)
        {
           if(!empty($log->date_started))
            {
                $log->date_started = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_started, 'UTC')
            ->setTimezone('America/Los_Angeles')->format('m/d/Y H:i:s');     
            }
            
            if(!empty($log->date_completed))
            {
                $log->date_completed = Carbon::createFromFormat('Y-m-d H:i:s', $log->date_completed, 'UTC')
            ->setTimezone('America/Los_Angeles')->format('m/d/Y H:i:s');
            } 
        }
            return json_encode($logs);
        }

    
    
    public function getTemplate()
    {
        //PDF file is stored under project/public/download/info.pdf
        $file="./templates/template.csv";
        return Response::download($file);
    }

    public function getWMTemplate()
    {
        $file="./templates/wmtemplate.csv";
        return Response::download($file);
    }
    
    public function index()
    {          
        $setting = amazon_settings::get()->first();
        $prd = products::whereIn('asin', function($query) use($setting){
            $query->select('SKU')
            ->from(with(new order_details)->getTable())
            ->join('orders','order_details.order_id','orders.id')
            ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
            ->groupBy('SKU')
            ->havingRaw('count(*) >= ?', [$setting->soldQty]);
            })            
            ->orWhere('created_at', '>', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
            
        $last_run = $prd->max('modified_at');      
        $products = $prd->select(['products.*', DB::raw("'Primary' As isPrimary")])->paginate(100);

        $strategies = strategies::select()->get(); 
        $accounts = accounts::select()->get(); 
        $strategyCodes = array(); 
        $maxSellers = ceil($prd->max('totalSellers'));
        $maxPrice = ceil($prd->max('price'));
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

    public function secondaryProducts()
    {          

        $setting = amazon_settings::get()->first();
         
        $prd = products::whereNotIn('asin', function($query) use($setting){
            $query->select('SKU')
            ->from(with(new order_details)->getTable())
            ->join('orders','order_details.order_id','orders.id')
            ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
            ->groupBy('SKU')
            ->havingRaw('count(*) >= ?', [$setting->soldQty]);
            })
            ->Where('created_at', '<=', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString());
            $last_run = $prd->max('modified_at');     
            $products = $prd->select(['products.*', DB::raw("'Secondary' As isPrimary")])->paginate(100);


        $strategies = strategies::select()->get(); 
        $accounts = accounts::select()->get(); 
        $strategyCodes = array(); 
        $maxSellers = ceil($prd->max('totalSellers'));
        $maxPrice = ceil($prd->max('price'));
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

        return view('products.secondary',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','minAmount','maxAmount','minSeller','maxSeller','accountFilter','strategyFilter','last_run'));
    }


     public static function getIranTime($date)
    {
        
        $datetime = new \DateTime($date);        
        
        return $datetime->format('m/d/Y H:i:s');
        
    }
    

    public function export(Request $request)
    {        
        $accountFilter = $request->accountFilter;
        $strategyFilter = $request->strategyFilter;
        $sellerFilter = $request->sellerFilter;
        $amountFilter = $request->amountFilter; 

        $filename = date("d-m-Y")."-".time()."-primary-products.xlsx";
        return Excel::download(new ProductsExport($accountFilter,$strategyFilter,$sellerFilter,$amountFilter,1), $filename);
    }

    public function secondaryExport(Request $request)
    {        
        $accountFilter = $request->accountFilter;
        $strategyFilter = $request->strategyFilter;
        $sellerFilter = $request->sellerFilter;
        $amountFilter = $request->amountFilter; 

        $filename = date("d-m-Y")."-".time()."-secondary-products.xlsx";
        return Excel::download(new ProductsExport($accountFilter,$strategyFilter,$sellerFilter,$amountFilter,2), $filename);
    }

    public function filter(Request $request)
    {
        $setting = amazon_settings::get()->first();
        $prd = products::whereIn('asin', function($query) use($setting){
            $query->select('SKU')
            ->from(with(new order_details)->getTable())
            ->join('orders','order_details.order_id','orders.id')
            ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
            ->groupBy('SKU')
            ->havingRaw('count(*) >= ?', [$setting->soldQty]);
            })
            ->orWhere('created_at', '>', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString())
            ->select(['products.*', DB::raw("'Primary' As isPrimary")])
            ;

        $last_run = $prd->max('modified_at');      
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

        $products= $prd; 
                           

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

        $maxSellers = ceil($prd->max('totalSellers'));
        $maxPrice = ceil($prd->max('price'));
        return view('products.index',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','accountFilter','strategyFilter','minAmount','maxAmount','minSeller','maxSeller','last_run'));
    }  
    
    public function secondaryFilter(Request $request)
    {
        $setting = amazon_settings::get()->first();
        $prd = products::whereNotIn('asin', function($query) use($setting){
            $query->select('SKU')
            ->from(with(new order_details)->getTable())
            ->join('orders','order_details.order_id','orders.id')
            ->where('date', '>=', Carbon::now()->subDays($setting->soldDays)->toDateTimeString())
            ->groupBy('SKU')
            ->havingRaw('count(*) >= ?', [$setting->soldQty]);
            })
            ->Where('created_at', '<=', Carbon::now()->subDays($setting->createdBefore)->toDateTimeString())
            ->select(['products.*', DB::raw("'Secondary' As isPrimary")])
            ;

        $last_run = $prd->max('modified_at'); 

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
        $products = $prd;
                           

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

        $maxSellers = ceil($prd->max('totalSellers'));
        $maxPrice = ceil($prd->max('price'));
        return view('products.secondary',compact('products','strategyCodes','strategies','accounts','maxSellers','maxPrice','accountFilter','strategyFilter','minAmount','maxAmount','minSeller','maxSeller','last_run'));
    } 

    public function getFile(Request $request)
    {
        $i = $request->range;   
        if($i==0)
            return redirect()->route('products')->withStatus(__('Please Select Range'));
        
        $filename = date("d-m-Y")."-".time()."-primary-sa-api-export.xlsx";
        return Excel::download(new SellerActiveExport($i, 1), $filename);   
    }

    public function secondaryGetFile(Request $request)
    {
        $i = $request->range;   
        if($i==0)
            return redirect()->route('products')->withStatus(__('Please Select Range'));   
        $filename = date("d-m-Y")."-".time()."-secondary-sa-api-export.xlsx";
        return Excel::download(new SellerActiveExport($i, 2), $filename);   
    }

    public function exportAsins(Request $request)
    {
        $i = $request->range;        
        $filename = date("d-m-Y")."-".time()."-primary-asins-export.csv";
        return Excel::download(new AsinsExport($i,1), $filename);   
    }

    public function secondaryExportAsins(Request $request)
    {
        $i = $request->range;        
        $filename = date("d-m-Y")."-".time()."-secondary-asins-export.csv";
        return Excel::download(new AsinsExport($i,2), $filename);   
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
                
        $status = 'new';
        Repricing::dispatch($collection, $status,3);
                
        Session::flash('success_msg', 'Import in progress. Check logs for details');
        return redirect()->route('products');

    }
    public function uploadWmFile(Request $request)
    {
        $input = [
            'file' => $request->file           
        ];

        $rules = [
            'file'    => 'required'  
        ];

        $tag = $request->route; 

        if($tag == '1')
            $route = 'products';
        else
            $route = 'secondaryproducts';

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            return redirect()->route($route);
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
                return redirect()->route($route);
             }
            

        }
        else
        {
            
        }
        $import = new WMProductsImport;
        Excel::import($import, $filename);
        $collection = $import->data;
        $cnt =0; 
        foreach($collection as $col)
        {
            try{
                $update = products::where('asin',$col['sku'])->update(['wmid'=>$col['id'], 'wmimage'=>$col['link']]);
                if($update)
                $cnt++;
            }
            catch(\Exception $ex)
            {

            }
            
        }   
        
        Session::flash('success_msg', $cnt. ' WM Items Imported Successfully');
        return redirect()->route($route);

    }

    public function manualReprice(Request $request)
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
                $filename = $request->file->storeAs('.', \Str::random(40) . '.' . $file->getClientOriginalExtension(),['disk' => 'imports']);   
                           
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
        //$import = new PricesImport;
        $import = new NewProductsImport;
        Excel::import($import, $filename,'imports');
        $collection = $import->data;
        $url = URL::to('/repricing/imports/').trim($filename, '.');
        $id = new_logs::insertGetId(['date_started'=>date('Y-m-d H:i:s'),'status'=>'In Progress','action'=>'Repricing','upload_link'=> $url]);

        //now send to Informed 
        NewRepricing::dispatch($collection, $id);       
        
        //$cnt = $this->updateManualPricing($collection);

        Session::flash('success_msg', "Repricing is in progress. Check new logs page.");
        return redirect()->route('products');

    }

    public function updateManualPricing($collection)
    {
        $cnt=0;
        
        foreach($collection as $product)
        {
            $prod = products::where('asin',$product['asin'])->get()->first();                
                    
            if(empty($prod))
                continue; 
         
            try{
            
                $strategy = strategies::where('id', $prod->strategy_id)->get()->first();

                if(empty($strategy))
                    continue;

                if($strategy->type==1)
                    $price = ($product['lowestPrice'] + $strategy->value) / (1 - $strategy->breakeven/100);
                else
                    $price = ($product['lowestPrice'] * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
                
                if($product['lowestPrice']!=0)                                                
                    {
                        $prod = products::where('asin',$product['asin'])->update(['lowestPrice'=>$product['lowestPrice'], 'price'=>$price]);                                    
                        $cnt++;
                    }
                else
                {
                    $prod = products::where('asin',$product['asin'])->update(['lowestPrice'=>$product['lowestPrice']]);                                    
                    $cnt++;        
                }
                    
            }   
            catch(\Exception $ex)
            {

            }
        }

        return $cnt;
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

    public function deleteSecondaryProduct($id)
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
            return redirect()->route('secondaryproducts');                          
        else
            Session::flash('success_msg', $sc_id->asin.' Was Deleted Succesfully');
        
        return redirect()->route('secondaryproducts');  
    }
   

    public function repricing()
    {
        $collection = array(); 
        
        $status = 'old';

        Repricing::dispatch($collection, $status, 1);        
        
        Session::flash('success_msg', 'Repricing in progress. Please check logs page for updates.');
        
        return redirect()->route('products');  
    }

    public function secondaryRepricing()
    {
        $collection = array(); 
        
        $status = 'old';

        Repricing::dispatch($collection, $status, 2);        
        
        Session::flash('success_msg', 'Repricing in progress. Please check logs page for updates.');
        
        return redirect()->route('secondaryproducts');  
    }

    public function editAmzProduct(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'title' => $request->get('title'),
        ];  			
        $rules = [
                'id'    => 'required',
                'title' => 'required'                             
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_carrier');
        }     
        	
        $id = $formData['id'];
        $title = $formData['title'];
        
        try{
        $obj = products::find($id);

        $obj->title = $title;        

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Product updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }

}
