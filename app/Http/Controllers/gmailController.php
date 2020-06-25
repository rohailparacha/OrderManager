<?php

namespace App\Http\Controllers;
use App\gmail_accounts;
use App\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;

class gmailController extends Controller
{
    // //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function accounts()
    {
        $accounts = gmail_accounts::select()->paginate(100);
        return view('gmailAccounts', compact('accounts'));
    }

    public function addAccount(Request $request)
    {
        $input = [
            'url' => $request->get('url'),
            'bceurl' => $request->get('bceurl'),
            'email' => $request->get('email'),
            'type' => $request->get('type')
        ];  			
        $rules = [
                'url'    => 'required',
                'bceurl'    => 'required',                
                'email' => 'required',
                'type' => 'required|not_in:0'                      
        ];
        

       

        $formData= $request->all();

        $validator = Validator::make($input,$rules);
        
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_category');
        }     
        if($formData['type']==1)
        $acc = 'Regular';
    else
        $acc = 'Business';
		$data = ['url' =>  $formData['url'], 'bceurl'=> $formData['bceurl'], 'email' =>  $formData['email'], 'accountType'=>$acc ];
		$created = gmail_accounts::insert($data);		 
        
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
    public function delAccount($account_id)
    {
        gmail_accounts::where('id','=',$account_id)->delete();        
        return redirect()->route('gmailAccounts')->withStatus(__('Account successfully deleted.'));
    }

   
    public function editAccount(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'url' => $request->get('url'),
            'bceurl' => $request->get('bceurl'),
            'email' => $request->get('email'),
            'type' => $request->get('type')
        ];  			
        $rules = [
                'id'    => 'required',
                'url' => 'required',                             
                'bceurl' => 'required',                             
                'email' => 'required',
                'type' => 'required|not_in:0'                           
        ];
        

        $formData= $request->all();

        
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_account');
        }     
        	
        $id = $formData['id'];
        $url = $formData['url'];
        $bceurl = $formData['bceurl'];
        $email = $formData['email'];
        if($formData['type']==1)
            $acc = 'Regular';
        else
            $acc = 'Business';
        try{
        $obj = gmail_accounts::find($id);

        $obj->url = $url;
        $obj->bceurl = $bceurl;
        $obj->email = $email;
        $obj->accountType= $acc;

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
