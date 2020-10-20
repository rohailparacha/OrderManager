<?php

namespace App\Http\Controllers;
use App\informed_accounts;
use Illuminate\Http\Request;
use Validator;
use Response; 
use Session;
use App\Rules\PriceRange;

class informedAccountsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function accounts()
    {
        $accounts = informed_accounts::select()->paginate(100);
        return view('informedaccounts', compact('accounts'));
    }

    public function addAccount(Request $request)
    {
        $input = [
            'name' => $request->get('name'),            
            'token' => $request->get('token')         
        ];  			
        $rules = [
                'name'    => 'required|unique:informed_accounts',
                'token'    => 'required|unique:informed_accounts'                                                   
        ];
        

        $formData= $request->all();
        
        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['name' =>  $formData['name'], 'token' =>  $formData['token']];
		$created = informed_accounts::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Account added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delAccount($acc_id)
    {
        informed_accounts::where('id','=',$acc_id)->delete();        
        return redirect()->route('informedaccounts')->withStatus(__('Account successfully deleted.'));
    }

   
    public function editAccount(Request $request)
    {
     

        $input = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),            
            'token' => $request->get('token')         
        ];  			
        $rules = [
                'id'    => 'required',
                'name'    => 'required|unique:informed_accounts,name,' . $request->get('id'),
                'token'    => 'required|unique:informed_accounts,token,' . $request->get('id')                                                   
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $name = $formData['name'];
        $token =  $formData['token'];        
        
        try{
        $obj = informed_accounts::find($id);

        $obj->name = $name;
        $obj->token = $token;        

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Account updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
