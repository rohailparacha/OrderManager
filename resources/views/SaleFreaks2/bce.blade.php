@extends('layouts.app', ['title' => __('Order Fulfillment BCE Conversions')])

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

<script>
$(document).ready(function(){

    $('.btnProcess').on('click',function(event){
    var id = $(this).attr("data-id");
    $('#catId').val(id);
    $('#error').hide();     
    $('#modal-edit').show(); 
    $('#process').modal('show');  
    $('#emptyPO').hide(); 
 });
    
 $('#modal-edit').on('click',function(event){ 
    
    var bce = $('#bceTbx').val();
    var id = $('#catId').val();  
    
    if(bce.trim()=='')
    {        
        $('#emptyPO').show(); 
            
        
        


        return;
    }

    else
        {
            $('#emptyPO').hide(); 
        }

    $.ajax({               
               type: 'post',
               url: '/saleFreaks2UpdateBCE',
               data: {
               'bce': bce.trim(),
               'id' : id.trim(),              
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#error2').hide();  
                   document.location.reload();                       
               } 
               else
               {                                
                $('#error2').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#error2').show();
               }        
           });


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
                                <h3 class="mb-0">{{ __('SaleFreaks2 - BCE Pending') }}</h3>                                
                            </div>  
                            <div class="col-6" style="text-align:right;">
                            <a href="saleFreaks2OrderFulfillmentExport" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px;">Export</a>       
                            
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

                    <div class="card-header border-0" style="padding-top:0px;">
                        <div class="row align-items-center">
                            <div class="col-8">
                            </div>

                            <div class="col-4" style="text-align:right;"> 
                            Showing {{$orders->toArray()['from']}} - {{$orders->toArray()['to']}} of {{$orders->toArray()['total']}} records                        
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Purchase Order Id') }}</th>
                                    <th scope="col">{{ __('Old Tracking Number') }}</th>
                                    <th scope="col">{{ __('View') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>                    
                                        <td>{{ empty($order->of_bce_created_at)?"":$provider::getIranTime(date_format(date_create($order->of_bce_created_at), 'm/d/Y H:i:s')) }}</td>                                                          
                                        <td>{{ $order->afpoNumber }}</td>
                                        <td>{{ $order->trackingNumber }}</td>
                                        <td><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a>
                                        <a target="_blank" href="https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&orderId={{$order->poNumber}}&packageIndex=0&itemId={{$order->itemId}}" class="btn btn-primary btn-sm">Shipping Link</a>
                                        </td>

                                        <td class="text-right prodtd">                                        
                                        <button class="btnProcess btn btn-primary btn-sm" style="float:left;" data-id="{{$order->id}}"> Add BCE Tracking</button>
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                                                                            
                                                        <form action="{{ route('saleFreaks2DeleteConversion', $order->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                           
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this orders?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Delete') }}
                                                            </button>
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

    <!-- Process Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="process">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error2" style="display:none">
            Error while processing order. Please check the inputs below: 
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add BCE Tracking:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
            
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('BCE Tracking Number:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="bceTbx" name="category" >                                        
                   </div>
                    <div id="emptyPO" style="color:red; display:none;">BCE Tracking Number cannot be empty</div>
               </div>
           </div>
           
       </div>
</div>
<br/>
       
   </form>
      </div>
       <div class="modal-footer">
        
        <button type="button" style="display:none" class="btn btn-primary" id="modal-edit">@lang('Update')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Process Modal -->
@endsection