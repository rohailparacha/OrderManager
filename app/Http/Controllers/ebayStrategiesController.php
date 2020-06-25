<?php

namespace App\Http\Controllers;
use App\User; 
use App\ebay_products;
use App\ebay_strategies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Validator; 
use Session;
use Redirect;

class ebayStrategiesController extends Controller
{
    //
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function strategies()
    {
        $strategies = ebay_strategies::select()->paginate(100);
        
        foreach($strategies as $strategy)
        {
            $product = ebay_products::where('strategy_id',$strategy->id)->get()->count();            
            $strategy->count = $product; 
        }

        return view('ebayStrategies', compact('strategies'));
    }

    public function addStrategy(Request $request)
    {
        $input = [
            'code' => $request->get('code'),
            'breakeven' => $request->get('breakeven'),
            'value' => $request->get('value'),
            'type' => $request->get('type')
        ];  			
        $rules = [
                'code'    => 'required|unique:ebay_strategies',
                'breakeven'    => 'required|numeric',
                'value'    => 'required|numeric',
                'type'    => 'required|not_in:0'                              
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
            
        if($formData['default']=='true')
            $check =1;
        else
            $check=0;

        $data = ['code' =>  $formData['code'],'breakeven' =>  $formData['breakeven'],'type' =>  $formData['type'],'value' =>  $formData['value'],'isDefault'=>$check];
        
        if($formData['default']=='true')
        {
            ebay_strategies::query()->update(["isDefault"=>0]);
        }

		$created = ebay_strategies::insert($data);		 
        

        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Strategy added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delStrategy($strategy_id)
    {
        $count = ebay_products::where('strategy_id',$strategy_id)->count(); 
        if($count>0)
        {
            Session::flash('error_msg', __("Cannot Delete Strategy."));
            return Redirect()->back();
        }
        
        ebay_strategies::where('id','=',$strategy_id)->delete();        
        
        

        return redirect()->route('ebaystrategies')->withStatus(__('Strategy successfully deleted.'));


    }

   
    public function editStrategy(Request $request)
    {
        $input = [
            'id' => $request->get('id'),
            'code' => $request->get('code'),
            'breakeven' => $request->get('breakeven'),
            'value' => $request->get('value'),
            'type' => $request->get('type')
        ];  			
        $rules = [
                'id'    => 'required',
                'code'    => 'required',
                'breakeven'    => 'required|numeric',
                'value'    => 'required|numeric',
                'type'    => 'required|not_in:0'                              
        ];

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $code = $formData['code'];
        $breakeven = $formData['breakeven'];
        $type = $formData['type'];
        $value = $formData['value'];
        $default = $formData['default'];
        if($formData['default']=='true')
            $check =1;
        else
            $check=0;
        
        if($formData['default']=='true')
            {
                ebay_strategies::query()->update(["isDefault"=>0]);
            }
        
        try{
        $obj = ebay_strategies::find($id);

        $obj->value = $value;
        $obj->code = $code;
        $obj->breakeven = $breakeven;
        $obj->type = $type;
        $obj->isDefault = $check;

        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Strategy updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }
}
