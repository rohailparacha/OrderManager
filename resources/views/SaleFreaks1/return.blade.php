@extends('layouts.app', ['title' => __('Gmail Integration Management')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')

<script src="{{ asset('argon') }}/js/jquery.printPage.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />



<style>
td.prodtd,th.prodth {
  white-space: normal !important; 
  word-wrap: break-word;  
  padding-left:1rem!important;
  padding-right:1rem!important;
}
th.prodth
{
    text-align: center;
}

.specifictd{
    text-align: center;
}
.prodtable {
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
    $(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
});
 
        $('#addLabel').on('show.bs.modal', function(e) {                
            
            var link     = $(e.relatedTarget),
            id = link.data("id");
    
            console.log(link);
            $('#labelId').val(id);
        });

        
  
        $('#btnAddCat').on('click',function(event){ 
            $('#addCat').modal('show');  
            $('#editTitle').hide();
            $('#modal-que-edit').hide(); 
            $('#addTitle').show();
            $('#modal-que-save').show();
            $('#editSuccess').hide();
            $('#addSuccess').hide();
            $('#error').hide();             
            $('#sellOrderTbx').val('');
            $('#trackingTbx').val('');                  
            $('#carrierTbx').val(0);
            $('#reasonTbx').val(0);
            $('#orderissue').hide();
            $(".print-error-msg").hide();
            $('#sellOrderTbx').attr('readonly',false);

        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {    
            
            
            
            var link     = $(e.relatedTarget),
            id = link.data("id"),
            orderId = link.data("order"),
            tracking = link.data("tracking"),
            carrier = link.data("carrier"),
            reason = link.data("reason")
            ;
            
            $('#sellOrderTbx').val(orderId);
            $('#trackingTbx').val(tracking);
            $('#carrierTbx').val(carrier);
            $('#reasonTbx').val(reason);
            $('#catId').val(id); 
            $('#sellOrderTbx').attr('readonly',true);

            $('#addTitle').hide();
            $('#modal-que-save').hide();  
            $('#editTitle').show();
            $('#modal-que-edit').show();  
            $('#editSuccess').hide();
            $('#addSuccess').hide();    
            $(".print-error-msg").hide();
            $('#orderissue').hide();                      
    
    });

  

    
        $('#modal-que-save').on('click',function(event){                       
            
            var sellOrder = $('#sellOrderTbx').val();
            var tracking = $('#trackingTbx').val();        
            var carrier = $('#carrierTbx option:selected').val();
            var reason = $('#reasonTbx option:selected').val();
            
            $.ajax({                
            type: 'post',
            url: '/autofulfillAddreturn',
            data: {
            'sellOrder': sellOrder,
            'tracking':tracking,
            'carrier' : carrier,
            'reason':reason            
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
            console.log(data);
            if (data == 'success') {
                $('#add-modal').modal('hide');
                $('#error').hide();
                $('#orderissue').hide();
                document.location.reload();
                $("#addSuccess").show().delay(3000).fadeOut();
            } 
            else  if (data == 'failure') {
                $('#orderissue').show();
            }
            else
                printErrorMsg(data.error);
            },
            
            error: function(XMLHttpRequest, textStatus, errorThrown) {                
                $('#error').show();
            }        
        });
        })

        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }

        $('#modal-que-edit').on('click',function(event){                       
            var pid = $('#catId').val();
            var sellOrder = $('#sellOrderTbx').val();
            var tracking = $('#trackingTbx').val();        
            var carrier = $('#carrierTbx option:selected').val();
            var reason = $('#reasonTbx option:selected').val();




            $.ajax({
                
            type: 'post',
            url: '/autofulfillEditreturn',
            data: {
            'id':pid,
            'sellOrder': sellOrder,
            'tracking':tracking,
            'carrier' : carrier,
            'reason':reason     
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
                printErrorMsg(data.error);
            },
            
            error: function(XMLHttpRequest, textStatus, errorThrown) {                
                $('#error').show();
            }        
        });
        })
    
       

    
    });


</script>
    <div class="container-fluid mt--7">
        @if(Session::has('error_msg'))
        <div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('error_msg')}}</div>
        @endif
        @if(Session::has('success_msg'))
        <div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('success_msg')}}</div>
        @endif
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center" style="padding-bottom:10px!important;">
                            <div class="col-4">
                                <h3 class="mb-0">{{ __('SaleFreaks1 - Waiting For Return') }}</h3>
                            </div>
                            <div class="col-8" style="float:right; ">
                            <form class="form-inline" action="/returnsupload" method="post" enctype="multipart/form-data" style="float:right;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file" />                
                            
                                    <input type="submit" class="btn btn-primary" value="Import" style="margin-left:10px;"/>
                                    <input type="button" id="btnAddCat" class="btn btn-primary" value="Add Return"/>      
                                    @if(!empty($search) && $search==1)
                                        <a href="{{ route($route) }}"class="btn btn-primary btn-md"  style="float:right;">Go Back</a>
                                    @endif                               
                                </div>
                            
                            </form>
                            
                            </div> 
                              
                        </div>
                    </div>
                    
                    

                    <div class="row" style="margin-left:0px!important;">
                        <div class="col-12 text-center" id="filters">
                        <form action="autofulfillReturnFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <div style="width:100%; padding-bottom:2%;">
                                <div class="form-group">
                                    
                                <div style="padding-right:1%;">
                                <select class="form-control" name="statusFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Status</option>
                                    <option value="1" {{ isset($statusFilter) && $statusFilter=="1"?"selected":"" }}>New</option>
                                    <option value="2" {{ isset($statusFilter) && $statusFilter=="2"?"selected":"" }}>Returned</option>
                                    <option value="3" {{ isset($statusFilter) && $statusFilter=="3"?"selected":"" }}>Refunded</option>                                                                        
                                </select>
                                </div>

                                <div style="padding-right:1%;">
                                <select class="form-control" name="labelFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Label</option>
                                    <option value="1" {{ isset($labelFilter) && $labelFilter=="1"?"selected":"" }}>Yes</option>
                                    <option value="2" {{ isset($labelFilter) && $labelFilter=="2"?"selected":"" }}>No</option>
                                </select>
                                </div>

                                <div style="padding-right:1%;">
                                <select class="form-control" name="storeFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Store Name</option>
                                    @foreach($stores as $store)
                                    <option value="{{$store->id}}" {{ isset($storeFilter) && $storeFilter==$store->id?"selected":"" }}>{{$store->store}}</option>
                                    @endforeach
                                    
                                    
                                </select>
                                </div>

                                <div style="padding-right:1%;">
                                <select class="form-control" name="accountFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Select Account</option>
                                    @foreach($accounts as $account)
                                    <option value="{{$account->id}}" {{ isset($accountFilter) && $accountFilter==$account->id?"selected":"" }}>{{$account->email}}</option>
                                    @endforeach
                                    
                                    
                                </select>
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

                    <!-- End Filters Section -->
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="prodth">{{ __('Date') }}</th>
                                    
                                    <th scope="col" class="prodth">{{ __('Buyer Name') }}</th>
                                    <th scope="col" class="prodth">{{ __('Sell Order Number') }}</th>
                                    <th scope="col" class="prodth">{{ __('Sell Amount') }}</th>
                                    <th scope="col" class="prodth">{{ __('Purchase Order Number') }}</th>  
                                    <th scope="col" class="prodth">{{ __('Store Name') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Account') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Purchase Amount') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Purchase Source') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Return Reason') }}</th>                                        
                                    <th scope="col" class="prodth">{{ __('Carrier') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Tracking Number') }}</th>  
                                    <th scope="col" class="prodth">{{ __('Label') }}</th>
                                    
                                    <th scope="col" class="prodth">{{ __('Return') }}</th>                                                                         
                                                                                                    
                                    <th scope="col" class="prodth"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returns as $return)
                                    <tr>
                                        <td width="10%">{{ $provider::getIranTime(date_format(date_create($return->created_at), 'm/d/Y H:i:s')) }}</td>                                        
                                        <td class="prodtd">{{ $return->buyerName }}</td>                                        
                                        <td class="prodtd"><a href="orderDetails/{{$return->order_id}}" target='_blank'>{{ $return->sellOrderId }}</a></td>
                                        <td class="prodtd">{{number_format((float)$return->totalAmount , 2, '.', '')}}</td>
                                        <td class="prodtd">{{ $return->poNumber }}</td>
                                        <td class="prodtd">{{ $return->storeName }}</td>
                                        <td class="prodtd">
                                        @foreach($accounts as $account)
                                            @if($account->id == $return->account_id)
                                                {{$account->email}}
                                            @endif 
                                        @endforeach    
                                        @if(!is_numeric($return->account_id))
                                            {{$return->account_id}}
                                        @endif                                    
                                        </td>
                                        <td class="prodtd">{{number_format((float)$return->poTotalAmount , 2, '.', '')}}</td>

                                      
                                        <td class="prodtd">{{ $return->source }}</td>
                                        @if($return->reason==1)
                                            <td class="prodtd">Damaged</td>
                                        @elseif($return->reason==2)
                                            <td class="prodtd">No Longer Wanted</td>
                                        @elseif($return->reason==3)
                                            <td class="prodtd">Incorrect Item</td>
                                        @elseif($return->reason==4)
                                            <td class="prodtd">Not As Described</td>
                                        @else
                                            <td class="prodtd"></td>
                                        @endif

                                       
                                        @if($return->carrier==1)
                                            <td class="prodtd">USPS</td>
                                        @elseif($return->carrier==2)
                                            <td class="prodtd">UPS</td>
                                        @elseif($return->carrier==3)
                                            <td class="prodtd">Fedex</td>
                                        @elseif($return->carrier==4)
                                            <td class="prodtd">Amazon Dropoff</td>
                                        @else
                                            <td class="prodtd"></td>
                                        @endif

                                        <td class="prodtd">{{$return->trackingNumber}}</td>                                    
                                        <td class="prodtd">                                           
                                           @if(empty($return->label))
                                            <a class="btn btn-primary btn-sm"  data-toggle="modal" data-target="#addLabel" data-id="{{$return->id}}" href="#">{{ __('Label') }}</a>
                                            @else
                                            <button class="btn btn-primary btn-sm" disabled>{{ __('Label') }}</button>
                                            @endif
                                        </td>                                        
                                        
                                        @if($return->status!='refunded' && $return->status!='returned')
                                        <td class="prodtd"><a  href="./autofulfillUpdateStatus?status=1&id={{$return->id}}"  class="btn btn-primary btn-sm">Return</a></td>
                                        @else                                    
                                            <td class="prodtd">Returned</td>    
                                        @endif
                                                                                
                                  
                                        <td class="text-right prodtd">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        @if(empty($route))                               
                                                        <form action="{{ route('saleFreaks1DeleteReturn', $return->id) }}" method="post">
                                                        @else
                                                        <form action="/saleFreaks1DeleteReturn/{{$route}}/{{$return->id}}" method="post">
                                                        @endif
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-id="{{$return->id}}" data-tracking="{{$return->trackingNumber}}"  data-carrier="{{$return->carrier}}" data-reason="{{$return->reason}}" data-order="{{$return->sellOrderId}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this return?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Delete') }}
                                                            </button>

                                                            <a class="dropdown-item labelPrint" href="/autofulfillLabelPrint/{{$return->id}}">{{ __('Print Label') }}</a>

                                                            @if(empty($route))
                                                            <a class="dropdown-item" href="/saleFreaks1LabelDelete/{{$return->id}}">{{ __('Delete Label') }}</a>
                                                            @else
                                                            <a class="dropdown-item" href="/saleFreaks1LabelDelete/{{$route}}/{{$return->id}}">{{ __('Delete Label') }}</a>
                                                            @endif

                                                            @endif
                                                        </form>                                                       
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
                        <span>Showing {{$returns->toArray()['from']}} - {{$returns->toArray()['to']}} of {{$returns->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$returns->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            
<!-- Add Question Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="addCat">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
      @lang('Some fields are incorrect or missing below:')
       </div>
       <div class="alert alert-danger" id="orderissue" style="display:none">
      @lang('Order doesnot exist with this seller ID')
       </div>
       <div class="alert alert-success" id="addSuccess" style="display:none">
               @lang('Return Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Return Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add New Return')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Return')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />

            <div class="alert alert-danger print-error-msg" style="display:none">
                <ul></ul>
            </div>
   <form class="form-horizontal" method="post" >
{{csrf_field()}}

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Sell Order Number:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="email" class="form-control" id="sellOrderTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Return Reason:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="reasonTbx" style="">                                
                                                        <option value=0>Select Reason</option>
                                                        <option value=1>Damaged</option>
                                                        <option value=2>No Longer Wanted</option>
                                                        <option value=3>Incorrect Item</option>
                                                        <option value=4>Not As Described</option>                                                    
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Carrier:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="carrierTbx" style="">                                
                                                        <option value=0>Select Carrier</option>
                                                        <option value=1>USPS</option>
                                                        <option value=2>UPS</option>
                                                        <option value=3>Fedex</option>
                                                        <option value=4>Amazon Dropoff</option>                                                    
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Tracking Number:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="trackingTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>









       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Return')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Return')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>

<!-- Add Question Modal End -->

<div class="modal fade" tabindex="-1" role="dialog" id="addLabel">     
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
      @lang('Some fields are incorrect or missing below:')
       </div>
       <div class="alert alert-success" id="addSuccess" style="display:none">
               @lang('Carrier Added Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Upload Label')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            
{{csrf_field()}}
<form class="form-inline" action="/uploadLabel" method="post" enctype="multipart/form-data" style="padding-left:16%;">
        <input type="hidden" value="" name="id" id="labelId" />
<input type="hidden" value="{{empty($route)?'':$route}}" name="route" id="route" />
        {{ csrf_field() }}
        <div class="form-group">
            <input type="file" class="form-control" name="file" />                
        </div>


       <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="modal-que-save">@lang('Upload Label')</button>        
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
        </div>                     
</form>
      </div>
       
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
        @include('layouts.footers.auth')
    </div>
@endsection