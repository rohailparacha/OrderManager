<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator; 
use Redirect; 
use Response; 
use Session;
use GuzzleHttp\Client;

class keepaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    //
    public function index()
    {
        return view('keepa.index');
    }

    public function getResponse(Request $request)
    {
        $input = [
            'asin' => $request->get('asin'),
            'offer' => $request->get('offer'),
        ];

        $rules = [
            'asin'    => 'required',
            'offer'    => 'required|numeric|min:20',                    
        ];

        $validator = Validator::make($input,$rules);

        if($validator->fails())
        {
           Session::flash('error_msg', __('Please check the errors and try again.'));
           return Redirect::back()->withInput()->withErrors($validator);
        }
        
       
        
        try{
            $client = new client(); 
            $endPoint = "https://api.keepa.com/product";
            $key="f26tbbe0537u7c8d13tffvgkq4b8s1jd9cipv51uvbagasg3q8g07d2gfjmboti4";

            $response = $client->request('GET', $endPoint,
            [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'query' => ['key' => $key,'domain' => 1,'asin' => $request->get('asin'),'offers' => $request->get('offer')]          
            ]);    
            
            $statusCode = $response->getStatusCode();
        
            $json = json_decode($response->getBody()->getContents());               

            $tokensLeft = $json->tokensLeft;
            $tokensConsumed = $json->tokensConsumed;

            $products = $json->products;

            $offers = array();
            foreach($products as $product)
            {
                $offersRcvd = $product->offers;

                foreach($offersRcvd as $off)
                {
                    if($off->isShippable==true && $off->condition==1 && $off->isPrime == true && $off->isPreorder == false)
                    {
                                        
                        $lastSeen = $off->lastSeen;
                        $sellerId = $off->sellerId;   
                        $price = $off->offerCSV[count($off->offerCSV)-2];
                        $offer_object = new \stdClass; 
                        $offer_object->lastSeen = $lastSeen; 
                        $offer_object->lastSeen = date('r', ($lastSeen + 21564000) * 60);
                        $offer_object->sellerId = $sellerId; 
                        $offer_object->price = $price;   
                        $offers[]=$offer_object;
                        
                    }
                }
            }   
     
        return view('keepa.result',compact('offers','tokensConsumed','tokensLeft'));
            
        }
        catch(\Exception $ex)
        {

        }
    }
}
