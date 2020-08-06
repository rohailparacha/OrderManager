<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator; 
use Session;
use Redirect;
use App\Imports\ReturnsImport;
use App\orders;
use Carbon\Carbon;
use App\returns;
use App\order_details;
use App\accounts;
use App\gmail_accounts;
use App\products; 
use App\ebay_products;
use Excel;
use Response;

class cindyReturnsController extends Controller
{
    //
    public function index()
    {
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->where('orders.account_id','Cindy')
                ->select(['orders.*','returns.*'])
                ->orderBy('created_at','desc')
                ->whereNull('returns.status')                
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])                
                ->whereIn('orders.storeName',$strArray)      
                ->whereNull('returns.status')     
                ->where('orders.account_id','Cindy')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('orders.uid',auth()->user()->id)  
                ->where('orders.account_id','Cindy')              
                ->whereNull('returns.status')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            return view('cindy.return',compact('returns','accounts','stores','dateRange'));
    }
    public function refunds()
    {
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','returned')    
                ->where('orders.account_id','Cindy')     
                ->orderBy('returnDate','desc')
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])                
                ->whereIn('orders.storeName',$strArray)
                ->where('orders.account_id','Cindy')    
                ->where('returns.status','returned')  
                ->orderBy('returnDate','desc')       
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('orders.uid',auth()->user()->id)  
                ->where('returns.status','returned')  
                ->where('orders.account_id','Cindy')
                ->orderBy('returnDate','desc')                    
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            return view('cindy.refund',compact('returns','accounts','stores','dateRange'));
    }


    public function completed()
    {
            if(auth()->user()->role==1)
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','refunded') 
                ->where('orders.account_id','Cindy')        
                ->orderBy('refundDate','desc')
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])         
                ->where('returns.status','refunded')  
                ->where('orders.account_id','Cindy')              
                ->whereIn('orders.storeName',$strArray)  
                ->orderBy('refundDate','desc')           
                ->paginate(100);
            }
        
            else
            {
                $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*'])
                ->where('returns.status','refunded')    
                ->where('orders.account_id','Cindy')     
                ->where('orders.uid',auth()->user()->id)  
                ->orderBy('refundDate','desc')              
                ->paginate(100);
            }
            
            
            foreach($returns as $return)
            {
                $sources = array();
                $order_details = order_details::where('order_id',$return->order_id)->get(); 
                if(empty($order_details))
                    continue;
                
                
                foreach($order_details as $detail)
                {

                    $amz = products::where('asin',$detail->SKU)->get()->first(); 
                    if(empty($amz))
                        {
                            $ebay = ebay_products::where('sku',$detail->SKU)->get()->first(); 
                            if(empty($ebay))
                                $sources[]= 'NA'; 
                            else
                                $sources[]= 'Ebay'; 

                        }
                    else
                                $sources[]= 'Amazon'; 

                    $b = array_unique($sources); 

                    if(count($b)==1)
                        $return->source = $b[0];
                    else
                        $return->source = 'Mix';
                }
            }

            $accounts = gmail_accounts::all();      
            $stores = accounts::all();
            $startDate = returns::min('returnDate');
            $endDate = returns::max('returnDate');

            $from = date("m/d/Y", strtotime($startDate));  
            $to = date("m/d/Y", strtotime($endDate));  
            $dateRange = $from .' - ' .$to;
            return view('cindy.complete',compact('returns','accounts','stores','dateRange'));
    }


    public function updateStatus(Request $request)
    {
        $status = $request->status; 
        $id = $request->id; 

        if($status==1)
            $status='returned';
        elseif($status==2)
            $status='refunded';

        if($status=='returned')
            $test = returns::where('id',$id)->update(['status'=>$status,'returnDate'=>Carbon::now()]);
        else
            $test = returns::where('id',$id)->update(['status'=>$status,'refundDate'=>Carbon::now()]);

        if($test && $status=='returned')
        {
            $return  = returns::where('id',$id)->get()->first(); 
        
            $order = orders::where('id',$return->order_id)->get()->first();

            $orderDetails = order_details::where('order_id',$order->id)->get(); 

            foreach($orderDetails as $orderDetail)
            {
                products::where('asin',$orderDetail->SKU)->increment('returned',$orderDetail->quantity);
            }
        }

        
        if($test)
            {
                $return  = returns::where('id',$id)->get()->first(); 
        
                $order = orders::where('id',$return->order_id)->get()->first();
                                
                if($status=='returned')
                    return redirect()->back()->withStatus('Order '.$order->poNumber.' is returned successfully.');
                else
                    return redirect()->back()->withStatus('Order '.$order->poNumber.' is refunded successfully.');
            }
        
    }

    public function returnFilter(Request $request)
    {
        
        if($request->has('statusFilter'))
            $statusFilter = $request->get('statusFilter');
        if($request->has('labelFilter'))
            $labelFilter = $request->get('labelFilter');  

        if($request->has('accountFilter'))
            $accountFilter = $request->get('accountFilter');  

        if($request->has('daterange'))
            $dateRange = $request->get('daterange');  

         $startDate = explode('-',$dateRange)[0];
            $from = date("Y-m-d", strtotime($startDate));  
         $endDate = explode('-',$dateRange)[1];
            $to = date("Y-m-d", strtotime($endDate)); 

        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');  
        
            $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*']);
            
            if(!empty($statusFilter)&& $statusFilter !=0)
                {                            
                    if($statusFilter==1)
                        $returns = $returns->where('returns.status',null);
                    elseif($statusFilter==2)
                        $returns = $returns->where('returns.status','returned');
                    elseif($statusFilter==3)
                        $returns = $returns->where('returns.status','refunded');
                              
                }

                if(!empty($labelFilter)&& $labelFilter !=0)
                {                            
                    if($labelFilter==1)
                        $returns = $returns->where('label','!=',null);
                    elseif($labelFilter==2)
                        $returns = $returns->where('label','=',null);                            
                }

                if(!empty($storeFilter)&& $storeFilter !=0)
                {
                    $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
                    $returns = $returns->where('orders.storeName',$storeName->store);
                }
                if(!empty($accountFilter)&& $accountFilter !=0)
                {                    
                    $returns = $returns->where('account_id',$accountFilter);
                }

                if(!empty($startDate)&& !empty($endDate))
                {
                    $returns = $returns->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);
                }
               

            if(auth()->user()->role==1)
            {
                
                $returns = $returns->orderBy('created_at','desc')
                ->where('orders.account_id','Cindy')
                ->whereNull('returns.status')
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = $returns
                ->whereIn('orders.storeName',$strArray)    
                ->whereNull('returns.status')
                ->where('orders.account_id','Cindy')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }
        
            else
            {

                $returns = $returns
                ->where('orders.uid',auth()->user()->id)                
                ->whereNull('returns.status')
                ->where('orders.account_id','Cindy')
                ->orderBy('created_at','desc')
                ->paginate(100);
            }

            
            $accounts = gmail_accounts::all();  
            $stores = accounts::all();

            $returns  = $returns->appends('statusFilter',$statusFilter)->appends('labelFilter',$labelFilter)->appends('storeFilter',$storeFilter)->appends('accountFilter',$accountFilter)->appends('daterange',$dateRange);

            return view('cindy.return',compact('returns','accounts','stores','statusFilter','labelFilter','storeFilter','accountFilter','dateRange'));
                 
    }
    public function refundFilter(Request $request)
    {
        
        if($request->has('statusFilter'))
            $statusFilter = $request->get('statusFilter');
        if($request->has('labelFilter'))
            $labelFilter = $request->get('labelFilter');  

        if($request->has('accountFilter'))
            $accountFilter = $request->get('accountFilter');  

        if($request->has('daterange'))
            $dateRange = $request->get('daterange');  

         $startDate = explode('-',$dateRange)[0];
            $from = date("Y-m-d", strtotime($startDate));  
         $endDate = explode('-',$dateRange)[1];
            $to = date("Y-m-d", strtotime($endDate)); 

        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');  
        
            $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*']);
            
            if(!empty($statusFilter)&& $statusFilter !=0)
                {                            
                    if($statusFilter==1)
                        $returns = $returns->where('returns.status',null);
                    elseif($statusFilter==2)
                        $returns = $returns->where('returns.status','returned');
                    elseif($statusFilter==3)
                        $returns = $returns->where('returns.status','refunded');
                              
                }

                if(!empty($labelFilter)&& $labelFilter !=0)
                {                            
                    if($labelFilter==1)
                        $returns = $returns->where('label','!=',null);
                    elseif($labelFilter==2)
                        $returns = $returns->where('label','=',null);                            
                }

                if(!empty($storeFilter)&& $storeFilter !=0)
                {
                    $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
                    $returns = $returns->where('orders.storeName',$storeName->store);
                }
                if(!empty($accountFilter)&& $accountFilter !=0)
                {                    
                    $returns = $returns->where('account_id',$accountFilter);
                }

                if(!empty($startDate)&& !empty($endDate))
                {
                    $returns = $returns->whereBetween('returnDate', [$from.' 00:00:00', $to.' 23:59:59']);
                }
               

            if(auth()->user()->role==1)
            {
                
                $returns = $returns->orderBy('returnDate','desc')
                ->where('returns.status','returned')
                ->where('orders.account_id','Cindy')
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = $returns
                ->whereIn('orders.storeName',$strArray)    
                ->where('returns.status','returned')
                ->where('orders.account_id','Cindy')
                ->orderBy('returnDate','desc')
                ->paginate(100);
            }
        
            else
            {

                $returns = $returns
                ->where('orders.uid',auth()->user()->id)  
                ->where('returns.status','returned') 
                ->where('orders.account_id','Cindy')             
                ->orderBy('returnDate','desc')
                ->paginate(100);
            }

            
            $accounts = gmail_accounts::all();  
            $stores = accounts::all();

            $returns  = $returns->appends('statusFilter',$statusFilter)->appends('labelFilter',$labelFilter)->appends('storeFilter',$storeFilter)->appends('accountFilter',$accountFilter)->appends('daterange',$dateRange);

            return view('cindy.return',compact('returns','accounts','stores','statusFilter','labelFilter','storeFilter','accountFilter','dateRange'));
                 
    }

    public function completedFilter(Request $request)
    {
        
        if($request->has('statusFilter'))
            $statusFilter = $request->get('statusFilter');
        if($request->has('labelFilter'))
            $labelFilter = $request->get('labelFilter');  

        if($request->has('accountFilter'))
            $accountFilter = $request->get('accountFilter');  

        if($request->has('daterange'))
            $dateRange = $request->get('daterange');  

         $startDate = explode('-',$dateRange)[0];
            $from = date("Y-m-d", strtotime($startDate));  
         $endDate = explode('-',$dateRange)[1];
            $to = date("Y-m-d", strtotime($endDate)); 

        if($request->has('storeFilter'))
            $storeFilter = $request->get('storeFilter');  
        
            $returns = returns::leftJoin('orders','orders.id','=','returns.order_id')
                ->select(['orders.*','returns.*']);
            
            if(!empty($statusFilter)&& $statusFilter !=0)
                {                            
                    if($statusFilter==1)
                        $returns = $returns->where('returns.status',null);
                    elseif($statusFilter==2)
                        $returns = $returns->where('returns.status','returned');
                    elseif($statusFilter==3)
                        $returns = $returns->where('returns.status','refunded');
                              
                }

                if(!empty($labelFilter)&& $labelFilter !=0)
                {                            
                    if($labelFilter==1)
                        $returns = $returns->where('label','!=',null);
                    elseif($labelFilter==2)
                        $returns = $returns->where('label','=',null);                            
                }

                if(!empty($storeFilter)&& $storeFilter !=0)
                {
                    $storeName = accounts::select()->where('id',$storeFilter)->get()->first();
                    $returns = $returns->where('orders.storeName',$storeName->store);
                }
                if(!empty($accountFilter)&& $accountFilter !=0)
                {                    
                    $returns = $returns->where('account_id',$accountFilter);
                }

                if(!empty($startDate)&& !empty($endDate))
                {
                    $returns = $returns->whereBetween('refundDate', [$from.' 00:00:00', $to.' 23:59:59']);
                }
               

            if(auth()->user()->role==1)
            {
                
                $returns = $returns->orderBy('refundDate','desc')
                ->where('returns.status','refunded')
                ->where('orders.account_id','Cindy')
                ->paginate(100);
            }
    
            elseif(auth()->user()->role==2)
            {
                
                $stores = accounts::select()->where('manager_id',auth()->user()->id)->get(); 
                $strArray  = array();
    
                foreach($stores as $str)
                {
                    $strArray[]= $str->store;
                }
                                
                $returns = $returns
                ->whereIn('orders.storeName',$strArray) 
                ->where('returns.status','refunded')   
                ->where('orders.account_id','Cindy')
                ->orderBy('refundDate','desc')
                ->paginate(100);
            }
        
            else
            {

                $returns = $returns
                ->where('orders.uid',auth()->user()->id)    
                ->where('returns.status','refunded') 
                ->where('orders.account_id','Cindy')           
                ->orderBy('refundDate','desc')
                ->paginate(100);
            }

            
            $accounts = gmail_accounts::all();  
            $stores = accounts::all();

            $returns  = $returns->appends('statusFilter',$statusFilter)->appends('labelFilter',$labelFilter)->appends('storeFilter',$storeFilter)->appends('accountFilter',$accountFilter)->appends('daterange',$dateRange);

            return view('cindy.return',compact('returns','accounts','stores','statusFilter','labelFilter','storeFilter','accountFilter','dateRange'));
                 
    }

    public function addReturn(Request $request)
    {
        $input = [
            'sellOrder' => $request->get('sellOrder'),
            'tracking' => $request->get('tracking'),
            'carrier' => $request->get('carrier'),
            'reason' => $request->get('reason')
        ];  			

        $rules = [
            'sellOrder'    => 'required',
            'tracking'    => 'required|string',
            'carrier'    => 'required|not_in:0',
            'reason'    => 'required|not_in:0',
                               
        ];
        

        $formData= $request->all();

        
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {                    
            return response()->json(['error'=>$validator->errors()->all()]);
        }  
        
        $orderId = $request->get('sellOrder');
        $tracking = $request->get('tracking');
        $carrier = $request->get('carrier');
        $reason = $request->get('reason');

        $order = orders::where('sellOrderId',$orderId)->get()->first(); 

        date_default_timezone_set('UTC');      

        if(empty($order))
            return "failure";
        else
           {
               $return = returns::updateOrCreate(
                ['sellOrderId'=>$orderId],    
                ['order_id'=>$order->id,'reason'=>$reason,'carrier'=>$carrier,'trackingNumber'=>$tracking]
                );
               return "success";
           }
        
    }

    public function editReturn(Request $request)
    {
        $input = [
            'tracking' => $request->get('tracking'),
            'carrier' => $request->get('carrier'),
            'reason' => $request->get('reason')
        ];  			

        $rules = [
            
            'tracking'    => 'required|string',
            'carrier'    => 'required|not_in:0',
            'reason'    => 'required|not_in:0',
                               
        ];
        

        $formData= $request->all();

        
        $validator = Validator::make($input,$rules);
        if($validator->fails())
        {                    
            return response()->json(['error'=>$validator->errors()->all()]);
        }  
        
        $orderId = $request->get('sellOrder');
        $tracking = $request->get('tracking');
        $carrier = $request->get('carrier');
        $reason = $request->get('reason');
        $id = $request->get('id');

        $order = orders::where('sellOrderId',$orderId)->get()->first(); 

        if(empty($order))
            return "failure";
        else
           {
               $return = returns::where('id',$id)->update(['order_id'=>$order->id,'sellOrderId'=>$orderId,'reason'=>$reason,'carrier'=>$carrier,'trackingNumber'=>$tracking]);
               return "success";
           }
        
    }

    public function deleteReturn($id)
    {
        returns::where('id',$id)->delete();        
        return redirect()->back()->withStatus(__('Return successfully deleted.'));
    }

    public function deleteReturnRoute($route, $id)
    {
        returns::where('id',$id)->delete();        
        return redirect()->route($route)->withStatus(__('Return successfully deleted.'));
    }

    public function uploadSubmit(Request $request)
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
            return redirect()->back();
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
                return redirect()->back();
             }
            

        }
        else
        {
            
        }

        $import = new ReturnsImport;
        Excel::import($import, $filename);
        $collection = $import->data;        
        
        $oldCount = returns::all()->count(); 
        $this->createReturns($collection);
        $newCount = returns::all()->count(); 
        Session::flash('success_msg',$newCount - $oldCount .' Returns Added Succsesfully');
        return redirect()->back();
    }

    public function labelPrint($id)
    {
        $label = returns::where('id',$id)->get()->first(); 
        $filename = $label->label;
        $path = storage_path('/app/public/'.$filename);

        return response()->file($path);
    }

    public function labelDelete($id)
    {
        $return = returns::where('id',$id)->get()->first();
        try
        {
            unlink(storage_path('/app/public/'.$return->label));
        }
        catch(\Exception $ex)
        {
          
        }

        $returns  = returns::where('id',$id)->update(['label'=>null]);
        
        $return  = returns::where('id',$id)->get()->first(); 
        
        $order = orders::where('id',$return->order_id)->get()->first();
        
        return redirect()->back()->withStatus(__('Label was deleted for order: '). $order->poNumber);
    }

    public function labelDeleteRoute($route, $id)
    {
        $return = returns::where('id',$id)->get()->first();
        try
        {
            unlink(storage_path('/app/public/'.$return->label));
        }
        catch(\Exception $ex)
        {
          
        }

        $returns  = returns::where('id',$id)->update(['label'=>null]);
        
        $return  = returns::where('id',$id)->get()->first(); 
        
        $order = orders::where('id',$return->order_id)->get()->first();
        
        return redirect()->route($route)->withStatus(__('Label was deleted for order: '). $order->poNumber);
    }


    public function uploadLabel(Request $request)
    {
        
        $input = [
            'id' => $request->id,
            'file' => $request->file           
        ];

        $rules = [
            'id' => 'required',
            'file' => 'required'  
        ];

        $validator = Validator::make($input,$rules);
        $route = $request->route;
        if($validator->fails())
        {
            Session::flash('error_msg', __('File is required'));
            if(empty($route))
            return redirect()->back();
        else
            return redirect()->route($route);
        }

        if($request->hasFile('file'))
        {
        
            $allowedfileExtension=['pdf','png','jpg','jpeg'];
        
            $file = $request->file('file');
            $id = $request->id;
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);
            
            if($check)
            {                
                $filename = $request->file->store('labels', ['disk' => 'public']);          
                $label = returns::where('id',$id)->update(['label'=>$filename]);                           
                Session::flash('success_msg', __('File Uploaded Successfully'));
            }

           else
             {
                Session::flash('error_msg', __('Invalid File Extension'));
                if(empty($route))
                    return redirect()->back();
                else
                    return redirect()->route($route);
             }
            

        }
        else
        {
            
        }
        
        $return  = returns::where('id',$id)->get()->first(); 
        $order = orders::where('id',$return->order_id)->get()->first();
        Session::flash('success_msg','Label was uploaded for order: '.$order->poNumber);
        if(empty($route))
        return redirect()->back();
    else
        return redirect()->route($route);
    }

    public function createReturns($collection)
    {
        foreach($collection as $return)
        {
            $orderId = $return['sellOrderId'];
            $carrier = $return['carrier'];
            $status = $return['status'];
            $created_at = $return['created_at'];
            $reason = $return['reason'];
            $tracking = $return['tracking'];

            if(strtolower(trim($reason))=='damaged')
                $reason=1;
            elseif(strtolower(trim($reason))=='no longer wanted')
                $reason=2;
            elseif(strtolower(trim($reason))=='incorrect item')
                $reason=3;
            elseif(strtolower(trim($reason))=='not as described')
                $reason=4;

            if(strtolower(trim($carrier))=='usps')
                $carrier=1;
            elseif(strtolower(trim($carrier))=='ups')
                $carrier=2;
            elseif(strtolower(trim($carrier))=='fedex')
                $carrier=3;
            elseif(strtolower(trim($carrier))=='amazon dropoff')
                $carrier=4;
            
            $order = orders::where('sellOrderId',$orderId)->get()->first(); 

            if(empty($order))
               continue;
            else
               {                   
                   
                   $return = returns::updateOrCreate(
                    ['sellOrderId'=>$orderId],    
                    ['created_at'=>$created_at,'order_id'=>$order->id,'reason'=>$reason,'carrier'=>$carrier,'trackingNumber'=>$tracking, 'status'=>$status]
                    );
               }
        }
    }
}
