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

        if($request->has('min_sold') && $request->min_sold > 0)
        {
            $products = $products->where('sold', '>=', $request->min_sold);
        }


        if($request->has('max_sold') && $request->max_sold > 0)
        {
            $products = $products->where('sold', '<=', $request->max_sold);
        }
        
        $daterange = 0;

        if($request->has('daterange') && $request->daterange != 0)
        {
            $daterange = $request->daterange;
        }

        if($request->has('btnExport'))
        {
            $products =  $products->get();
            return Excel::download(new ProductReportExport($products), 'Products.xlsx');
        }

        $min_sold = 0;
        $max_sold = products::max('sold');

        $products = $products->paginate(100);

        return view('report.productReport', ['products' => $products, 'stores' => $stores, 'min_sold' => $min_sold, 'max_sold' => $max_sold, 'fromDate' => $fromDate, 'toDate' => $toDate, 'daterange' => $daterange, 'storeName'=> $storeName]);
    }

    
}
