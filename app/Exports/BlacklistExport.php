<?php

namespace App\Exports;

use App\blacklist;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BlacklistExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $blacklist = blacklist::all();
        $dataArray = array();

        foreach($blacklist as $bl)
        {            

            $dataArray[]=  [
                "Date"=>$bl->date,
                "SKU"=>$bl->sku,
                "Reason"=>$bl->reason,
                "Allowance"=>$bl->allowance               
            ];
        }
        
        return collect($dataArray);

    }

    public function headings(): array
    {
        return [
            'Date','SKU','Reason','Allowance'
        ];
    }
}
