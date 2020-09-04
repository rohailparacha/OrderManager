@extends('layouts.app', ['title' => __('New Orders')])

@section('content')
@include('layouts.headers.cards')

<style>
td,th {
  white-space: normal !important; 
  padding-left:1rem!important;
  padding-right:1rem!important;  
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
@inject('provider', 'App\Http\Controllers\orderController')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
        
        @if(Session::has('inner_msg'))
        <div class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('inner_msg')}}</div>
        @endif
        
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Tracking Lookup Orders') }}</h3>
                            </div>  
                            
                            <div class="col-6" style="text-align:right;">
                                @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                                @endif
                                Showing {{$orders->toArray()['from']}} - {{$orders->toArray()['to']}} of {{$orders->toArray()['total']}} records
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
                        <form action="lookupFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <div style="width:100%; padding-bottom:2%;">
                                <div class="form-group">
                                    
                                



                            <div style="padding-right: 1%; float:right; width=170px; ">                                
                                <input class="form-control" type="text" name="cityFilter" placeholder="Type City" value="{{$cityFilter ?? ''}}" />
                            </div>

                            <div style="padding-right:1%;">
                                <select class="form-control" name="stateFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">State Name</option>
                                    @foreach($states as $state)
                                        <option value='{{$state->code}}' {{ isset($stateFilter) && $state->code == $stateFilter?"selected":"" }}>{{$state->name}} - {{$state->code}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="padding-right: 1%; float:right; width=170px; ">                                
                                <input class="form-control" type="text" name="zipFilter" placeholder="Type Zip Code" value="{{$zipFilter ?? ''}}" />
                            </div>

                            <div style="padding-right: 1%; float:right; width=170px; ">                                
                                <input class="form-control" type="text" name="daterange" value="{{$dateRange ?? ''}}" />
                            </div>
                                    
                                    <input type="submit" value="Filter" class="btn btn-primary btn-md">    
                                    
                                </div>
                                
                            </div>
                            
                            
                            
                        </form>   
                          
                        
                    </div>

                    
                </div>
                
                    <div class="table-responsive">
                    <table width="100%" class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%" scope="col">{{ __('Date') }}</th>
                                    <th width="8%" scope="col">{{ __('Marketplace') }}</th>
                                    <th width="10%" scope="col">{{ __('Store Name') }}</th>
                                    <th width="11%" scope="col">{{ __('Sell Order Id') }}</th>
                                    <th width="11%" scope="col">{{ __('Purchase Order Id') }}</th>
                                    <th width="10%" scope="col">{{ __('Buyer Name') }}</th>
                                    <th width="10%" scope="col">{{ __('State') }}</th>
                                    <th width="8%" scope="col">{{ __('Qty') }}</th>
                                    <th width="8%" scope="col">{{ __('Sell Total') }}</th>
                                    <th width="8%" scope="col">{{ __('Purchase Total') }}</th>
                                    <th width="10%" scope="col">{{ __('Tracking Number') }}</th>
                                    <th width="8%" scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        
                                        <td width="10%">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td width="8%">{{ $order->marketplace }}</td>
                                        <td width="10%">{{ $order->storeName }}</td>
                                        <td width="11%">{{ $order->sellOrderId }}</td>
                                        <td width="11%">{{ $order->poNumber }}</td>
                                        <td width="10%">{{ $order->buyerName }}</td>
                                        <td width="8%">{{ $order->state }}</td>
                                        <td width="8%">{{ $order->quantity }}</td>
                                        <td width="8%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td width="8%">{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>                                
                                        
                                        <td width="10%">
                                        @if(empty($order->upsTrackingNumber))
                                            {{$order->trackingNumber}}
                                        @else
                                            {{$order->upsTrackingNumber}}
                                        @endif
                                        </td>

                                        <td width="8%"><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
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