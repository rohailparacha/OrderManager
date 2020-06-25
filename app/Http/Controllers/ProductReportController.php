<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\products;
use DataTables;
use Carbon\Carbon;
use App\orders;
use App\order_details;
use App\returns;
use App\cancelled_orders;

// use App\blacklist;
// use App\settings;
// use App\walmart_products;
// use DB;
// use App\User;
// use App\states;
// use App\accounts;
// use App\gmail_accounts;
// use App\carriers;
// use App\strategies;
// use App\ebay_products;
// use App\ebay_trackings;
// use App\ebay_strategies;
// use App\categories;
// use App\Exports\OrdersExport;
// use GuzzleHttp\Client;
// use Hash;
// use Auth; 
// use Illuminate\Support\Facades\Input;
// use Validator; 
// use Session;
// use Redirect;
// use Excel;


class ProductReportController extends Controller
{

    public function index(Request $request)
    {
        // $products = products::query();

        // return $products;

        if($request->ajax())
        {
            // $products = products::with(['orderDetails'])->take(500)->get();  // WORKING
            // $products = products::with(['orderDetails'])->get();
            // $products = products::get(['id', 'asin', 'account', 'created_at']);
    
            // return view('test', ['products' => $products]);

            $products = products::query();

            // $products = $products->where('account', 'Snapp!');

            return DataTables::of($products)
                ->addColumn('sold', function(products $product) {

                    $details = order_details::groupBy('SKU')->where(['SKU' => $product->asin])
                                ->selectRaw('sum(quantity) as sold')
                                ->get();

                    $sold = 0;

                    if(!empty($details))
                    {
                        foreach($details as $detail)
                        {
                            $sold += $detail->sold;
                        }
                    }

                    return $sold;
                })

                ->addColumn('returned', function(products $product) {
                    $returned = 0;

                    $details = order_details::where(['SKU' => $product->asin])->get();

                    if($details)
                    {
                        foreach($details as $detail)
                        {
                            $returns = returns::where(['order_id' => $detail->order_id])->get();
                            
                            if($returns)
                            {
                                foreach($returns as $return)
                                {
                                    $o = order_details::where(['order_id' => $return->order_id])->first();
                                    $returned += $o->quantity;
                                }
                            }
                        }
                    }
                    return $returned;
                })

                ->addColumn('cancelled', function(products $product) {
                    $cancelled = 0;

                    $details = order_details::where(['SKU' => $product->asin])->get();

                    if($details)
                    {
                        foreach($details as $detail)
                        {
                            $cancels = cancelled_orders::where(['order_id' => $detail->order_id])->get();
                            
                            if($cancels)
                            {
                                foreach($cancels as $cancel)
                                {
                                    $c = order_details::where(['order_id' => $cancel->order_id])->first();
                                    $cancelled += $c->quantity;
                                }
                            }
                        }
                    }
                    return $cancelled;
                })

                ->addColumn('net', function(products $product) {

                    $details = order_details::groupBy('SKU')->where(['SKU' => $product->asin])
                                ->selectRaw('sum(quantity) as sold')
                                ->get();

                    $sold = 0;

                    if(!empty($details))
                    {
                        foreach($details as $detail)
                        {
                            $sold += $detail->sold;
                        }
                    }

                    $returned = 0;

                    $details = order_details::where(['SKU' => $product->asin])->get();

                    if($details)
                    {
                        foreach($details as $detail)
                        {
                            $returns = returns::where(['order_id' => $detail->order_id])->get();
                            
                            if($returns)
                            {
                                foreach($returns as $return)
                                {
                                    $o = order_details::where(['order_id' => $return->order_id])->first();
                                    $returned += $o->quantity;
                                }
                            }
                        }
                    }
                 
                 
                    $cancelled = 0;

                    $details = order_details::where(['SKU' => $product->asin])->get();

                    if($details)
                    {
                        foreach($details as $detail)
                        {
                            $cancels = cancelled_orders::where(['order_id' => $detail->order_id])->get();
                            
                            if($cancels)
                            {
                                foreach($cancels as $cancel)
                                {
                                    $c = order_details::where(['order_id' => $cancel->order_id])->first();
                                    $cancelled += $c->quantity;
                                }
                            }
                        }
                    }
               
                    $net = $sold - $returned - $cancelled;
                    return $net;
                })
                ->make(true);
        }

        return view('report.productReport');
    }

    
}
