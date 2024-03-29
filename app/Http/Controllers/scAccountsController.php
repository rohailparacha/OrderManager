<?php

namespace App\Http\Controllers;
use App\sc_accounts;
use App\accounts;
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
            'products'=>$request->get('products')       
        ];  			
        $rules = [
                'token'    => 'required|unique:sc_accounts',
                'campaign'    => 'required',
                'name'    => 'required|unique:sc_accounts',                       
                'products'=>'required'
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
            
        
		$data = ['campaign' =>  $formData['campaign'], 'token' =>  $formData['token'], 'name' =>  $formData['name'], 'products' =>  $formData['products']];
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
        $accounts = accounts::where('scaccount_id',$acc_id)->get()->count(); 

        if($accounts>0)
        {
            Session::flash('error_msg', __("Account cannot be deleted as it is assigned to any store. First detach it from store in marketplaces."));
            return redirect()->route('scaccounts');
        }
        
        sc_accounts::where('id','=',$acc_id)->delete();        
        Session::flash('success_msg', __("Account successfully deleted."));
        return redirect()->route('scaccounts');
    }

   
    public function editAccount(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'token' => $request->get('token'),            
            'campaign' => $request->get('campaign'),
            'name' => $request->get('name'),
            'products'=>$request->get('products')
        ];  			
        $rules = [
                'id'    => 'required',
                'token' => 'required|unique:sc_accounts,token,' . $request->get('id'),
                'campaign' => 'required',
                'name' => 'required|unique:sc_accounts,name,' . $request->get('id'),     
                'products'=>'required'                 
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
        $products = $formData['products'];
        
        try{
        $obj = sc_accounts::find($id);

        $obj->campaign = $campaign;
        $obj->token = $token;
        $obj->name = $name;
        $obj->products = json_encode($products);
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
