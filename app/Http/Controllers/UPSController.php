<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator; 
use Redirect; 
use Response; 
use Session;
use GuzzleHttp\Client;
use Carbon\Carbon;
use App\orders;
use App\carriers;

class UPSController extends Controller
{
    public function test()
    {
        $this->checkFedexTracking('','122816215025810');
    }
    
    public function index(Request $request)
    {
            $success=0;
            $records = $request->data;
            $bceCarrier = carriers::where('name','UPS')->get()->first();
            $fedexCarrier = carriers::where('name','Fedex')->get()->first();
          
            foreach($records as $record)
            {              
                try{    
                $issues = '';  
                if(empty(trim($record['sellOrderId'])))
                    continue;
        
                $order = orders::where('sellOrderId',$record['sellOrderId'])->get()->first(); 
                
                if(empty($order)|| $order->status=='shipped')
                    continue;
               
                if(!empty($record['tracking']))
                {
                       
            
                    if(strtolower(substr( $record['tracking'], 0, 2 )) === "1z")
                    {
                        $flags = $this->checkTracking($order->id, $record['tracking']); 
                        if(count($flags)>0)
                        {
                            $update = orders::where('sellOrderId',$record['sellOrderId'])                    
                            ->update([
                            'upsTrackingNumber'=>$record['tracking'],         
                            'carrierName'=>$bceCarrier->id,
                            'isBCE'=>true,
                            'upsFlags'=>json_encode($flags)
                            ]);
                            foreach($flags as $flag)
                            {
                                $issues.= $flag.' - ';
                            }
                           
                            $issues = trim(trim($issues),'-');
                            $this->updateSheet($record['sellOrderId'], $issues);
                           
                        }
                        else
                        {
                            $update = orders::where('sellOrderId',$record['sellOrderId'])                    
                            ->update([
                            'upsTrackingNumber'=>$record['tracking'],         
                            'carrierName'=>$bceCarrier->id,
                            'isBCE'=>true,
                            'upsFlags'=>''
                            ]);

                            $this->updateSheet($record['sellOrderId'], '');
                        } 
                       
                    }
                        
                    else
                    {
                         
                        try{
                            $flags = $this->checkFedexTracking($order->id, $record['tracking']);  
                        }
                        catch(\Exception $ex)
                        {
                            
                            $flags[]="Tracking Not Found";
                        }
                        
                
                        if(count($flags)>0)
                        {
                            
                            $update = orders::where('sellOrderId',$record['sellOrderId'])                    
                            ->update([
                            'upsTrackingNumber'=>$record['tracking'],         
                            'carrierName'=>$fedexCarrier->id,
                            'isBCE'=>true,
                            'upsFlags'=>json_encode($flags)
                            ]);
                            foreach($flags as $flag)
                            {
                                $issues.= $flag.' - ';
                            }
                           
                            $issues = trim(trim($issues),'-');
                          
                            $this->updateSheet($record['sellOrderId'], $issues);
                        }
                        else
                        {
                            
                            $update = orders::where('sellOrderId',$record['sellOrderId'])                    
                            ->update([
                            'upsTrackingNumber'=>$record['tracking'],         
                            'carrierName'=>$fedexCarrier->id,
                            'isBCE'=>true,
                            'upsFlags'=>''
                            ]);

                            $this->updateSheet($record['sellOrderId'], '');
                        } 
                    }                                                                   

                    if($update)
                            $success++;                                
                }

                }
                catch(\Exception $ex)
                {
                    
                }
        
                
            }
        
            return response()->json([
                'count' => $success
            ],201);
      

         
    }

    public function updateSheet($sellOrderId, $issues)
    {       
        try{
        
            $client = new client(); 
            $endPoint = env('BCE_SYNC_TOKEN', '');

            $response = $client->request('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'query' => ['issues' => $issues,'sellOrderId' => $sellOrderId,'function' => 'issueUpdate']          
            ]);    
            
            $statusCode = $response->getStatusCode();
        
            $body = json_decode($response->getBody()->getContents()); 
            
  
        }
        catch(\Exception $ex)
        {
         
        }
    }

    public function checkTracking($orderId, $trackingNumber)
    {        
        $endPoint = env('UPS_API_ENDPOINT', '');
        $key = env('UPS_API_KEY','');
        $username = env('UPS_API_USER', '');
        $password = env('UPS_API_PASSWORD', '');

        $flags = array(); 

        $currentStatus = "";
        $labelCreateDate = "";
        $state="";
        $city="";
        $postalCode="";

        try {
            
        $accessRequestXML = new \SimpleXMLElement ( "<AccessRequest></AccessRequest>" );
        $accessRequestXML->addChild ( "AccessLicenseNumber", $key );
        $accessRequestXML->addChild ( "UserId", $username );
        $accessRequestXML->addChild ( "Password", $password );
        $trackRequestXML = new \SimpleXMLElement ( "<TrackRequest></TrackRequest>" );
        $request = $trackRequestXML->addChild ( 'Request' );
        $request->addChild ( "RequestAction", "Track" );
        $request->addChild ( "RequestOption", "activity" );

        $trackRequestXML->addChild ( "TrackingNumber", $trackingNumber );
        
        $requestXML = $accessRequestXML->asXML() . $trackRequestXML->asXML();    
        
        $client = new client(); 
        
        try{
            $options = [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => $requestXML,
            ];

            
            $promise = $client->requestAsync('POST', $endPoint,$options);    
            
            $promise->then(
                function (ResponseInterface $res) {
                return $res;                                     
                },
                function (RequestException $e) {
                return $e;
                }
            );

            $response1 = $promise->wait();
            $statusCode = $response1->getStatusCode();        

            $body = json_decode($response1->getBody()->getContents());
            
            $response = $response1->getBody(); 
            $xml=simplexml_load_string($response);
        
            $city=$xml->Shipment[0]->ShipTo->Address->City;
            $state=$xml->Shipment[0]->ShipTo->Address->StateProvinceCode;
            $postalCode=$xml->Shipment[0]->ShipTo->Address->PostalCode;	
            $currentStatus = $xml->Shipment[0]->Package->Activity[0]->Status->StatusType->Code;
            
            try{
            
                foreach($xml->Shipment[0]->Package->Activity as $activity)
                {
                    if($activity->Status->StatusType->Code=='M' && $activity->Status->StatusCode->Code=='MP')
                    {
                        $date = $activity->GMTDate;
                        $time = $activity->GMTTime;
                        $offset = $activity->GMTOffset;
                        $labelCreateDate = $date.' '.$time.$offset;                
                        list($hours, $minutes) = explode(':', $offset);
                        $seconds = $hours * 60 * 60 + $minutes * 60;
                        $minutes = $seconds / 60;
                        $labelCreateDate = Carbon::parse($labelCreateDate)->addMinutes($minutes);
                                    
                    }
                    
                }              
            }
            
            catch(\Exception $ex)
            {

            }
        }

        catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();     
            $flags[]="API Issue";      
        }

        if ($response == false) {
            $flags[]="API Issue";
        } 
        
        } catch ( Exception $ex ) {
            $flags[]="API Issue";
        }

        if(count($flags)>0)
            return $flags;

    
        if($this->checkDuplicate($orderId,$trackingNumber))
            $flags[]='Duplicate';

        if($this->checkDate($orderId,$labelCreateDate))
            $flags[]='Label Print Date Issue';
        
        if($this->checkAddress($orderId, $city, $state, $postalCode))
            $flags[]='Address Issue';

        if(!($currentStatus=='D' || $currentStatus=='I' ||$currentStatus=='X'||$currentStatus=='P'||$currentStatus=='RS'||$currentStatus=='DO'||$currentStatus=='DD'||$currentStatus=='W'||$currentStatus=='NA'||$currentStatus=='O'))
            $flags[]='Status Issue';
        

        return $flags; 
    }

    public function checkFedexTracking($orderId, $trackingNumber)
    {
        $flags = array(); 
        $currentStatus = "";
        $labelCreateDate = "";
        $state="";
        $city="";
        $postalCode="";

        $client = new \SoapClient(env('FEDEX_URL',''), array('trace' => 1));

        $request['WebAuthenticationDetail'] = array(
            'ParentCredential' => array(
                'Key' => env('FEDEX_KEY',''), 
                'Password' => env('FEDEX_PASSWORD','')
            ),
            'UserCredential' => array(
                'Key' => env('FEDEX_KEY',''), 
                'Password' => env('FEDEX_PASSWORD','')
            )
        );
        
        $request['ClientDetail'] = array(
            'AccountNumber' => env('FEDEX_SHIPACCOUNT',''), 
            'MeterNumber' => env('FEDEX_METER','')
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request using PHP ***');
        $request['Version'] = array(
            'ServiceId' => 'trck', 
            'Major' => '19', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );
        $request['SelectionDetails'] = array(
            'PackageIdentifier' => array(
                
                'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
                'Value' => $trackingNumber 
            ),
            
            'ShipmentAccountNumber' => env('FEDEX_TRACKACCOUNT','') 
        );
        
        
        
        try {
            
            $newLocation = $client->__setLocation(env('FEDEX_ENDPOINT',''));
            
            $response = $client ->track($request);
        
            if ($response -> HighestSeverity != 'FAILURE' && $response -> HighestSeverity != 'ERROR'){
                if($response->HighestSeverity != 'SUCCESS'){
                        $this->trackDetails($response->Notifications, '');                    
                }else{
                    if ($response->CompletedTrackDetails->HighestSeverity != 'SUCCESS'){
                        $this->trackDetails($response->CompletedTrackDetails, '');                        
                    }else{
                        $this->trackDetails($response->CompletedTrackDetails->TrackDetails, '');                 
                    }
                }                
            }else{
                //error
                $flags[]="Tracking Not Found";                
            } 
                        
        } catch (SoapFault $exception) {
            $flags[]="Tracking Not Found";
        }
        
        if(count($flags)>0)
            return $flags;
        if(!empty($this->apidetails['city']))
            $city = $this->apidetails['city']; 
            
        if(!empty($this->apidetails['state']))
            $state = $this->apidetails['state'];

        if(!empty($this->apidetails['status']))
            $currentStatus = $this->apidetails['status'];
                    
        if($this->checkDuplicate($orderId,$trackingNumber))
            $flags[]='Duplicate';

        if($this->checkAddress($orderId, $city, $state, $postalCode))
            $flags[]='Address Issue';

        if(!($currentStatus=='AD' || $currentStatus=='IT' ||$currentStatus=='LO'||$currentStatus=='DL'||$currentStatus=='OD'||$currentStatus=='PU'||$currentStatus=='ED' || $currentStatus=='DP'|| $currentStatus=='AR'|| $currentStatus=='SF' || $currentStatus=='FD'))
            $flags[]='Status Issue';
            
        return $flags; 
    }

    public function checkDate($orderId,$labelCreateDate)
    {
        $order = orders::where('id',$orderId)->get()->first();
        
        $firstDate = $labelCreateDate->format('Y-m-d');
        
        $secondDate = $order->date->format('Y-m-d');
        
        if($secondDate>$firstDate)
            return true; 
        else
            return false; 
    }

    public function checkAddress($orderId, $city, $state, $postalCode)
    {
        
        $order = orders::where('id',$orderId)->get()->first();
        
    
       if(strtolower($order->state)!=strtolower($state) || strtolower($order->city) != strtolower($city))
            return true; 
       else
            return false; 
       
        
    }

    public function checkDuplicate($orderId, $trackingNumber)
    {
        $count = orders::where('upsTrackingNumber',trim($trackingNumber))->where('id','!=',$orderId)->count();
        if($count>0)
            return true; 
        else
            return false; 
    }

    public function trackDetails($details, $spacer){
        $response= array(); 	
        foreach($details as $key => $value){
            if ($key==='DestinationAddress')
            {
                $city = $value->City; 
                $state = $value->StateOrProvinceCode;				
                $this->apidetails['city'] = $city; 
                $this->apidetails['state'] = $state;                 
            }

            if($key==='StatusDetail')
            {
                $this->apidetails['status'] = $value->Code; 
            }
    
            if(is_array($value) || is_object($value)){
                $newSpacer = $spacer. '&nbsp;&nbsp;&nbsp;&nbsp;';                
                $this->trackDetails($value, $newSpacer);
            }elseif(empty($value)){
                
            }else{
                
            }
        }

        
    }
    
  
}
