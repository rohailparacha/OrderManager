@extends('layouts.app', ['title' => __('Report')])

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
    var marketFilter = "<?php echo $marketFilter; ?>";
    var statusFilter = "<?php echo $statusFilter; ?>";
    var userFilter = "<?php echo $userFilter; ?>";

var query = {
                daterange:daterange,
                storeFilter:storeFilter,
                marketFilter:marketFilter,
                statusFilter:statusFilter,
                userFilter:userFilter
            }


var url = "/export?" + $.param(query)

window.location = url;

}
   catch{
       
   }
});

});

</script>
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

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Report') }}</h3>
                            </div>    
                            <div class="col-6" style="text-align:right;">
                                Showing {{$orders->toArray()['from']}} - {{$orders->toArray()['to']}} of {{$orders->toArray()['total']}} records
                            
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
                <form action="filter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                    @csrf
                    <div style="width:100%; padding-bottom:1%;">
                        <div class="form-group">
                            <div style="padding-right:1%;">
                                <select class="form-control" name="marketFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Marketplaces</option>
                                    <option value="1" {{ isset($marketFilter) && $marketFilter=="1"?"selected":"" }}>Amazon</option>
                                    <option value="2" {{ isset($marketFilter) && $marketFilter=="3"?"selected":"" }}>eBay</option>
                                    <option value="3" {{ isset($marketFilter) && $marketFilter=="9"?"selected":"" }}>Walmart</option>                                                                        
                                </select>
                            </div>



                            <div style="padding-right:1%;">
                                <select class="form-control" name="storeFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Store Name</option>
                                    @foreach($stores as $store)
                                        <option value='{{$store->id}}' {{ isset($storeFilter) && $store->id == $storeFilter?"selected":"" }}>{{$store->store}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="padding-right:1%;">
                                <select class="form-control" name="userFilter" style="margin-right:0%;width:170px;">
                                    <option value="0">Username</option>
                                    @foreach($users as $user)
                                        <option value='{{$user->id}}' {{ isset($userFilter) && $user->id == $userFilter?"selected":"" }}>{{$user->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="padding-right:1%;">
                                <select class="form-control" name="statusFilter" style="margin-right:0%;width:170px;">
                                    <option value="0" {{ isset($statusFilter) && $statusFilter=="0"?"selected":"" }}>Status</option>
                                    <option value="1" {{ isset($statusFilter) && $statusFilter=="1"?"selected":"" }}>Unshipped</option>
                                    <option value="2" {{ isset($statusFilter) && $statusFilter=="2"?"selected":"" }}>Pending</option>
                                    <option value="3" {{ isset($statusFilter) && $statusFilter=="3"?"selected":"" }}>Processed</option>
                                    <option value="4" {{ isset($statusFilter) && $statusFilter=="4"?"selected":"" }}>Cancelled</option>
                                    <option value="5" {{ isset($statusFilter) && $statusFilter=="5"?"selected":"" }}>Shipped</option>
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
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Marketplace') }}</th>
                                    <th scope="col">{{ __('Store Name') }}</th>
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Sell Total') }}</th>
                                    <th scope="col">{{ __('Purchase Order ID') }}</th>
                                    <th scope="col">{{ __('Purchase Total') }}</th>
                                    <th scope="col">{{ __('Carrier Name') }}</th>
                                    <th scope="col">{{ __('Tracking Number') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('View') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        
                                        <td>{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td>{{ $order->marketplace }}</td>
                                        <td>{{ $order->storeName }}</td>
                                        <td>{{ $order->buyerName }}</td>
                                        <td>{{ $order->sellOrderId }}</td>                                                                                                                        
                                        <td>{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td>{{ $order->poNumber }}</td>
                                        <td>{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>
                                        @if(!empty($order->carrierName))
                                        <td>{{ $carrierArr[$order->carrierName] }}</td>
                                        @else
                                        <td>{{ $order->carrierName }}</td>
                                        @endif
                                        @if(empty($order->newTrackingNumber))
                                        <td>{{ $order->trackingNumber }}</td>
                                        @else
                                        <td>{{ $order->newTrackingNumber }}</td>
                                        @endif
                                        <td>{{ $order->status }}</td>
                                        <td><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
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