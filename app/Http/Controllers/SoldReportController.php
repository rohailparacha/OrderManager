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
        // return $request->all();
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

        
        $daterange = 0;
        if($request->has('daterange') && $request->daterange != 0)
        {
            $daterange = $request->daterange;
        }
        
        $filtered_min_sold = 0;
        if($request->has('min_sold') && $request->min_sold > 0)
        {
            $filtered_min_sold = $request->min_sold;
        }
        
        $filtered_max_sold = 0;
        if($request->has('max_sold') && $request->max_sold > 0)
        {
            $filtered_max_sold = $request->max_sold;
        }
        
        $daysRange = 0;
        if($request->has('daysRange') && $request->daysRange > 0)
        {
            $daysRange = $request->daysRange;

            if($request->daysRange == 30)
            {
                $products = $products->whereBetween('30days', [$filtered_min_sold, $filtered_max_sold]);
            }

            if($request->daysRange == 60)
            {
                $products = $products->whereBetween('60days', [$filtered_min_sold, $filtered_max_sold]);
            }

            if($request->daysRange == 90)
            {
                $products = $products->whereBetween('90days', [$filtered_min_sold, $filtered_max_sold]);
            }

            if($request->daysRange == 120)
            {
                $products = $products->whereBetween('120days', [$filtered_min_sold, $filtered_max_sold]);
            }

        }

        $min_sold = 0;
        $max_sold = products::max('sold');

        if($request->has('btnExport'))
        {
            $products = $products->get();
            return Excel::download(new SoldReportExport($products), 'Sold Report.xlsx');
        }
        
        $products   = $products->paginate(100);

        return view('report.soldReport', [
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
            'daysRange' => $daysRange
            ]
        );
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