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



$('#checkPass').on('show.bs.modal', function(e) {        
        $('#error').text();
        var link     = $(e.relatedTarget),
        id = link.data("id"),
        account = link.data('account');
        
        $('#idTbx').val(id);
        $('#accountTbx').val(account);
});

$('#modal-confirm-password').on('click',function(event){            
           var pass = $('#passTbx').val();           
           var id = $('#idTbx').val();      
           var account = $('#accountTbx').val();           
         
           $.ajax({
               
           type: 'post',
           url: '/checkAssignPass',
           data: {
           'password': pass,
           'id' : id,
           'account': account
           },
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
           success: function (data) {
           console.log(data);
           if (data == 'success') {
               $('#checkPass').modal('hide');
               $('#error').hide();  
               document.location.reload();                       
           } 
           else if(data== 'failure')
           {
            $('#error').text('Password is incorrect');                
            $('#error').show();
           }
               
           else if(data== 'deleteIssue')
           {
            $('#error').text('API returned error');                  
            $('#error').show();
           }
            else if(data== 'dbIssue')
            {
                $('#error').text('Issue while updating data in local db');                   
                $('#error').show();
            }
           },
           
           error: function(XMLHttpRequest, textStatus, errorThrown) {                
               $('#error').show();
           }        
       });
       })

   
$( function() {    
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
                sourceFilter:sourceFilter,
                route:'food'
            }


var url = "/orderexport?" + $.param(query)

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
        
        @if(Session::has('inner_msg'))
        <div class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('inner_msg')}}</div>
        @endif
        
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <h3 class="mb-0">{{ __('New Orders - Food') }}</h3>
                            </div>  
                            <div class="col-6" style="padding-left:2%; margin-top:2%;">
                            <form class="form-inline" action="/assignFood" method="post" enctype="multipart/form-data" style="float:left;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file"  style="width:250px!important"/>                
                            
                                    <input type="submit" class="btn btn-primary" value="Assign Attributes" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>

                            <a href="./orderTemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">Template</a>   
                            </div>
                            <div class="col-2" style="text-align:right;">
                                @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                                @endif
                                @if(auth()->user()->role==1 || auth()->user()->role==2)
                                <a href="sync" class="btn btn-primary btn-md">Sync</a>
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
                        <form action="newFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <input type="hidden" value="food" name="page">
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
                                    <th scope="col" width="8%">{{ __('Action') }}</th>
                                    <th scope="col" width="8%">Sheet</th>
                                    <th scope="col" width="8%">Flag</th>
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
                                        @if($order->lowestPrice == 0)
                                        <td width="10%" style="color:red;">
                                        @else
                                        <td width="10%">
                                        @endif
                                        {{number_format((float)$order->lowestPrice , 2, '.', '')}}</td>
                                         
                                        <td width="10%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        
                                        @if((number_format(((float)$order->totalAmount +(float)$order->shippingPrice) *0.85 , 2, '.', '') - number_format((float)$order->lowestPrice , 2, '.', '')) < ($order->quantity * 5))
                                        <td width="10%" style="color:red;">
                                        @else
                                        <td width="10%">
                                        @endif
                                        {{ number_format((((float)$order->totalAmount +(float)$order->shippingPrice) *0.85) - (float)$order->lowestPrice , 2, '.', '') }}                                        
                                        </td>
                                        
                                        
                                        <td width="8%"><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
                                        <td class="text-right" width="3%" style="padding:0px!important">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    @foreach($accounts as $account)       
                                                        @if($order->itemcount>1 || $order->lowestPrice==0 || (in_array($order->state,$disabledStates) && $statecheck && ($account->name=='jonathan' ||$account->name=='jonathan2')))                                       
                                                            <a class="dropdown-item btnTransfer"  data-toggle="modal" data-target="#checkPass"  data-id={{$order->id}} data-account={{$account->id}} href="#">{{$account->name}}</a>
                                                        @else
                                                            @if(empty($route))
                                                            <a class="dropdown-item" href="/accTransfer/{{$order->id}}/{{$account->id}}">{{$account->name}}</a>
                                                            @else
                                                            <a class="dropdown-item" href="/accTransfer/{{$route}}/{{$order->id}}/{{$account->id}}">{{$account->name}}</a>
                                                            @endif
                                                        @endif
                                                    @endforeach                                                   
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right" width="3%" style="padding:0px!important">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    @foreach($flags as $flag)
                                                         @if(empty($route))
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/{{$flag->id}}">{{$flag->name}}</a>
                                                    @else
                                                    <a class="dropdown-item" href="/orderFlag/{{$route}}/{{$order->id}}/{{$flag->id}}">{{$flag->name}}</a>
                                                    @endif
                                                    @endforeach
                                                   @if(empty($route))
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/0">{{ __('Unflag') }}</a>
                                                    @else
                                                    <a class="dropdown-item" href="/orderFlag/{{$route}}/{{$order->id}}/0">{{ __('Unflag') }}</a>
                                                    @endif
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

        <!-- Confirm Admin Password -->
<div class="modal fade" tabindex="-1" role="dialog" id="checkPass">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
            Admin Password Is Incorrect
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Confirm password to continue with order transfer:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="idTbx" />
            <input type="hidden" value="" id="accountTbx" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Password:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="passTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>




       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-confirm-password">@lang('Confirm Password')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Confirm Admin Password -->
            
        @include('layouts.footers.auth')
    </div>
@endsection