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
use App\carriers;
use Log;
use App\Exports\ProductReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductReportController extends Controller
{

    public function index(Request $request)
    {
        // Log::debug(print_r($request->all(), true));
        $stores = products::distinct('account')->pluck('account');

        $products = products::query();

        $storeName = " ";
        if($request->has('storeName'))
        {
            if($request->storeName != "")
            {
                $storeName = $request->storeName;
                $products = $products->where(['account' => $request->storeName]);
            }
        }

        $fromDate = 0;
        $toDate = 0;
        if($request->has('fromDate') && $request->has('toDate'))
        {
            if($request->fromDate != 0  && $request->toDate != 0)
            {
                $fromDate = new Carbon($request->fromDate);
                $toDate = new Carbon($request->toDate);
                $products = $products->whereBetween('created_at', [$fromDate, $toDate]);
            }
        }

        $filtered_min_sold = 0;
        if($request->has('min_sold') && $request->min_sold > 0)
        {
            $filtered_min_sold = $request->min_sold;
            $products = $products->where('sold', '>=', $request->min_sold);
        }

        $filtered_max_sold = 0;
        if($request->has('max_sold') && $request->max_sold > 0)
        {
            $filtered_max_sold = $request->max_sold;
            $products = $products->where('sold', '<=', $request->max_sold);
        }
        
        $filtered_min_returned = 0;
        if($request->has('min_returned') && $request->min_returned > 0)
        {
            $filtered_min_returned = $request->min_returned;
            $products = $products->where('returned', '>=', $request->min_returned);
        }

        $filtered_max_returned = 0;
        if($request->has('max_returned') && $request->max_returned > 0)
        {
            $filtered_max_returned = $request->max_returned;
            $products = $products->where('returned', '<=', $request->max_returned);
        }
        

        $filtered_min_cancelled = 0;
        if($request->has('min_cancelled') && $request->min_cancelled > 0)
        {
            $filtered_min_cancelled = $request->min_cancelled;
            $products = $products->where('cancelled', '>=', $request->min_cancelled);
        }

        $filtered_max_cancelled = 0;
        if($request->has('max_cancelled') && $request->max_cancelled > 0)
        {
            $filtered_max_cancelled = $request->max_cancelled;
            $products = $products->where('cancelled', '<=', $request->max_cancelled);
        }

        $daterange = 0;
        if($request->has('daterange') && $request->daterange != 0)
        {
            $daterange = $request->daterange;
        }
        
        $min_sold = 0;
        $max_sold = products::max('sold');
        
        $min_returned = 0;
        $max_returned = products::max('returned');
        
        $min_cancelled = 0;
        $max_cancelled = products::max('cancelled');
        
        
        if($request->has('btnExport'))
        {
            $products =  $products->get();
            return Excel::download(new ProductReportExport($products), 'Products.xlsx');
        }
        $products = $products->paginate(100);

        return view('report.productReport', [
            'products' => $products, 
            'stores' => $stores, 
            'fromDate' => $fromDate, 
            'toDate' => $toDate, 
            'daterange' => $daterange, 
            'storeName'=> $storeName,

            'min_sold' => $min_sold, 
            'max_sold' => $max_sold, 
            'filtered_min_sold' => $filtered_min_sold,
            'filtered_max_sold' => $filtered_max_sold,

            'min_returned' => $min_returned, 
            'max_returned' => $max_returned, 
            'filtered_min_returned' => $filtered_min_returned,
            'filtered_max_returned' => $filtered_max_returned,

            'min_cancelled' => $min_cancelled, 
            'max_cancelled' => $max_cancelled, 
            'filtered_min_cancelled' => $filtered_min_cancelled,
            'filtered_max_cancelled' => $filtered_max_cancelled,

            ]
        );
    }



    public function orders(Request $request)
    {

        if(!$request->has('asin') || !$request->has('status'))
        {
            return redirect()->back();
        }

        if($request->status == 'sold')
        {
            $order_details = order_details::with(
                [
                    'order' => function($order)
                    {
                        $order->where(['status' => 'shipped']);
                    },
                    'asin'
                ]
            )->where(['SKU' => $request->asin])->paginate(100);
        }


        if($request->status == 'returned')
        {
            $returns  = returns::pluck('order_id');
            $order_details = order_details::with(['order'])->whereIn('order_id', $returns)->where(['SKU' => $request->asin])->paginate(10);
        }


        if($request->status == 'cancelled')
        {
            $cancelled  = cancelled_orders::pluck('order_id');
            $order_details = order_details::with(['order'])->whereIn('order_id', $cancelled)->where(['SKU' => $request->asin])->paginate(10);
        }


        $carriers = carriers::all(); 
        $carrierArr = array(); 
        foreach($carriers as $carrier)
        {
            $carrierArr[$carrier->id]= $carrier->name; 
        }

        if($order_details)
        {
            return view('report.orders',['order_details' => $order_details,'carrierArr' => $carrierArr]);
        }else{
            return 'No orders found';
        }

    }
    
}
