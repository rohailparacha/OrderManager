@extends('layouts.app', ['title' => __('Processed Orders')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
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


$(document).ready(function(){

$(document).on("click", "#export", function(){		

   try{
    var daterange = "<?php echo $dateRange; ?>";
    var storeFilter = "<?php echo $storeFilter; ?>";
    var accountFilter = "<?php echo $accountFilter; ?>";

var query = {
                daterange:daterange,
                storeFilter:storeFilter,
                accountFilter:accountFilter
            }


var url = "/dueExport?" + $.param(query)

window.location = url;

}
   catch{
       
   }
});

});
</script>

<style>
td,th {
  white-space: normal !important; 
  word-wrap: break-word;  
}
table {
  table-layout: fixed;
}

@media (min-width: 768px)
{
    .main-content .container-fluid
    {
        padding-right: 12px !important;
        padding-left: 12px !important;
    }
}
</style>
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
                                <h3 class="mb-0">{{ __('Due Date Coming Soon') }}</h3>
                            </div>                            
                                                        
                            <div class="col-6" style="text-align:right;">
                                Showing {{$orders->toArray()['from']}} - {{$orders->toArray()['to']}} of {{$orders->toArray()['total']}} records
                            </div> 
                                                    
                        </div>
                        
                            

                        <div class="row align-items-center" style="padding-top:2%;">                            
                        <div class="col-2 offset-md-10" style="text-align:right; float:right;">
                            @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                            @endif                              
                            </div>                             
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

                    <div class="row" style="margin-left:0px!important;">
            <div class="col-12 text-center" id="filters">
                <form action="dueFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                    @csrf
                    <div style="width:100%; padding-bottom:1%;">
                        <div class="form-group">
                            
                            <div style="padding-right:1%;">
                                <select class="form-control" name="storeFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Store Name</option>
                                    @foreach($stores as $store)
                                        <option value='{{$store->id}}' {{ isset($storeFilter) && $store->id == $storeFilter?"selected":"" }}>{{$store->store}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="padding-right:1%;">
                                <select class="form-control" name="accountFilter" style="margin-right:0%;width:170px;">
                                    <option value="0">Select Account</option>                        
                                    @foreach($accounts as $account)
                                    <option value={{$account->id}} {{ isset($accountFilter) && $account->id == $accountFilter?"selected":"" }}>{{$account->email}}</option>                                                           
                                    @endforeach                              
                                    <option value="ebay" {{ isset($accountFilter) && $accountFilter=='ebay'?"selected":"" }}>eBay</option>
                                    <option value="iHerb" {{ isset($accountFilter) && $accountFilter=='iHerb'?"selected":"" }}>iHerb</option>
                                    <option value="Bonanza" {{ isset($accountFilter) && $accountFilter=='Bonanza'?"selected":"" }}>Bonanza</option>
                                    <option value="Target" {{ isset($accountFilter) && $accountFilter=='Target'?"selected":"" }}>Target</option>
                                    <option value="Cindy" {{ isset($accountFilter) && $accountFilter=='Cindy'?"selected":"" }}>Cindy</option>                        
                                    <option value="Jonathan" {{ isset($accountFilter) && $accountFilter=='Jonathan'?"selected":"" }}>Jonathan</option>
                                    <option value="Jonathan2" {{ isset($accountFilter) && $accountFilter=='Jonathan2'?"selected":"" }}>Jonathan2</option> 
                                    <option value="Yaballe" {{ isset($accountFilter) && $accountFilter=='Yaballe'?"selected":"" }}>Yaballe</option>
                                    <option value="SaleFreaks1" {{ isset($accountFilter) && $accountFilter=='SaleFreaks1'?"selected":"" }}>SaleFreaks1</option>
                                    <option value="SaleFreaks2" {{ isset($accountFilter) && $accountFilter=='SaleFreaks2'?"selected":"" }}>SaleFreaks2</option>
                                    <option value="SaleFreaks3" {{ isset($accountFilter) && $accountFilter=='SaleFreaks3'?"selected":"" }}>SaleFreaks3</option>
                                    <option value="SaleFreaks4" {{ isset($accountFilter) && $accountFilter=='SaleFreaks4'?"selected":"" }}>SaleFreaks4</option>
                                    <option value="SaleFreaks5" {{ isset($accountFilter) && $accountFilter=='SaleFreaks5'?"selected":"" }}>SaleFreaks5</option>                        
                                    <option value="Vaughn" {{ isset($accountFilter) && $accountFilter=='Vaughn'?"selected":"" }}>Vaughn</option>                        
                                    <option value="Other" {{ isset($accountFilter) && $accountFilter=='Other'?"selected":"" }}>Other</option> 
                                </select>
                            </div>
                            
                            <div style="padding-right: 1%; float:right; width=170px; ">                                
                                <input class="form-control" type="text" name="daterange" value="{{$dateRange ?? ''}}" />
                            </div>

                            <div>
                            
                            <input type="submit" value="Filter" class="btn btn-primary btn-md">
                            @if(auth()->user()->role==1)
                            <a id="export" class="btn btn-primary btn-md" style="color:white;">Export</a>   
                            @endif
                            </div>

                                 
                        </div>
                    </div>
                    
                    

                </form>                
                
            </div>
        </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th width="12%" scope="col">{{ __('Date') }}</th>
                                    <th width="12%" scope="col">{{ __('Ship Due Date') }}</th>
                                    <th width="12%" scope="col">{{ __('Marketplace') }}</th>
                                    <th width="12%" scope="col">{{ __('Store Name') }}</th>
                                    <th width="12%" scope="col">{{ __('Account') }}</th>
                                    <th width="10%" scope="col">{{ __('Sell Order Id') }}</th>
                                    <th width="10%" scope="col">{{ __('Purchase Order Id') }}</th>
                                    <th width="10%" scope="col">{{ __('Buyer Name') }}</th>
                                    <th width="8%" scope="col">{{ __('Qty') }}</th>
                                    <th width="10%" scope="col">{{ __('Remaining Days') }}</th>
                                    <th width="9%" scope="col">{{ __('Sell Total Amount') }}</th>
                                    <th width="10%" scope="col">{{ __('Purchase Total Amount') }}</th>
                                    <th width="8%" scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>                                                                               
                                        <td width="10%">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td width="10%">
                                            @if(!empty($order->dueShip))
                                            {{ $provider::getIranTime(date_format(date_create($order->dueShip), 'm/d/Y H:i:s')) }}
                                            @endif                                        
                                        </td>
                                        <td width="11%">{{ $order->marketplace }}</td>
                                        <td width="12%">{{ $order->storeName }}</td>
                                        <td width="11%"> 
                                        @foreach($accounts as $account)
                                            @if($account->id == $order->account_id)
                                                {{$account->email}}
                                            @endif 
                                        @endforeach
                                        @if(!is_numeric($order->account_id))
                                        {{$order->account_id}}
                                        @endif
                                        </td>
                                                    
                                        <td width="13%">{{ $order->sellOrderId }}</td>
                                        <td width="12%">
                                        @if(!empty($order->trackingLink))
                                        <a  target="_blank" href={{$order->trackingLink}}>{{ $order->poNumber }}</a>
                                        @else
                                        {{ $order->poNumber }}
                                        @endif
                                        </td>
                                        <td width="10%">{{ $order->buyerName }}</td>
                                        <td width="7%">{{ $order->quantity }}</td>
                                        
                                        @if(\Carbon\Carbon::now()->diffInDays( \Carbon\Carbon::parse($order->dueShip)->format('Y-m-d'),false )<2)
                                        <td width="7%" style="color:red;">
                                        @else
                                        <td width="7%">
                                        @endif
                                        {{ \Carbon\Carbon::now()->diffInDays( \Carbon\Carbon::parse($order->dueShip)->format('Y-m-d'),false ) }} days</td>                                       
                                        <td width="9%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td width="9%">{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>
                                        <td width="9%"><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
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