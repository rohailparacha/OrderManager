<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\bank_accounts;
use App\transactions;
use Response; 
use Validator;
use Session;

class bankAccountsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $accounts = bank_accounts::select()->paginate(100);
        return view('accounting.bankAccounts', compact('accounts'));
    }

    public function addBank(Request $request)
    {
        $input = [
            'name' => $request->get('name'),            
        ];  			
        $rules = [
                'name'    => 'required|unique:bank_accounts'                              
        ];
        
        $formData= $request->all();

        $validator = Validator::make($input,$rules);
        
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['name' =>  $formData['name']];
		$created = bank_accounts::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Bank added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }

    public function delBank($bank_id)
    {
        $usage = transactions::where('bank_id',$bank_id)->get(); 

        if(count($usage)>0)
        {
            Session::flash('error_msg','This Bank Account cannot be deleted because it is already used.');
            return redirect()->route('bankaccounts');
        }

        bank_accounts::where('id','=',$bank_id)->delete();        
        return redirect()->route('bankaccounts')->withStatus(__('Bank successfully deleted.'));
    }

    public function editBank(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
        ];  			
        $rules = [
                'id'    => 'required',
                'name'    => 'required|unique:bank_accounts,name,' . $request->get('id'),                 
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $name = $formData['name'];        
        
        try{
        $obj = bank_accounts::find($id);

        $obj->name = $name;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Bank account updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
