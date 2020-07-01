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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use DB;


class SoldReportController extends Controller
{

    public function index(Request $request, $asin = 0)
    {

        Log::debug(print_r($request->all(), true));
        $stores = products::distinct('account')->pluck('account');

        $products = products::query();

        if($asin !== 0)
        {
            $products = $products->where(['asin' => $asin]);
        }

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
            // $products = $products->where('sold', '>=', $request->min_sold);
        }

        $filtered_max_sold = 0;
        if($request->has('max_sold') && $request->max_sold > 0)
        {
            $filtered_max_sold = $request->max_sold;
            // $products = $products->where('sold', '<=', $request->max_sold);
        }
        
        $daterange = 0;
        if($request->has('daterange') && $request->daterange != 0)
        {
            $daterange = $request->daterange;
        }
        
        $min_sold = 0;
        $max_sold = products::max('sold');

        $forExport = $products;
        $forView   = $products;

        if($request->has('btnExport'))
        {
            // return $forExport->get()->count();
            // DB::enableQueryLog();
            // $products = $forExport->get();
            $products = $forExport->paginate(600);
            // return $forExport->get()->count();

            // dd(DB::getQueryLog());            

            // ini_set('max_execution_time', 300);

            // $products->chunk(200, function($prds)
            // {
            //     foreach($prds as $product)
            //     {
            //         $orderIds = order_details::where('SKU','=',$product->asin)->pluck('order_id');
                    
            //         $product->sales30days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()])->sum('quantity');
            //         $product->sales60days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(60), Carbon::now()])->sum('quantity');
            //         $product->sales90days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(90), Carbon::now()])->sum('quantity');
            //         $product->sales120days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(120), Carbon::now()])->sum('quantity');
            //         $product->totalSold = orders::whereIn('id', $orderIds)->sum('quantity');
            //     }
            // });

            foreach($products as $product)
            {
                $orderIds = order_details::where('SKU','=',$product->asin)->pluck('order_id');
                
                $product->sales30days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()])->sum('quantity');
                $product->sales60days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(60), Carbon::now()])->sum('quantity');
                $product->sales90days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(90), Carbon::now()])->sum('quantity');
                $product->sales120days = orders::whereIn('id', $orderIds)->whereBetween('date', [Carbon::now()->subDays(120), Carbon::now()])->sum('quantity');
                $product->totalSold = orders::whereIn('id', $orderIds)->sum('quantity');
            }

            $daysRange = 0;
            if($request->has('daysRange') && $request->daysRange)
            {
                $daysRange = $request->daysRange;
                $filtered = $products->reject(function ($value, $key) use($request, $filtered_min_sold, $filtered_max_sold) {
                    // Log::debug('Rejecting value '. $value);
                    
                    if($request->daysRange == 30)
                    {
                        // Log::debug('Entered into 30 days filter');
                        // Log::debug('value->sales30days : ' . $value->sales30days . ' filtered_min_sold : ' . $filtered_min_sold . ' filtered_max_sold :  ' . $filtered_max_sold);
                        if($value->sales30days < $filtered_min_sold || $value->sales30days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 60)
                    {
                        // Log::debug('Entered into 60 days filter');
                        if($value->sales60days < $filtered_min_sold || $value->sales60days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 90)
                    {
                        if($value->sales90days < $filtered_min_sold || $value->sales90days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 120)
                    {
                        if($value->sales120days < $filtered_min_sold || $value->sales120days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }
                });
                // $ps = $this->paginateWithoutKey($filtered);
            } 

            return Excel::download(new SoldReportExport($filtered), 'Sold Report.xlsx');
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

            $daysRange = 0;
            if($request->has('daysRange') && $request->daysRange)
            {
                $daysRange = $request->daysRange;
                $filtered = $ps->reject(function ($value, $key) use($request, $filtered_min_sold, $filtered_max_sold) {
                    // Log::debug('Rejecting value '. $value);
                    
                    if($request->daysRange == 30)
                    {
                        // Log::debug('Entered into 30 days filter');
                        // Log::debug('value->sales30days : ' . $value->sales30days . ' filtered_min_sold : ' . $filtered_min_sold . ' filtered_max_sold :  ' . $filtered_max_sold);
                        if($value->sales30days < $filtered_min_sold || $value->sales30days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 60)
                    {
                        // Log::debug('Entered into 60 days filter');
                        if($value->sales60days < $filtered_min_sold || $value->sales60days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 90)
                    {
                        if($value->sales90days < $filtered_min_sold || $value->sales90days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }

                    if($request->daysRange == 120)
                    {
                        if($value->sales120days < $filtered_min_sold || $value->sales120days > $filtered_max_sold)
                        {
                            return true;
                        }
                    }
                });

                $ps = $this->paginateWithoutKey($filtered);
            } 

            return view('report.soldReport', [
                'products' => $ps, 
                'stores' => $stores, 
                'fromDate' => $fromDate, 
                'toDate' => $toDate, 
                'daterange' => $daterange, 
                'storeName'=> $storeName,
                'min_sold' => $min_sold, 
                'max_sold' => $max_sold, 
                'filtered_min_sold' => $filtered_min_sold,
                'filtered_max_sold' => $filtered_max_sold,
                'daysRange' => $daysRange
                ]
            );



        }

    }

    public function paginateWithoutKey($items, $perPage = 100, $page = null, $options = [])
    {

        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);

        $items = $items instanceof Collection ? $items : Collection::make($items);

        // $lap = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        $lap = new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);

        return $lap;
        // return [
        //     'current_page' => $lap->currentPage(),
        //     'data' => $lap->values(),
        //     'first_page_url' => $lap->url(1),
        //     'from' => $lap->firstItem(),
        //     'last_page' => $lap->lastPage(),
        //     'last_page_url' => $lap->url($lap->lastPage()),
        //     'next_page_url' => $lap->nextPageUrl(),
        //     'per_page' => $lap->perPage(),
        //     'prev_page_url' => $lap->previousPageUrl(),
        //     'to' => $lap->lastItem(),
        //     'total' => $lap->total(),
        // ];
    }
    
}
