@extends('layouts.app', ['title' => __('New Orders')])

@section('content')
@include('layouts.headers.cards')

<style>
td,th {
  white-space: normal !important; 
  word-wrap: break-word;
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
<script>

$(document).ready(function(){
   
$( function() {
    debugger;
    var price = <?php echo json_encode($maxPrice); ?>;
    var minAmount = <?php echo json_encode($minAmount); ?>;
    var maxAmount = <?php echo json_encode($maxAmount); ?>;
    $( "#price-range" ).slider({
      range: true,
      min: 0,
      max: price,
      values: [ minAmount, maxAmount ],
      slide: function( event, ui ) {
        $( "#amount" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
      }
    });

    $( "#amount" ).val( $( "#price-range" ).slider( "values", 0 ) +
      " - " + $( "#price-range" ).slider( "values", 1 ) );
  } );

$(document).on("click", "#export", function(){		

try{
    var storeFilter = "<?php echo empty($storeFilter)?"":$storeFilter; ?>";
    var marketFilter = "<?php echo empty($marketFilter)?"":$marketFilter; ?>";
    var stateFilter = "<?php echo empty($stateFilter)?"":$stateFilter; ?>";
    var amountFilter = "<?php echo $minAmount; ?>"+" - "+"<?php echo $maxAmount; ?>";    
    var sourceFilter ="<?php echo empty($sourceFilter)?"":$sourceFilter; ?>";

var query = {                
                storeFilter:storeFilter,
                marketFilter:marketFilter,
                stateFilter:stateFilter,
                amountFilter:amountFilter,
                sourceFilter:sourceFilter
            }


var url = "/samuelexport?" + $.param(query)

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
                                <h3 class="mb-0">{{ __('Samuel - Auto Fulfill Orders') }}</h3>
                            </div>  
                            
                            <div class="col-6" style="text-align:right;">
                                @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                                @endif
                                @if(auth()->user()->role==1 || auth()->user()->role==2)
                                <a href="sync" class="btn btn-primary btn-md">Sync</a>
                                <a href="samuelProcess" class="btn btn-primary btn-md">Process Orders</a>
                                 @endif
                            </div>                      
                               
                        </div>
                        <div class="row align-items-center" style="padding-top:2%;">                            
                            <div class="col-4 offset-md-8" style="text-align:right;">
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
                        <form action="autoFulfillFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <div style="width:100%; padding-bottom:2%;">
                                <div class="form-group">
                                    
                                <div style="padding-right:1%;">
                                <select class="form-control" name="marketFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Marketplaces</option>
                                    <option value="1" {{ isset($marketFilter) && $marketFilter=="1"?"selected":"" }}>Amazon</option>
                                    <option value="2" {{ isset($marketFilter) && $marketFilter=="2"?"selected":"" }}>eBay</option>
                                    <option value="3" {{ isset($marketFilter) && $marketFilter=="3"?"selected":"" }}>Walmart</option>                                                                        
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
                                <select class="form-control" name="stateFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">State Name</option>
                                    @foreach($states as $state)
                                        <option value='{{$state->code}}' {{ isset($stateFilter) && $state->code == $stateFilter?"selected":"" }}>{{$state->name}} - {{$state->code}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div style="padding-right:1%;">
                                <select class="form-control" name="sourceFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Select Source</option>
                                    <option value="1" {{ isset($sourceFilter) && $sourceFilter=="1"?"selected":"" }}>Amazon</option>
                                    <option value="2" {{ isset($sourceFilter) && $sourceFilter=="2"?"selected":"" }}>eBay</option>
                                    <option value="3" {{ isset($sourceFilter) && $sourceFilter=="3"?"selected":"" }}>IHerb</option>  
                                </select>
                            </div>
                                    
                                    <div style="padding-right:3%;">
                                    <p id="price">
                                        <label for="amount">Total Purchase Amount</label>
                                        <input  class="form-control"   style="width:200px;" type="text" name= "amountFilter" id="amount" readonly/>
                                    </p>
                                        <div id="price-range" style="width:200px;"></div>
                                    </div>
                                    
                                    <input type="submit" value="Filter" class="btn btn-primary btn-md">    
                                    <a id="export" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px;">Export</a>       
                                </div>
                                
                            </div>
                            
                            
                            
                        </form>   
                          
                        
                    </div>

                    
                </div>
                
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="9%">{{ __('Date') }}</th>
                                    <th scope="col" width="9%">{{ __('Marketplace') }}</th>
                                    <th scope="col" width="9%">{{ __('Store Name') }}</th>
                                    <th scope="col" width="9%">{{ __('Sell Order Id') }}</th>
                                    <th scope="col" width="9%">{{ __('Buyer Name') }}</th>
                                    <th scope="col" width="9%">{{ __('Source') }}</th>
                                    <th scope="col" width="9%">{{ __('State') }}</th>
                                    <th scope="col" width="8%">{{ __('Qty') }}</th>                                   
                                    <th scope="col" width="10%">{{ __('Total Purchase Amount') }}</th>
                                     <th scope="col" width="10%">{{ __('Total Amount') }}</th>
                                     <th scope="col" width="11%">{{ __('Net') }}</th>
                                    <th scope="col" width="11%">{{ __('Flag') }}</th>
                                    <th scope="col" width="8%">{{ __('Action') }}</th>
                                    <th scope="col" width="3%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                    
                                        <td width="9%">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>                                       
                                        <td width="9%">{{ $order->marketplace }}</td>
                                        <td width="9%">{{ $order->storeName }}</td>
                                        <td width="9%">{{ $order->sellOrderId }}</td>
                                        <td width="9%">{{ $order->buyerName }}</td>
                                        <td width="9%">{{ $order->source }}</td>
                                        <td width="9%">{{ $order->state }}</td>
                                        <td width="8%">{{ $order->quantity }}</td>
                                        <td width="10%">{{number_format((float)$order->lowestPrice , 2, '.', '')}}</td>
                                         
                                        <td width="10%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                       
                                        @if((number_format(((float)$order->totalAmount +(float)$order->shippingPrice) *0.85 , 2, '.', '') - number_format((float)$order->lowestPrice , 2, '.', '')) < ($order->quantity * 5))
                                        <td width="10%" style="color:red;">
                                        @else
                                        <td width="10%">
                                        @endif
                                        {{ number_format((((float)$order->totalAmount +(float)$order->shippingPrice) *0.85) - (float)$order->lowestPrice , 2, '.', '') }}  
                                        </td>
                                        <td width="11%">
                                        @if($order->flag==0)
                                        <span></span>
                                        @elseif($order->flag==1)
                                        <p style="padding: 8px 4px 8px 4px;background-color:red;color:white;width:100px;text-align: center;font-weight: bold;font-size: 14px;">Overpriced</p>
                                        @elseif($order->flag==2)
                                        <p style="padding: 8px 4px 8px 4px; background-color:orange; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Quantity Limit</p>
                                        @elseif($order->flag==3)
                                        <p style="padding: 8px 4px 8px 4px; background-color:yellow; color:black; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Unavailable</p>
                                        @elseif($order->flag==4)
                                        <p style="padding: 8px 4px 8px 4px; background-color:green; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Date</p>
                                        @elseif($order->flag==5)
                                        <p style="padding: 8px 4px 8px 4px; background-color:blue; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Address Issue</p>
                                        @elseif($order->flag==6)
                                        <p style="padding: 8px 4px 8px 4px; background-color:gray; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Other</p>
                                        @elseif($order->flag==7)
                                        <p style="padding: 8px 4px 8px 4px; background-color:purple; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Tax Issue</p>                                        
                                        @elseif($order->flag==8)
                                        <p style="padding: 8px 4px 8px 4px; background-color:brown; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Cindy</p>                                        
                                        @elseif($order->flag==9)
                                        <p style="padding: 8px 4px 8px 4px; background-color:brown; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Jonathan</p>                                    
                                        
                                        @elseif($order->flag==10)
                                        <p style="padding: 8px 4px 8px 4px; background-color:brown; color:white; width:100px;text-align: center;font-weight: bold;font-size: 14px;">Samuel</p>                                          
                                        @endif
                                        </td>
                                        <td width="8%"><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
                                        <td class="text-right" width="3%" style="padding:0px!important">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/1">{{ __('Overpriced') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/2">{{ __('Qty Limit') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/3">{{ __('Unavailable') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/4">{{ __('Date') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/5">{{ __('Address Problem') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/6">{{ __('Other') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/7">{{ __('Tax Issue') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/8">{{ __('Cindy') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/9">{{ __('Jonathan') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/10">{{ __('Samuel') }}</a>
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/0">{{ __('Unflag') }}</a>
                                                </div>
                                            </div>
                                        </td>
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