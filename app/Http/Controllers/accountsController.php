<?php

namespace App\Http\Controllers;
use App\User;
use App\orders;
use App\order_details;
use App\accounts;
use App\sc_accounts;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;

class accountsController extends Controller
{
    //

    public function index()
    {
        $accounts = accounts::
        leftJoin('sc_accounts','accounts.scaccount_id','=','sc_accounts.id')
        ->leftJoin('users','accounts.manager_id','=','users.id')
        ->select(['accounts.*','sc_accounts.name','users.name As manager'])->paginate(50);   

        $managers = User::where('role',2)->get();    
        return view('accounts.index',compact('accounts','managers'));
    }

    public function create()
    {
        $managers = User::where('role',2)->get(); 
        $scaccounts = sc_accounts::all();
        return view ('accounts.create',compact('managers','scaccounts'));
    }

    public function edit($id)
    {        

        $account = accounts::select()->where('id',$id)->get()->first();
        $managers = User::where('role',2)->get(); 
        $scaccounts = sc_accounts::all();
        return view ('accounts.edit',compact('account','managers','scaccounts'));
    }

    public function update(Request $request)
    {
        $input = [
            'store' => $request->store,
            'username' => $request->username,
            'password'    => $request->password,
            'scaccount'    => $request->scaccount,
            'informed' => $request->informed
        ];

        $rules = [
            'store'    => 'required',
            'username' => 'required',
            'password' => 'required',
            'scaccount' => 'required',
            'informed' => 'required'        
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator,'account_add');
        }
        $store  =$request->store;
        $username  =$request->username;
        $password  =$request->password;
        $id  =$request->id;
        $manager  =$request->manager;
        $lag = $request->lag;
        $informed = $request->informed;
        $scaccount = $request->scaccount;
      
        $account = accounts::where('id', $id)->update(['store'=>$store, 'username'=>$username, 'password'=>$password, 'manager_id'=>$manager, 'lagTime'=>$lag ,'scaccount_id'=>$scaccount, 'informed_id'=>$informed]);

        if($account)
        {
            Session::flash('success_msg', __('Account updated successfully'));
            return Redirect::to('accounts');
        }
        else
        {
            Session::flash('error_msg', __('Account could not be updated'));
            return Redirect::to('accounts');
        }

    }

    public function destroy(Request $account)
    {        
        $id= $_SERVER['QUERY_STRING'];
        
        $deletedRows = accounts::where('id', $id)->delete();
        
        if($deletedRows)
            return redirect()->route('accounts')->withStatus(__('Account successfully deleted.'));
        else
            return redirect()->route('accounts')->withStatus(__('Account could not be deleted.'));
    }

    public function store(Request $request)
    {
		$input = [
            'store' => $request->store,
            'username' => $request->username,
            'password'    => $request->password,
            'scaccount'    => $request->scaccount,
            'informed' => $request->informed
        ];

        $rules = [
            'store'    => 'required',
            'username' => 'required|unique:accounts',
            'password' => 'required',
            'scaccount' => 'required',
            'informed' => 'required'       
    ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator,'account_add');
        }
        $store  =$request->store;
        $username  =$request->username;
        $password  =$request->password;
        $manager  =$request->manager;
        $lag = $request->lag;
        $informed = $request->informed;
        $scaccount = $request->scaccount;

        $account = accounts::insert(['store'=>$store, 'username'=>$username, 'password'=>$password, 'manager_id'=>$manager, 'lagTime'=>$lag,'scaccount_id'=>$scaccount, 'informed_id'=>$informed]);

        if($account)
        {
            Session::flash('success_msg', __('Account added successfully'));
            return Redirect::to('accounts');
        }
        else
        {
            Session::flash('error_msg', __('Account could not be added'));
            return Redirect::to('accounts');
        }

	}
}
