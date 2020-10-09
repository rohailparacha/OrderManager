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
    
    public function index(Request $request)
    {
            $success=0;
            $records = $request->data;
            $bceCarrier = carriers::where('name','UPS')->get()->first();
          
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
        if(empty($postalCode))
        {
            if($order->state!=$state || $order->city != $city)
                return true; 
            else
                return false; 
        }
        else
        {
            if($order->state!=$state || $order->postalCode != $postalCode)
                return true; 
            else
                return false; 
        }
        
    }

    public function checkDuplicate($orderId, $trackingNumber)
    {
        $count = orders::where('upsTrackingNumber',trim($trackingNumber))->where('id','!=',$orderId)->count();
        if($count>0)
            return true; 
        else
            return false; 
    }
    
  
}
