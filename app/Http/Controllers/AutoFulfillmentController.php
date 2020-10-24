<?php

namespace App\Http\Controllers;
use DB;
use App\accounts;
use App\settings;
use App\carriers; 
use App\orders;
use App\cancelled_orders;
use App\states;
use App\flags;
use App\products; 
use App\returns;
use App\gmail_accounts;
use App\ebay_products; 
use App\order_details;
use App\Exports\VaughnBceExport;
use App\Exports\VaughnExport;
use App\Exports\VaughnCancelExport;
use Session;
use Redirect;
use Validator;
use GuzzleHttp\Client;
use Excel;
use File;
use Response;

use Illuminate\Http\Request;

class AutoFulfillmentController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {        
        $settings = settings::all();
        return view('afManager',compact('settings'));
    }
    public function storeSettings(Request $request)
    {

        $switches = $request->all();
        $check = false;

        foreach($switches as $key=>$val)
        {
            if($val == 'Enable')
                $check = false;
            else
                $check = true;
            settings::where('name',$key)->update(['sidebarCheck'=>$check]);
        }
    
        Session::flash('success_msg', __('Settings successfully updated'));
        return redirect()->route('afManager');

    }

}
