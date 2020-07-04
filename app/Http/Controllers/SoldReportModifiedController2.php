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


class SoldReportModifiedController extends Controller
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
                $fromDate = $fromDate->startOfDay();
                $toDate = new Carbon($request->toDate);
                $toDate = $toDate->endOfDay();

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
        
        $daysRange = 0;
        if($request->has('daysRange') && $request->daysRange)
        {
            $daysRange = $request->daysRange;
        }

        $min_sold = 0;
        $max_sold = products::max('sold');

        $forExport = $products;
        $forView   = $products->get();


        /* Start New Code */ 

        $orders = orders::with(['orderDetails'])->get();
        $now = Carbon::now();
        $quantities = [];

        foreach($orders as $order) {
            $daysOld = $order->date->diffInDays($now);

            foreach ($order->orderDetails as $details) {
                if (!isset($quantities[$details->SKU])) {
                    $quantities[$details->SKU]['30'] = 0;
                    $quantities[$details->SKU]['60'] = 0;
                    $quantities[$details->SKU]['90'] = 0;
                    $quantities[$details->SKU]['120'] = 0;
                    $quantities[$details->SKU]['total'] = 0;
                }

                if ($daysOld <= 30) {
                    $quantities[$details->SKU]['30'] += $details->quantity;
                }

                if ($daysOld <= 60) {
                    $quantities[$details->SKU]['60'] += $details->quantity;
                }
                
                if ($daysOld <= 90) {
                    $quantities[$details->SKU]['90'] += $details->quantity;
                }

                if ($daysOld <= 120) {
                    $quantities[$details->SKU]['120'] += $details->quantity;
                }

                $quantities[$details->SKU]['total'] += $details->quantity;
            }
        }

        $mapedProducts =    $forView->map(function ($product) use ($quantities) 
                            {
                                $product->sales30days  = array_key_exists($product->asin, $quantities) ?  $quantities[$product->asin]['30'] : 0;
                                $product->sales60days  = array_key_exists($product->asin, $quantities) ?  $quantities[$product->asin]['60'] : 0;
                                $product->sales90days  = array_key_exists($product->asin, $quantities) ?  $quantities[$product->asin]['90'] : 0;
                                $product->sales120days = array_key_exists($product->asin, $quantities) ?  $quantities[$product->asin]['120'] : 0;
                                $product->salesTotal   = array_key_exists($product->asin, $quantities) ?  $quantities[$product->asin]['total'] : 0;

                                return $product;
                            }); 


                            // return $mapedProducts;
// $ps = $this->paginateWithoutKey($mapedProducts);

// return

            return view('report.soldReport', [
                'products' => $mapedProducts, 
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


        /* End New Code */ 










        if($request->has('btnExport'))
        {
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
