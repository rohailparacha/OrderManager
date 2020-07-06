<?php

namespace App\Http\Controllers;
use App\informed_settings;
use Illuminate\Http\Request;
use Validator;
use Response; 
use Session;
use App\Rules\PriceRange;

class informedSettingsController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function settings()
    {
        $settings = informed_settings::select()->paginate(100);
        return view('informed', compact('settings'));
    }

    public function addSetting(Request $request)
    {
        $input = [
            'strategy' => $request->get('strategy'),            
            'min' => $request->get('min'),            
            'max' => $request->get('max'),            
        ];  			
        $rules = [
                'strategy'    => 'required',                
                'min'    => ['required','numeric',new PriceRange($request->get('id'))],
                'max'    => ['required','numeric','min:'.(int)$request->get('min'),new PriceRange($request->get('id'))],                        
        ];
        

        $formData= $request->all();
        
        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['minAmount' =>  $formData['min'], 'maxAmount' =>  $formData['max'], 'strategy_id' =>  $formData['strategy']];
		$created = informed_settings::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Setting added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delSetting($acc_id)
    {
        informed_settings::where('id','=',$acc_id)->delete();        
        return redirect()->route('informed')->withStatus(__('Setting successfully deleted.'));
    }

   
    public function editSetting(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'strategy' => $request->get('strategy'),            
            'min' => $request->get('min'),
            'max' => $request->get('max')
        ];  			
        $rules = [
                'id'    => 'required',
                'strategy' => 'required',
                
                'min'    => ['required','numeric',new PriceRange($request->get('id'))],
                'max'    => ['required','numeric','min:'.(int)$request->get('min'),new PriceRange($request->get('id'))],                      
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $strategy = $formData['strategy'];
        $min =  $formData['min'];
        $max =  $formData['max'];
        
        try{
        $obj = informed_settings::find($id);

        $obj->strategy_id = $strategy;
        $obj->minAmount = $min;
        $obj->maxAmount = $max;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Setting updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
