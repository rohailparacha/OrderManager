<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\blacklist;
use App\reasons;

use Excel;

use App\Exports\BlacklistExport;
use App\Imports\BlacklistImport;

use Response; 

use Validator;
use Session;


class blacklistController extends Controller
{
    //
    public function index()
    {
        $blacklist = blacklist::select()->orderBy('date','desc')->paginate(100);
        $reasons = reasons::all(); 
        return view('blacklist', compact('blacklist','reasons'));
    }

    public function addBlacklist(Request $request)
    {
        $input = [
            'sku' => $request->get('sku'), 
            'reason' => $request->get('reason'),
            'allowance' => $request->get('allowance'),            
        ];  			

        $rules = [
                'sku'    => 'required|unique:blacklist',
                'reason'    => 'required|not_in:0', 
                'allowance' =>'numeric'                             
        ];
        
        $formData= $request->all();

        $validator = Validator::make($input,$rules);
        
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
		$data = ['sku' =>  $formData['sku'], 'reason' =>  $formData['reason'], 'allowance' => $formData['allowance']];
		$created = blacklist::insert($data);		 
        
        if($created)
        {
            return "success";
            Session::flash('success_msg', __("Success. Blacklist added successfully."));
            return Redirect()->back();
        }
        else
        {
            return "error";
        }
    }
    public function delBlacklist($cat_id)
    {
        blacklist::where('id','=',$cat_id)->delete();        
        return redirect()->route('blacklist')->withStatus(__('Item successfully deleted from blacklist.'));
    }

   
    public function editBlacklist(Request $request)
    {

        $input = [
            'id' => $request->get('id'),
            'sku' => $request->get('sku'), 
            'reason' => $request->get('reason'),   
            'allowance' => $request->get('allowance'),        
        ];  			

        $rules = [
                'id'    => 'required',
                'sku'    => 'required|unique:blacklist,sku,' . $request->get('id'), 
                'reason'    => 'required|not_in:0',             
                'allowance' =>'numeric'          
        ];
        

        $formData= $request->all();
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {        
            return response()->json(['error'=>$validator->errors()->all()]);
        }     
        	
        $id = $formData['id'];
        $sku = $formData['sku'];        
        $reason = $formData['reason'];  
        $allowance = $formData['allowance'];        
        
        try{
        $obj = blacklist::find($id);

        $obj->sku = $sku;
        $obj->reason = $reason;
        $obj->allowance = $allowance;
        
        $obj->save();
            return "success";
            Session::flash('success_msg', __("Success. Product updated successfully."));
            return Redirect()->back();
        }
        catch(Exception $ex)
        {
            return "error";
        }

    }

    public function import(Request $request)
    {
        $input = [
            'file' => $request->file           
        ];

        $rules = [
            'file'    => 'required'  
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            return redirect()->route('products');
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['csv','xls','xlsx'];
        
            $file = $request->file('file');
          
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('imports');   
                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                return redirect()->route('products');
             }
            

        }
        else
        {
            
        }
        $import = new BlacklistImport;
        Excel::import($import, $filename);
        $collection = $import->data;
                
        $cnt= $this->processRecords($collection);
        
        Session::flash('success_msg', $cnt. ' Records added/updated/deleted successfully');
        return redirect()->route('blacklist');

    }

    public function export(Request $request)
    {        
        $filename = date("d-m-Y")."-".time()."-blacklist.xlsx";
        return Excel::download(new BlacklistExport(), $filename);
    }

    public function processRecords($collection)
    {
        $cnt=0;
        foreach($collection as $col)
        {
            try{
                if(strtolower(trim($col['action']))=='add')
                {                    
                    
                    $insert = blacklist::updateOrCreate(
                        ['sku'=>$col['asin']],    
                        ['reason'=>$col['reason'],'allowance'=>$col['allowance']]
                    );

                    if($insert)
                        $cnt++;
                }

                elseif(strtolower(trim($col['action']))=='modify')
                {
                    $update = blacklist::where('sku',$col['asin'])->update(                            
                        ['allowance'=>$col['allowance']]
                    );

                    if($update)
                        $cnt++;
                }
                elseif(strtolower(trim($col['action']))=='delete')
                {
                    $del = blacklist::where('sku','=',trim($col['asin']))->delete();       
                    if($del)
                        $cnt++;
                }


            }
            catch(\Exception $ex)
            {

            }
        }

        return $cnt;
    }

    public function getTemplate()
    {
        //PDF file is stored under project/public/download/info.pdf
        $file="./templates/blacklist.csv";
        return Response::download($file);
    }
    
}
