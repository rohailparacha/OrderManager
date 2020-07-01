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
use Log;
use App\Exports\SoldReportExport;
use Maatwebsite\Excel\Facades\Excel;


class SoldReportController extends Controller
{

    public function index(Request $request)
    {
        Log::debug(print_r($request->all(), true));
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

        // $fromDate = Carbon::now()->subDays(7);
        // $toDate = Carbon::now();
        // if($request->has('fromDate') && $request->has('toDate'))
        // {
        //     if($request->fromDate != 0  && $request->toDate != 0)
        //     {
        //         $fromDate = new Carbon($request->fromDate);
        //         $toDate = new Carbon($request->toDate);
        //         $products = $products->whereBetween('created_at', [$fromDate, $toDate]);
        //     }
        // }

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
        
        // $daterange = 0;
        // if($request->has('daterange') && $request->daterange != 0)
        // {
        //     $daterange = $request->daterange;
        // }
        
        $min_sold = 0;
        $max_sold = products::max('sold');

        $forExport = $products;
        $forView   = $products;

        if($request->has('btnExport'))
        {
            $products = $forExport->get();
            foreach($products as $product)
            {
                $orderIds = order_details::where('SKU','=',$product->asin)->pluck('order_id');
                
                $product->sales30days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()])->sum('quantity');
                $product->sales60days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(60), Carbon::now()])->sum('quantity');
                $product->sales90days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(90), Carbon::now()])->sum('quantity');
                $product->sales120days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(120), Carbon::now()])->sum('quantity');
                $product->totalSold = orders::whereIn('id', $orderIds)->sum('quantity');
            }

            return Excel::download(new SoldReportExport($products), 'Sold Report.xlsx');
        }else{

            $ps   = $forView->paginate(100);

            foreach($ps as $product)
            {
                $orderIds = order_details::where('SKU','=',$product->asin)->pluck('order_id');
                
                $product->sales30days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()])->sum('quantity');
                $product->sales60days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(60), Carbon::now()])->sum('quantity');
                $product->sales90days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(90), Carbon::now()])->sum('quantity');
                $product->sales120days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(120), Carbon::now()])->sum('quantity');
                $product->totalSold = orders::whereIn('id', $orderIds)->sum('quantity');
            }

            return view('report.soldReport', [
                'products' => $ps, 
                'stores' => $stores, 
                // 'fromDate' => $fromDate, 
                // 'toDate' => $toDate, 
                // 'daterange' => $daterange, 
                'storeName'=> $storeName,

                // 'min_sold' => $min_sold, 
                // 'max_sold' => $max_sold, 
                // 'filtered_min_sold' => $filtered_min_sold,
                // 'filtered_max_sold' => $filtered_max_sold,
                ]
            );



        }

    }


    public function orders(Request $request)
    {

        if(!$request->has('asin') || !$request->has('status'))
        {
            return redirect()->back();
        }

        if($request->status == 'sold')
        {
            $order_details = order_details::with(['order' => function($order){
                $order->where(['status' => 'shipped']);
            }])->where(['SKU' => $request->asin])->paginate(100);
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


        if($order_details)
        {
            return view('report.orders',['order_details' => $order_details]);
        }else{
            return 'No orders found';
        }

    }
    
}
