<?php

namespace App\Http\Controllers;
use App\informed_settings;
use App\informed_accounts;
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
        $settings = informed_settings::select(['informed_settings.*','informed_accounts.name'])
        ->leftJoin('informed_accounts','informed_settings.account_id','informed_accounts.id')   
        ->paginate(100);

        $accounts = informed_accounts::all();
        return view('informed', compact('settings','accounts'));
    }

    public function addSetting(Request $request)
    {
        $input = [
            'strategy' => $request->get('strategy'),            
            'min' => $request->get('min'),            
            'max' => $request->get('max'), 
            'acc' =>$request->get('acc')           
        ];  			
        $rules = [
                'strategy'    => 'required',                
                'min'    => ['required','numeric',new PriceRange($request->get('id'),$request->get('acc'))],
                'max'    => ['required','numeric','min:'.(int)$request->get('min'),new PriceRange($request->get('id'),$request->get('acc'))],                        
                'acc' =>'required'
        ];
        

        $formData= $request->all();
        
        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['account_id' =>  $formData['acc'],'minAmount' =>  $formData['min'], 'maxAmount' =>  $formData['max'], 'strategy_id' =>  $formData['strategy']];
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
            'max' => $request->get('max'),
            'acc' =>$request->get('acc') 
        ];  			
        $rules = [
                'id'    => 'required',
                'strategy' => 'required',
                
                'min'    => ['required','numeric',new PriceRange($request->get('id'),$request->get('acc'))],
                'max'    => ['required','numeric','min:'.(int)$request->get('min'),new PriceRange($request->get('id'),$request->get('acc'))],   
                'acc' =>'required'                   
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
        $account_id =  $formData['acc'];
        
        try{
        $obj = informed_settings::find($id);

        $obj->strategy_id = $strategy;
        $obj->minAmount = $min;
        $obj->maxAmount = $max;
        $obj->account_id = $account_id;

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
