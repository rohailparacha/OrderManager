<?php

namespace App\Http\Controllers;
use App\reasons;
use Illuminate\Http\Request;
use Validator; 
use Response;

class reasonsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function reasons()
    {
        $reasons = reasons::select()->paginate(100);
        return view('reasons', compact('reasons'));
    }

    public function addReason(Request $request)
    {
        $input = [
            'name' => $request->get('name'),            
        ];  			
        $rules = [
                'name'    => 'required'                              
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_category');
        }     
        	
		$data = ['name' =>  $formData['name']];
		$created = reasons::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Reason added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delReason($reason_id)
    {
        reasons::where('id','=',$reason_id)->delete();        
        return redirect()->route('reasons')->withStatus(__('Reason successfully deleted.'));
    }

   
    public function editReason(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
        ];  			
        $rules = [
                'id'    => 'required',
                'name' => 'required'                             
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_carrier');
        }     
        	
        $id = $formData['id'];
        $name = $formData['name'];
        
        try{
        $obj = reasons::find($id);

        $obj->name = $name;        

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Reason updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
