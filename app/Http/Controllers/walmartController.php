<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\walmart_products; 
use App\categories; 
use App\ebay_strategies; 
use App\accounts; 
use App\Exports\WalmartProductsExport;
use Excel;

class walmartController extends Controller
{
    //
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        
        $products = walmart_products::select()->orderBy('created_at','desc')->paginate(100);        
        $sellersFilter = 0;
        $minAmount = 0;
        
        $startDate = walmart_products::min('created_at');
        $endDate = walmart_products::max('created_at');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;

        $maxAmount = ceil(walmart_products::max('price'));
                
        $maxPrice  = $maxAmount; 

        $sellers = walmart_products::distinct()->get(['seller']);
        
        return view('walmartProducts',compact('products','sellers','minAmount','maxAmount','sellersFilter','dateRange','maxPrice'));
    }

    public function filter(Request $request)
    {
       
        if($request->has('sellersFilter'))
            $sellersFilter = $request->get('sellersFilter');
 
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
        $products = walmart_products::select();
                                  
        
        $products = $products->whereBetween('price',[$minAmount,$maxAmount]);
        
        if(!empty($startDate)&& !empty($endDate))
        {
            $products = $products->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
        }
        
        if(!empty($sellersFilter)&& $sellersFilter !='0')
        {            
            $products = $products->where('seller',$sellersFilter);
        }
        
        $products  = $products->orderBy('created_at','desc')->paginate(100)->appends('sellersFilter',$sellersFilter)->appends('dateRange',$dateRange)->appends('amountFilter',$amountFilter);

             
        
        $maxPrice = ceil(walmart_products::max('price'));
        
        $sellers = walmart_products::distinct()->get(['seller']);

        return view('walmartProducts',compact('products','sellers','minAmount','maxAmount','sellersFilter','dateRange','maxPrice'));

    }

    public function export(Request $request)
    {        
        $sellersFilter = $request->sellersFilter;
        $daterange = $request->daterange;
        $amountFilter = $request->amountFilter; 

        $filename = date("d-m-Y")."-".time()."-walmart-products.xlsx";
        return Excel::download(new WalmartProductsExport($sellersFilter,$daterange,$amountFilter), $filename);
    }

    public function delProduct($product_id)
    {   
        walmart_products::where('id','=',$product_id)->delete();        
        return redirect()->route('walmartProducts')->withStatus(__('Product successfully deleted.'));
    }
}
