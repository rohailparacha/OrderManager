<?php

namespace App\Http\Controllers;

use App\categories; 
use App\accounts;
use App\ebay_products;
use App\ebay_strategies;
use App\Imports\EbayProductsImport;
use App\Exports\EbayProductsExport;
use Illuminate\Support\Facades\File; 
use Validator; 
use Session;
use Redirect;
use Image; 
use Excel; 
use Storage;
use Response;
use Illuminate\Http\Request;

class ebayController extends Controller
{
    //
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
            Image::make($url)->save(public_path('images/ebay/' . $sku.'.jpg'));
        }
        catch(\Exception $ex)
        {

        }
    }

    public function delProduct($product_id)
    {   
        $product = ebay_products::where('id','=',$product_id)->get()->first(); 

        ebay_products::where('id','=',$product_id)->delete();        
         
        try{
            $file1 = public_path('images/ebay/' . $product->sku.'-1.jpg');
            $file2 = public_path('images/ebay/' . $product->sku.'-2.jpg');
            $files = array($file1, $file2);
            File::delete($files);
        }
        catch(\Exception $ex)
        {

        }        

        return redirect()->route('ebayProducts')->withStatus(__('Product successfully deleted.'));
    }

    public function getTemplate()
    {        
        $file="./templates/ebaytemplate.csv";
        return Response::download($file);
    }

    public function index()
    {
        
        $products = ebay_products::select()->orderBy('created_at','desc')->paginate(100);
        $categories = categories::all(); 
        $strategies = ebay_strategies::all();
        $categoryFilter = 0; 
        $strategyFilter = 0;
        $minAmount = 0;
        
        $strategies = ebay_strategies::all(); 
        $categories = categories::all();
        $accounts = accounts::all();
        $strategyArr = array();
        $categoryArr = array();
        
        foreach ($strategies as $strategy)
        {
            $strategyArr[$strategy->id] = $strategy->code;
        }

        foreach ($categories as $category)
        {
            $categoryArr[$category->id] = $category->name;
        }
        
        foreach ($accounts as $account)
        {
            $accountArr[$account->id] = $account->store;
        }
        



        $startDate = ebay_products::min('created_at');
        $endDate = ebay_products::max('created_at');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;

        $maxAmount = ceil(ebay_products::max('ebayPrice'));
                
        $maxPrice  = $maxAmount; 

        return view('ebayProducts',compact('products','strategies','categories','minAmount','maxAmount','categoryFilter','strategyFilter','dateRange','maxPrice','strategyArr','categoryArr','accountArr','accounts'));
    }

    public function filter(Request $request)
    {
       
        if($request->has('categoryFilter'))
            $categoryFilter = $request->get('categoryFilter');
        if($request->has('strategyFilter'))
            $strategyFilter = $request->get('strategyFilter');  
        if($request->has('amountFilter'))
            $amountFilter = $request->get('amountFilter');
        if($request->has('dateRange'))
            $dateRange = $request->get('dateRange');
         $startDate = explode('-',$dateRange)[0];
         $from = date("Y-m-d", strtotime($startDate));  
         $endDate = explode('-',$dateRange)[1];
         $to = date("Y-m-d", strtotime($endDate)); 
        
     
        $minAmount = trim(explode('-',$amountFilter)[0]);
        $maxAmount = trim(explode('-',$amountFilter)[1]);
              

        //now show orders
        $products = ebay_products::select();
                                  
        if(!empty($categoryFilter)&& $categoryFilter !=0)
        {               
            $products = $products->where('category_id',$categoryFilter);
        }

        if(!empty($strategyFilter)&& $strategyFilter !=0)
        {            
            $products = $products->where('strategy_id',$strategyFilter);
        }
        
        $products = $products->whereBetween('ebayPrice',[$minAmount,$maxAmount]);
        
        if(!empty($startDate)&& !empty($endDate))
        {
            $products = $products->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }

        $products  = $products->orderBy('created_at','desc')->paginate(100)->appends('categoryFilter',$categoryFilter)->appends('strategyFilter',$strategyFilter)->appends('dateRange',$dateRange)->appends('amountFilter',$amountFilter);

       
        $categories = categories::all(); 
        $strategies = ebay_strategies::all();
        
        $maxPrice = ceil(ebay_products::max('ebayPrice'));

        $strategies = ebay_strategies::all(); 
        $categories = categories::all();
        $accounts = accounts::all();
        $strategyArr = array();
        $categoryArr = array();
        
        foreach ($strategies as $strategy)
        {
            $strategyArr[$strategy->id] = $strategy->code;
        }

        foreach ($categories as $category)
        {
            $categoryArr[$category->id] = $category->name;
        }
        
        foreach ($accounts as $account)
        {
            $accountArr[$account->id] = $account->store;
        }

        return view('ebayProducts',compact('products','strategies','categories','minAmount','maxAmount','categoryFilter','strategyFilter','dateRange','maxPrice','strategyArr','categoryArr','accountArr','accounts'));

    }

    public function getProduct($id)
    {
        $product = ebay_products::select()->where('id',$id)->get()->first(); 
        echo json_encode($product);
        exit; 
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
            return redirect()->route('ebayProducts');
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
                return redirect()->route('ebayProducts');
             }
            

        }
        else
        {
            
        }

        $import = new EbayProductsImport;
        Excel::import($import, $filename);
        $collection = $import->data;        
        
        $this->updateProducts($collection);
       
        Session::flash('success_msg','Products Modified/Deleted Succsesfully');
        return redirect()->route('ebayProducts');

    }

    public function updateProducts($collection)
    {        
        foreach($collection as $prod)
        {
            if(strtolower($prod['action'])=='modify')
            {

            
            $strategy = ebay_strategies::where('code',$prod['strategy'])->get()->first();                
                        
            if(empty($strategy))
                continue; 

              
                $product = ebay_products::where('sku',$prod['sku'])->get()->first();

                if($strategy->type==1)
                    $price = ($product->ebayPrice + $strategy->value) / (1 - $strategy->breakeven/100);
                else
                    $price = ($product->ebayPrice * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
                
                if($product->ebayPrice==0)
                    $price = 99.99;
                    
                $pro = ebay_products::where('sku','=',(string)$prod["sku"])->update(['strategy_id'=>$strategy->id, 'price'=>$price]);
            }

            else  if(strtolower($prod['action'])=='delete')
            {                
                ebay_products::where('sku','=',(string)$prod["sku"])->delete();        
            }
            else
                continue;
        }
    }

    public function addProduct(Request $request)
    {

        $input = [
            'sku' => $request->get('sku'),
            'name' => $request->get('name'),
            'productId' => $request->get('id'),
            'desc' => $request->get('desc'),
            'brand' => $request->get('brand'),
            'primaryImg' => $request->get('primaryImg'),            
            'price' => $request->get('price'),
            'idType' => $request->get('idType'),
            'strategy' => $request->get('strategy'),
            'category' => $request->get('category'),
            'account' => $request->get('account'),

        ];  			

        $rules = [
                'sku'    => 'required|unique:ebay_products',
                'name'    => 'required|string|max:200',
                'productId'    => 'required|unique:ebay_products',
                'desc'    => 'required|string|max:4000',
                'brand'    => 'required|max:60',
                'primaryImg'    => 'required',                
                'price'    => 'required|numeric',
                'idType'    => 'required|not_in:0',
                'strategy'    => 'required|not_in:0',
                'category'    => 'required|not_in:0',
                'account'    => 'required|not_in:0'                          
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {                    
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        
        $price = 0;
        
        $strategy = ebay_strategies::where('id',$formData['strategy'])->get()->first();    
        $ebayPrice = $formData['price'];

        if($strategy->type==1)
            $price = ($ebayPrice + $strategy->value) / (1 - $strategy->breakeven/100);
        else
            $price = ($ebayPrice * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);

        $data = [
        'sku' =>  $formData['sku'],
        'name' =>  $formData['name'],
        'productIdType' =>  $formData['idType'],
        'productId' =>  $formData['id'],
        'description' =>  $formData['desc'],
        'brand' =>  $formData['brand'],
        'account_id'=>$formData['account'],
        'primaryImg' =>  $formData['primaryImg'],
        'secondaryImg' =>  $formData['secondaryImg'],
        'ebayPrice' =>  $formData['price'],
        'category_id' =>  $formData['category'],
        'strategy_id' =>  $formData['strategy'],
        'price' => $price
    
        ];
        
        $created = ebay_products::insert($data);	
            
        $this->saveImage($formData['sku'].'-1',$formData['primaryImg']);        
        $this->saveImage($formData['sku'].'-2',$formData['secondaryImg']);    

        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Product added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }

    public function updateProduct(Request $request)
    {

        $input = [
            'pid' =>$request->get('pid'),             
            'sku' => $request->get('sku'),
            'name' => $request->get('name'),
            'productId' => $request->get('id'),
            'desc' => $request->get('desc'),
            'brand' => $request->get('brand'),
            'primaryImg' => $request->get('primaryImg'),            
            'price' => $request->get('price'),
            'idType' => $request->get('idType'),
            'strategy' => $request->get('strategy'),
            'category' => $request->get('category'),
            'account' => $request->get('account'),

        ];  			

        $rules = [
                'pid' => 'required',
                'sku'    => 'required',
                'name'    => 'required|string|max:200',
                'productId'    => 'required|unique:ebay_products,productId,' . $request->get('pid'),
                'desc'    => 'required|string|max:4000',
                'brand'    => 'required|max:60',
                'primaryImg'    => 'required',
                'price'    => 'required|numeric',
                'idType'    => 'required|not_in:0',
                'strategy'    => 'required|not_in:0',
                'category'    => 'required|not_in:0',
                'account'    => 'required|not_in:0'                                
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {                    
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        $strategy = ebay_strategies::where('id',$formData['strategy'])->get()->first();    
        $price=0;
        $pid = $formData['pid'];
        $ebayPrice = $formData['price'];

        if($strategy->type==1)
            $price = ($ebayPrice + $strategy->value) / (1 - $strategy->breakeven/100);
        else
            $price = ($ebayPrice * (1 + $strategy->value)) / (1 - $strategy->breakeven/100);
         
        
        $data = [
        'sku' =>  $formData['sku'],
        'name' =>  $formData['name'],
        'productIdType' =>  $formData['idType'],
        'productId' =>  $formData['id'],
        'description' =>  $formData['desc'],
        'account_id'=>$formData['account'],
        'brand' =>  $formData['brand'],
        'primaryImg' =>  $formData['primaryImg'],
        'secondaryImg' =>  $formData['secondaryImg'],
        'ebayPrice' =>  $formData['price'],
        'category_id' =>  $formData['category'],
        'strategy_id' =>  $formData['strategy'],
        'price' => $price
    
        ];
        
		$created = ebay_products::where('id',$pid)->update($data);		 
        
        
        $this->saveImage($formData['sku'].'-1',$formData['primaryImg']);        
        $this->saveImage($formData['sku'].'-2',$formData['secondaryImg']);    

        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Product Updated successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }

    public function export(Request $request)
    {        
        $categoryFilter = $request->categoryFilter;
        $strategyFilter = $request->strategyFilter;
        $daterange = $request->daterange;
        $amountFilter = $request->amountFilter; 

        $filename = date("d-m-Y")."-".time()."-ebay-products.xlsx";
        return Excel::download(new EbayProductsExport($categoryFilter,$strategyFilter,$daterange,$amountFilter), $filename);
    }
}
