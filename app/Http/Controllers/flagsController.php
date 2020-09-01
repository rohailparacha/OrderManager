<?php

namespace App\Http\Controllers;
use App\flags;
use Illuminate\Http\Request;
use Validator; 
use Response;
use Session; 

class flagsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function flags()
    {
        $flags = flags::select()->paginate(100);
        return view('flags', compact('flags'));
    }

    public function addFlag(Request $request)
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
        	
		$data = ['name' =>  $formData['name'], 'color' =>  $formData['color']];
		$created = flags::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Flag added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delFlag($flag_id)
    {
        flags::where('id','=',$flag_id)->delete();        
        return redirect()->route('flags')->withStatus(__('Flag successfully deleted.'));
    }

   
    public function editFlag(Request $request)
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
        $color = $formData['color'];
        
        try{
        $obj = flags::find($id);

        $obj->name = $name;  
        $obj->color = $color;        

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Flag updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }

    public function editExpensive(Request $request)
    {
        $input = [
        
            'val' => $request->get('val'),
        ];  			
        $rules = [
            
                'val' => 'required'                             
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
           Session::flash('error_msg', __("Validation Error. Please fix errors and try again."));
           return Redirect::back()->withInput()->withErrors($validator,'add_carrier');
        }     
        	
        $val = $formData['val'];
        
        try{
        
            $return = flags::updateOrCreate(
                ['name'=>'Expensive'],    
                ['color'=>$val]
                );

            return "success";
           
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
