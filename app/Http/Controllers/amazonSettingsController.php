<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\amazon_settings;
use Validator; 
use Redirect;
use Session; 

class amazonSettingsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function amazonsettings()
    {
        
        $settings = amazon_settings::get()->first(); 
        return view('amazonSettings',compact('settings'));
    }

    public function storeSettings(Request $request)
    {

        $soldDays = 0;
        $soldQty = 0; 
        $createdBefore =0;
        
        $stores=array();
        
            $input = [
                'soldDays' => $request->soldDays,
                'soldQty' => $request->soldQty,
                'createdBefore' => $request->createdBefore,         
            ];
    
            $rules = [
                'soldDays'    => 'required|numeric',
                'soldQty' => 'required|numeric',
                'createdBefore' => 'required|numeric',
            ];
        

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator);
        }
        
        $soldDays = $request->soldDays;
        $soldQty = $request->soldQty;
        $createdBefore = $request->createdBefore;
        $amazon_settings = amazon_settings::get()->first();



        if(empty($amazon_settings))
             amazon_settings::insert(['soldDays'=>$soldDays,'soldQty'=>$soldQty,'createdBefore'=>$createdBefore]);
        else        
             amazon_settings::where('id',$amazon_settings->id)->update(['soldDays'=>$soldDays,'soldQty'=>$soldQty,'createdBefore'=>$createdBefore]);
        
        Session::flash('success_msg', __('Amazon Settings Successfully Updated'));
        return redirect()->route('amazonsettings');

    }

}
