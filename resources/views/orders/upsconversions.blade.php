@extends('layouts.app', ['title' => __('BCE Conversions')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<style>
@media (min-width: 768px)
{
    .main-content .container-fluid
    {
        padding-right: 12px !important;
        padding-left: 12px !important;
    }
}
</style>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
});
</script>

<script>
$(document).ready(function(){

$("button[name = 'shipBtn']").on('click',function(event){ 
    
    var btnId = '#'+this.id;
    var id = $(btnId).attr('data-id');
    var carrier = $(btnId).attr('data-carrier');
    var tracking = $(btnId).attr('data-track');
    
    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'carrier': 'UPS',
               'id' : id,
               'tracking' : tracking,
               'status': 'new',
               'type':'ship',
               'source':'UPS'
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#errorShipping').hide();  
                   document.location.reload();                       
               } 
               else
               {                                
                $('#errorShipping').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#errorShipping').show();
               }        
           });


 });

$(document).on("click", "#export", function(){		

try{
    var storeFilter = "<?php echo empty($storeFilter)?"":$storeFilter; ?>";
    var daterange = "<?php echo $dateRange; ?>";

var query = {                
                storeFilter:storeFilter,
                daterange:daterange,
                option:1
            }

var url = "/upsexport?" + $.param(query)

window.location = url;

}
catch{
    
}
});
});
</script>

<div class="container-fluid mt--7">
        @if(Session::has('error_msg'))
        <div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('error_msg')}}</div>
        @endif
        @if(Session::has('success_msg'))
        <div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('success_msg')}}</div>
        @endif

        @if(Session::has('count_msg'))
        <div class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('count_msg')}}</div>
        @endif
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('UPS Conversions - Waiting For Tracking') }}</h3>                                
                            </div>  
                            <div class="col-6" style="text-align:right;">
                            <a href="conversionssync/1" class="btn btn-primary btn-md">Sync</a>                            
                            @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                            @endif 
                            </div>                          
                        </div>                        
                    </div>
                    
                    <div class="row" style="margin-left:0px!important;">
                        <div class="col-12 text-center" id="filters">
                        <form action="upsfilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <div style="width:100%; padding-bottom:2%;">
                                <div class="form-group">                                

                                <div style="padding-right:1%;">
                                <select class="form-control" name="storeFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Store Name</option>
                                    @foreach($stores as $store)
                                    <option value="{{$store->id}}" {{ isset($storeFilter) && $storeFilter==$store->id?"selected":"" }}>{{$store->store}}</option>
                                    @endforeach
                                    
                                    
                                </select>
                                </div>

                                <div style="padding-right: 1%; float:right; width=170px; ">                                
                                    <input class="form-control" type="text" name="daterange" value="{{$dateRange ?? ''}}" />
                                </div>
                                   
                                                                        
                                    
                                    <input type="submit" value="Filter" class="btn btn-primary btn-md">     
                                    <a id="export" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px;">Export</a>          
                                </div>
                                
                            </div>
                            
                            
                            
                        </form>   
                          
                        
                    </div>

                    
                </div>
               
                    <div class="col-12">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    </div>                    

                    <div class="card-header border-0" style="padding-top:0px;">
                        <div class="row align-items-center">
                            <div class="col-8">
                        <strong><span  style="font-size:14px; color:red; padding-left:5px;">Waiting For Tracking: {{$count}}</span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Date') }}</th>  
                                    <th scope="col">{{ __('Order Date') }}</th>                                    
                                    <th scope="col">{{ __('Store Name') }}</th>                                    
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Purchase Order Id') }}</th>                                    
                                    <th scope="col">{{ __('City') }}</th>
                                    <th scope="col">{{ __('State') }}</th>
                                    <th scope="col">{{ __('Zip Code') }}</th>
                                    <th scope="col">{{ __('Old Tracking Number') }}</th>                                    
                                    <th scope="col">{{ __('UPS Tracking Number') }}</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>{{ $provider::getIranTime(date_format(date_create($order->of_bce_created_at), 'm/d/Y H:i:s')) }}</td>                                        
                                        <td>{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td>{{ $order->storeName }}</td>
                                        <td>{{ $order->buyerName }}</td>
                                        <td><a target="_blank" href="orderDetails/{{$order->id}}">{{ $order->sellOrderId }}</a></td>
                                        <td><a target="_blank" href="https://www.amazon.com/progress-tracker/package/ref=ppx_yo_dt_b_track_package?_encoding=UTF8&itemId=klpjsskrrrpoqn&orderId={{$order->poNumber}}">{{ $order->poNumber }}</a></td>        
                                        
                                        
                                        <td>{{ $order->city }}</td>
                                        <td>{{ $order->state }}</td>
                                        <td>{{ $order->postalCode }}</td>
                                        <td>{{ $order->trackingNumber }}</td>
                                        
                                        @if(strtolower(substr( $order->upsTrackingNumber, 0, 2 )) === "1z")
                                        <td><a target="_blank" href="https://www.ups.com/track?loc=en_US&tracknum={{ $order->upsTrackingNumber }}">{{ $order->upsTrackingNumber }}</a></td>                                                 
                                        @else
                                        <td><a target="_blank" href="https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber={{ $order->upsTrackingNumber }}&cntry_code=us&locale=en_US">{{ $order->upsTrackingNumber }}</a></td>                                                 
                                        @endif

                                        @if(empty($order->upsTrackingNumber))
                                        <td>UPS Tracking Missing</td>
                                                                            @elseif($order->status!='shipped')
                                            <td><button name="shipBtn" id="ship{{$loop->iteration}}" data-id= {{$order->id}} data-track= {{$order->upsTrackingNumber}} data-carrier= "UPS" class="btn btn-primary btn-sm">Ship</button></td>
                                        @else
                                            <td>Shipped</td>
                                        @endif
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="padding-right:2%">
                    <div class="col-md-4 offset-md-8" style="text-align:right">
                    <span>Showing {{$orders->toArray()['from']}} - {{$orders->toArray()['to']}} of {{$orders->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$orders->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection