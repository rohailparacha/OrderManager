@extends('layouts.app', ['title' => __('New Orders')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
<script>


$(document).ready(function(){
   
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

    $("#check-all").click(function(){
    $('input:checkbox').not(this).prop('checked', this.checked);
    
});


    $(document).on("click", "#assignBtn", function(){		
        var rows=[];        
        $("input:checkbox").each(function(){
            var $this = $(this);

            if($this.is(":checked")){
                rows.push($this.attr("id"));

            }
        });

      
        var user = $('#users').val();

        if(user==0)
        {
            $('#error').show();             
            return;
        }

        if(rows.length == 0)
        {
                $('#error2').show();
                return;
        }
        
        $.ajax({
                
                type: 'post',
                url: '/assignOrder',
                data: {
                'rows': rows,
                'user': user
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                console.log(data);
                if (data == 'success') {
                    $('#add-modal').modal('hide');
                    $('#error').hide();
                    document.location.reload();
                    $("#addSuccess").show().delay(3000).fadeOut();
                } else
                    $('#error').show();
                },
                
                error: function(XMLHttpRequest, textStatus, errorThrown) {                
                    $('#error').show();
                }        
            });

 });

   

 $(document).on("click", "#assign", function(){		
    $('#assignModal').modal('show');  
    $('#error').hide();
    $('#error2').hide();
    $('#users').val(0);
});
});
</script>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Assign Orders') }}</h3>
                            </div>    
                            
                            <div class="col-4 text-right">
                                <a id="assign" style="color:white;" class="btn btn-sm btn-primary">{{ __('Assign') }}</a>
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
                        <form action="assignFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
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
                                </div>
                                
                            </div>
                            
                            
                            
                        </form>   
                          
                        
                    </div>

                    
                </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="4%"><input type="checkbox" id="check-all" /></th>
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
                                    <th scope="col" width="11%">{{ __('Flag') }}</th>
                                    <th scope="col" width="8%">{{ __('Action') }}</th>
                                    <th scope="col" width="3%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td><input type="checkbox" id="check-{{$order->id}}" width="4%"/></td>                               
                                    
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
                                    
                                        <td width="11%">
                                        @if($order->flag==0)
                                        <span></span>
                                        @else                                        
                                        @foreach($flags as $flag)
                                        @if($flag->id == $order->flag)
                                            <p style="padding: 8px 4px 8px 4px;background-color:{{$flag->color}};color:white;width:100px;text-align: center;font-weight: bold;font-size: 14px;">{{$flag->name}}</p>
                                        @endif
                                        @endforeach             
                                        @endif                           
                                        </td>

                                        <td width="8%"><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
                                        <td class="text-right" width="3%" style="padding:0px!important">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    @foreach($flags as $flag)
                                                    <a class="dropdown-item" href="/orderFlag/{{$order->id}}/{{$flag->id}}">{{$flag->name}}</a>
                                                    @endforeach
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
            

    <!-- Assign Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="assignModal">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
            Please select a user. 
       </div>

       <div class="alert alert-danger" id="error2" style="display:none">
            Please select one or more orders to assign. 
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Assign User to Orders:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Select User:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="users" name="userList" style="">                                
                        <option value=0>Select User</option>
                        @foreach($users as $user)
                        <option value={{$user->id}}>{{$user->name}}</option>                                                           
                        @endforeach                                                      
                    </select>
                   </div>
                   
               </div>
           </div>
       </div>
</div>
      
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="assignBtn">@lang('Assign')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Assign Modal -->
        @include('layouts.footers.auth')
    </div>
@endsection