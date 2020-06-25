<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\transactions;
use App\bank_accounts;
use App\accounting_categories;
use App\Imports\TransactionsImport;
use Validator;
use Session;
use Excel;
use Response;
use App\Exports\TransactionsExport;
use App\Exports\ProcessedTransactionsExport;

class accountingController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function transactions()
    {
        $transactions = transactions::select(['transactions.*', 'bank_accounts.name', 'accounting_categories.category'])
        ->leftJoin('bank_accounts','bank_accounts.id','=','transactions.bank_id')
        ->leftJoin('accounting_categories','accounting_categories.id','=','transactions.category_id')
        ->whereNull('transactions.category_id')
        ->paginate(100);

        $banks = bank_accounts::select()->get();
        $categories = accounting_categories::select()->get();

        $startDate = transactions::whereNull('transactions.category_id')->min('date');
        $endDate = transactions::whereNull('transactions.category_id')->max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;

        return view('accounting.transactions', compact('transactions','banks','categories','dateRange'));
    }
    
    public function processedtransactions()
    {
        $transactions = transactions::select(['transactions.*', 'bank_accounts.name', 'accounting_categories.category'])
        ->leftJoin('bank_accounts','bank_accounts.id','=','transactions.bank_id')
        ->leftJoin('accounting_categories','accounting_categories.id','=','transactions.category_id')        
        ->whereNotNull('transactions.category_id')
        ->paginate(100);

        $banks = bank_accounts::select()->get();
        $categories = accounting_categories::select()->get();

        $startDate = transactions::whereNotNull('transactions.category_id')->min('date');
        $endDate = transactions::whereNotNull('transactions.category_id')->max('date');

        $from = date("m/d/Y", strtotime($startDate));  
        $to = date("m/d/Y", strtotime($endDate));  

        $dateRange = $from .' - '.$to;

        return view('accounting.processedtransactions', compact('transactions','banks','categories','dateRange'));
    }

    public function getTransaction($id)
    {
        $transaction = transactions::select()->where('id',$id)->get()->first(); 
        echo json_encode($transaction);
        exit; 
    }

    public function uploadSubmit(Request $request)
    {
        $input = [
            'file' => $request->file,
            'bank' => $request->bankTbx           
        ];

        $rules = [
            'file'    => 'required',
            'bank' => 'required|not_in:0'
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File and bank are required'));
            return redirect()->route('transactions');
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['csv','xls','xlsx'];
        
            $file = $request->file('file');
          
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('imports');   
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('ebayProducts');
             }
            

        }
        else
        {
            
        }

        $import = new TransactionsImport;
        Excel::import($import, $filename);
        $collection = $import->data;       
        
    
        
        $this->insertTransactions($request->bankTbx, $collection);
                
        Session::flash('success_msg','Transactions Imported Succsesfully');
        return redirect()->route('transactions');

    }

    public function insertTransactions($bankId, $collection)
    {
        $key=0; 

        foreach($collection as $col)
        {
            if($key==0)
            {
                $key++;
                continue; 
            }
            try{

                $insert = transactions::insert(['date'=>$col['date'],'description'=>$col['description'],'debitAmount'=>$col['debits'],'creditAmount'=>$col['credits'],'bank_id'=>$bankId,'category_id'=>$col['category']]);
            }
            catch(\Exception $ex)
            {

            }
            
        }

    }

    public function assignTransaction(Request $request)
    {
        $rows  = $request->rows; 
        $userId = $request->user;
        
        foreach($rows as $order)
        {
            $id = explode('-',$order)[1];
            $uId = $userId;
            if($id =='all')
                continue;
            $upd = transactions::where('id',$id)->update(['category_id'=>$userId]);
        }
        return "success";
    }

    public function getTemplate()
    {        
        $file="./templates/transactions.csv";
        return Response::download($file);
    }

    public function export(Request $request)
    {        
        $dateRange = $request->dateRange;
        $bankFilter = $request->bankFilter;
        $categoryFilter = $request->categoryFilter;        

        $filename = date("d-m-Y")."-".time()."-transactions.xlsx";
        return Excel::download(new TransactionsExport($dateRange,$categoryFilter,$bankFilter), $filename);
    }

    public function filter(Request $request)
    {
    
        $dateRange = $request->get('dateRange');
        if($request->has('bankFilter'))
            $bankFilter = $request->get('bankFilter');  
        if($request->has('categoryFilter'))
            $categoryFilter = $request->get('categoryFilter');

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

        $transactions = $transactions->whereNull('transactions.category_id')->paginate(100)->appends('categoryFilter',$categoryFilter)->appends('bankFilter',$bankFilter)->appends('dateRange',$dateRange);
        
        $banks = bank_accounts::select()->get();

        $categories = accounting_categories::select()->get();

        return view('accounting.transactions', compact('transactions','banks','categories','dateRange','categoryFilter','bankFilter'));
        
    }

    public function processedexport(Request $request)
    {        
        $dateRange = $request->dateRange;
        $bankFilter = $request->bankFilter;
        $categoryFilter = $request->categoryFilter;        

        $filename = date("d-m-Y")."-".time()."-processed-transactions.xlsx";
        return Excel::download(new ProcessedTransactionsExport($dateRange,$categoryFilter,$bankFilter), $filename);
    }

    public function processedfilter(Request $request)
    {
    
        $dateRange = $request->get('dateRange');
        if($request->has('bankFilter'))
            $bankFilter = $request->get('bankFilter');  
        if($request->has('categoryFilter'))
            $categoryFilter = $request->get('categoryFilter');

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

        $transactions = $transactions->whereNotNull('transactions.category_id')->paginate(100)->appends('categoryFilter',$categoryFilter)->appends('bankFilter',$bankFilter)->appends('dateRange',$dateRange);
        
        $banks = bank_accounts::select()->get();

        $categories = accounting_categories::select()->get();

        return view('accounting.transactions', compact('transactions','banks','categories','dateRange','categoryFilter','bankFilter'));
        
    }


    public function delTransaction($tid)
    {
        transactions::where('id','=',$tid)->delete();        
        return redirect()->route('transactions')->withStatus(__('Transaction successfully deleted.'));
    }

    public function editTransaction(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'date' => $request->get('date'),
            'description' => $request->get('description'),            
            'bank' => $request->get('bank')            
        ];  			
        $rules = [
                'id'    => 'required',
                'date'    => 'required',
                'description'    => 'required',
                'bank'    => 'required|not_in:0',                
                               
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $date = $formData['date'];        
        $description = $formData['description'];        
        $debit = $formData['debit'];        
        $credit = $formData['credit'];        
        $bank = $formData['bank'];        
        $category = $formData['category'];        
        
        try{
        $obj = transactions::find($id);

        $obj->date = $date;
        $obj->description = $description;
        $obj->debitAmount = $debit;
        $obj->creditAmount = $credit;
        $obj->bank_id = $bank;
        if($category>0)
            $obj->category_id = $category;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Transaction updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }


    

}
