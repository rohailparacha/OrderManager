<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\products;
use DataTables;
use Carbon\Carbon;
use App\orders;
use App\order_details;
use App\returns;
use Log;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use DB;


class SalesReportController extends Controller
{

    public function index(Request $request)
    {
        // Log::debug(print_r($request->all(), true));
        // return $request->all();
        $storesForView = orders::distinct('storeName')->pluck('storeName');

        $orders = orders::query();

        $storeName = " ";
        if($request->has('storeName'))
        {
            if($request->storeName != "")
            {
                $storeName = $request->storeName;
                $orders = $orders->where(['storeName' => $request->storeName]);
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
                
                // Log::debug('From Date : ' .$fromDate .' and to date : ' .$toDate);

                if($fromDate->diffInDays($toDate) > 31 )
                {
                    return 'Please select maximum 30 days range';
                }

            }
        }else{
            $fromDate = Carbon::now()->subDays(6);
            $toDate = Carbon::now();
        }
        
        $orders = $orders->whereBetween('date', [$fromDate, $toDate]);

        $daterange = 0;
        if($request->has('daterange') && $request->daterange != 0)
        {
            $daterange = $request->daterange;
        }


        $chartType = 'amt';
        if($request->has('chartType') && $request->chartType !== 0)
        {
            $chartType = $request->chartType;
        }

        $orders =   $orders
                    ->select(DB::raw('SUM(orders.quantity) as total_quantity'), DB::raw('SUM(orders.totalAmount) as total_amount'), DB::raw('count(orders.id) as count'), DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as o_date"), 'storeName')
                    ->groupBy('o_date', 'storeName');

        $data =  $orders->get()->toArray();
        
        // return $data;

        $dates = array_reduce($data, function ($dates, $record) {
            if( !in_array($record['o_date'], $dates)) {
                $dates[] = $record['o_date'];
            }
            return $dates;
        }, []);

        $stores = array_reduce($data, function ($stores, $record) {
            if (!in_array($record['storeName'], $stores)) {        
                $stores[] = $record['storeName'];
            }
            return $stores;
        }, []);
        
        $headers = $stores;
        $headerForChart = $stores;

        array_unshift($headerForChart , 'Stores');
        $headerForChart[] = [ 'role' => 'annotation' ];

        $chart = [ $headerForChart ];

        if($request->has('btnExport'))
        {
            return Excel::download(new SalesReportExport($stores, $dates, $data, $chartType), 'Sales Report.xlsx');
        }

        if($chartType == 'amt')
        {
            foreach ($dates as $date) {
                $datapoint = [ $date ];
    
                foreach ($stores as $store) {
                    $total = 0;
    
                    foreach ($data as $record) {
                        $isStore = $record['storeName'] === $store;
                        $isDate = $record['o_date'] === $date;
                        if($isStore && $isDate) {
                            $total += (float) $record['total_amount'];
                        }
                    }
                    $datapoint[] = $total;
                }
                $datapoint[] = '';
    
                $chart[] = $datapoint;
            }


        }else{
            foreach ($dates as $date) {
                $datapoint = [ $date ];
    
                foreach ($stores as $store) {
                    $qty = 0;
    
                    foreach ($data as $record) {
                        $isStore = $record['storeName'] === $store;
                        $isDate = $record['o_date'] === $date;
                        if($isStore && $isDate) {
                            $qty += $record['total_quantity'];
                        }
                    }
                    $datapoint[] = $qty;
                }
                $datapoint[] = '';
    
                $chart[] = $datapoint;
            }

        }


        return view('report.salesReport', [
            'orders' => $orders, 
            'stores' => $storesForView, 
            'fromDate' => $fromDate, 
            'toDate' => $toDate, 
            'daterange' => $daterange, 
            'storeName'=> $storeName,
            'chartType' => $chartType, 
            'chart' => $chart,
            ]
        );
    }

}
