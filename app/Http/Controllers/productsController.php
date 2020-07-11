<?php

namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\strategies;
use App\logs;
use App\products;
use App\accounts;
use App\Jobs\Repricing;
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
use App\Imports\PricesImport;
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
        return view('logs',compact('logs'));
    }

    
    public function getTemplate()
    {
        //PDF file is stored under project/public/download/info.pdf
        $file="./templates/template.csv";
        return Response::download($file);
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

    public function getFile()
    {
        $filename = date("d-m-Y")."-".time()."-sa-api-export.csv";
        return Excel::download(new SellerActiveExport(), $filename);  
    }


    public function exportAsins(Request $request)
    {
        $i = $request->range;        
        $filename = date("d-m-Y")."-".time()."-asins-export.csv";
        return Excel::download(new AsinsExport($i), $filename);   
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
        Repricing::dispatch($collection, $status);
        
        Session::flash('success_msg', 'Import in progress. Check logs for details');
        return redirect()->route('products');

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
        $import = new PricesImport;
        Excel::import($import, $filename);
        $collection = $import->data;

       $cnt = $this->updateManualPricing($collection);

        Session::flash('success_msg', 'Manual Repricing Completed Successfully. Total '.$cnt.' products updated');
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

   

    public function repricing()
    {
        $collection = array(); 
        
        $status = 'old';

        Repricing::dispatch($collection, $status);        
        
        Session::flash('success_msg', 'Repricing in progress. Please check logs page for updates.');
        
        return redirect()->route('products');  
    }



}
