<?php

namespace App\Exports;
use App\transactions;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromCollection,WithHeadings,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $dateRange; 
    protected $bankFilter; 
    protected $categoryFilter; 
  
    public function __construct($dateRange,$categoryFilter,$bankFilter)
    {
        $this->categoryFilter = $categoryFilter;
        $this->bankFilter = $bankFilter;
        $this->dateRange = $dateRange;        
    }


    public function collection()
    {        
        $categoryFilter = $this->categoryFilter;
        $bankFilter = $this->bankFilter;
        $dateRange = $this->dateRange;        

       //now show orders
       $startDate = explode('-',$dateRange)[0];
       $from = date("Y-m-d", strtotime($startDate));  
       $endDate = explode('-',$dateRange)[1];
       $to = date("Y-m-d", strtotime($endDate)); 
      
       
       $transactions = transactions::select(['transactions.*', 'bank_accounts.name', 'accounting_categories.category'])
       ->leftJoin('bank_accounts','bank_accounts.id','=','transactions.bank_id')
       ->leftJoin('accounting_categories','accounting_categories.id','=','transactions.category_id');
       

       if(!empty($bankFilter)&& $bankFilter !=0)
       {
           $transactions = $transactions->where('transactions.bank_id',$bankFilter);
       }
      
       if(!empty($categoryFilter)&& $categoryFilter !=0)
       {         
           $transactions = $transactions->where('transactions.category_id',$categoryFilter);
       }

       if(!empty($startDate)&& !empty($endDate))
       {
           $transactions = $transactions->whereBetween('date', [$from.' 00:00:00', $to.' 23:59:59']);
       }

        $transactions = $transactions->whereNull('transactions.category_id')->get();
        
        $dataArray = array();

        foreach($transactions as $transaction)
        {

            $dataArray[]=  [                
                "Date" =>date_format(date_create($transaction->date), 'm/d/Y'),
                "Bank Account" => $transaction->name,
                "Description"=>$transaction->description,              
                "Debit Amount"=>number_format((float)$transaction->debitAmount, 2, '.', ''),
                "Credit Amount"=>number_format((float)$transaction->creditAmount, 2, '.', ''),
                "Category"=>$transaction->category,             
        ];
        }
        
        return collect($dataArray);
        
    }

    public function headings(): array
    {
        return [
            'Date','Bank Account','Description','Debit Amount','Credit Amount','Category'
        ];
    }
}
