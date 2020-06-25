<?php

namespace App\Http\Controllers;
use App\carriers;
use App\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;

class carriersController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function carriers()
    {
        $carriers = carriers::select()->paginate(100);
        return view('carriers', compact('carriers'));
    }

    public function addCarrier(Request $request)
    {
        $input = [
            'carrier' => $request->get('carrier'),            
        ];  			
        $rules = [
                'carrier'    => 'required'                              
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_category');
        }     
        	
		$data = ['name' =>  $formData['carrier'], 'alias' =>  $formData['alias']];
		$created = carriers::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Carrier added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delCarrier($carrier_id)
    {
        carriers::where('id','=',$carrier_id)->delete();        
        return redirect()->route('carriers')->withStatus(__('Carrier successfully deleted.'));
    }

   
    public function editCarrier(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'carrier' => $request->get('carrier'),
        ];  			
        $rules = [
                'id'    => 'required',
                'carrier' => 'required'                             
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_carrier');
        }     
        	
        $id = $formData['id'];
        $carrier = $formData['carrier'];
        $alias =  $formData['alias'];
        
        try{
        $obj = carriers::find($id);

        $obj->name = $carrier;
        $obj->alias = $alias;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Carrier updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
