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
    $("button[name = 'shipBtn']").on('click',function(event){ 
    
    var btnId = '#'+this.id;
    var id = $(btnId).attr('data-id');
    var carrier = $(btnId).attr('data-carrier');
    var tracking = $(btnId).attr('data-track');
    
    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'carrier': carrier,
               'id' : id,
               'tracking' : tracking,
               'status': 'new',
               'type':'ship',
               'source':'BCE'
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
                            <h3 class="mb-0">{{ __('Cindy - Cancel Pending') }}</h3>                             
                            </div>  
                            <div class="col-6" style="text-align:right;">
                            <a href="orderCancelledExport" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px;">Export</a>       
                            
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
                                    <th scope="col">Date</th>
                                    <th scope="col">{{ __('Purchase Order Id') }}</th>                                   

                                    <th scope="col">{{ __('Status') }}</th>
                                    
                                    <th scope="col">{{ __('View') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        <td>  
                                        {{ $provider::getIranTime(date_format(date_create($order->ordercreatedate), 'm/d/Y H:i:s')) }}
                                        </td>                                      
                                        <td>{{ $order->afpoNumber }}</td>
                                        <td>{{$order->orderStatus}}</td>
                                        
                                        <td><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a>
                                        <a target="_blank" href="https://www.amazon.com/progress-tracker/package/ref=pe_2640190_232586610_TE_typ?_encoding=UTF8&from=gp&orderId={{$order->poNumber}}&packageIndex=0&itemId={{$order->itemId}}" class="btn btn-primary btn-sm">Shipping Link</a>
                                        </td>
                                        <td class="text-right prodtd">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                                                                            
                                                        <form action="{{ route('deleteCancelled', $order->cancelledId) }}" method="post">
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
@endsection