<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\accounting_categories;
use Response; 
use Validator;

class categoriesController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $categories = accounting_categories::select()->paginate(100);
        return view('accounting.categories', compact('categories'));
    }

    public function addCategory(Request $request)
    {
        $input = [
            'category' => $request->get('name'), 
            'type' => $request->get('type'),            
        ];  			
        $rules = [
                'category'    => 'required|unique:accounting_categories',
                'type'    => 'required|not_in:0',                              
        ];
        
        $formData= $request->all();

        $validator = Validator::make($input,$rules);
        
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['category' =>  $formData['name'], 'type' =>  $formData['type']];
		$created = accounting_categories::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Category added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delCategory($cat_id)
    {
        accounting_categories::where('id','=',$cat_id)->delete();        
        return redirect()->route('categories')->withStatus(__('Category successfully deleted.'));
    }

   
    public function editCategory(Request $request)
    {

        $input = [
            'id' => $request->get('id'),
            'category' => $request->get('name'), 
            'type' => $request->get('type'),            
        ];  			
        $rules = [
                'id'    => 'required',
                'category'    => 'required|unique:accounting_categories,category,' . $request->get('id'),  
                'type'    => 'required|not_in:0',                              
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $name = $formData['name'];        
        $type = $formData['type'];        
        
        try{
        $obj = accounting_categories::find($id);

        $obj->category = $name;
        $obj->type = $type;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Category updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
