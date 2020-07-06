<?php

namespace App\Http\Controllers;
use App\sc_accounts;
use Illuminate\Http\Request;
use Validator; 
use Session;
use Response; 

class scAccountsController extends Controller
{
    //
    
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function accounts()
    {
        $accounts = sc_accounts::select()->paginate(100);
        return view('scaccounts', compact('accounts'));
    }

    public function addAccount(Request $request)
    {
        $input = [
            'token' => $request->get('token'),            
            'campaign' => $request->get('campaign'),            
            'name' => $request->get('name'),            
        ];  			
        $rules = [
                'token'    => 'required|unique:sc_accounts',
                'campaign'    => 'required',
                'name'    => 'required|unique:sc_accounts',                       
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['campaign' =>  $formData['campaign'], 'token' =>  $formData['token'], 'name' =>  $formData['name']];
		$created = sc_accounts::insert($data);		 
        
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
        sc_accounts::where('id','=',$acc_id)->delete();        
        return redirect()->route('scaccounts')->withStatus(__('Account successfully deleted.'));
    }

   
    public function editAccount(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'token' => $request->get('token'),            
            'campaign' => $request->get('campaign'),
            'name' => $request->get('name')
        ];  			
        $rules = [
                'id'    => 'required',
                'token' => 'required|unique:sc_accounts,token,' . $request->get('id'),
                'campaign' => 'required',
                'name' => 'required|unique:sc_accounts,name,' . $request->get('id'),                       
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $campaign = $formData['campaign'];
        $token =  $formData['token'];
        $name =  $formData['name'];
        
        try{
        $obj = sc_accounts::find($id);

        $obj->campaign = $campaign;
        $obj->token = $token;
        $obj->name = $name;

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
