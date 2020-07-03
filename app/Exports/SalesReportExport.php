<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class SalesReportExport implements FromView, WithStrictNullComparison
{    
    protected $stores;
    protected $dates;
    protected $data;
    protected $chartType;

    public function __construct($stores, $dates, $data, $chartType)
    {
        $this->stores = $stores;
        $this->dates = $dates;
        $this->data = $data;
        $this->chartType = $chartType;
    }

    public function view(): View
    {
        return view('report.exports.salesReport', [
            'stores' => $this->stores,
            'dates' => $this->dates,
            'data' => $this->data,
            'chartType' => $this->chartType
        ]);
    }

}
