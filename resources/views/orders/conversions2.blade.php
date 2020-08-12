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
                                <h3 class="mb-0">{{ __('BCE Conversions') }}</h3>                                
                            </div>  
                            <div class="col-6" style="text-align:right;">
                            <a href="conversionssync" class="btn btn-primary btn-md">Sync</a>                            
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

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Date') }}</th>                                    
                                    <th scope="col">{{ __('Store Name') }}</th>                                    
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Purchase Order Id') }}</th>                                    
                                    <th scope="col">{{ __('City') }}</th>
                                    <th scope="col">{{ __('State') }}</th>
                                    <th scope="col">{{ __('Zip Code') }}</th>
                                    <th scope="col">{{ __('Old Tracking Number') }}</th>
                                    <th scope="col">{{ __('BCE Tracking Number') }}</th>
                                    <th scope="col">{{ __('UPS Tracking Number') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>                                        
                                        <td>{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td>{{ $order->storeName }}</td>
                                        <td>{{ $order->buyerName }}</td>
                                        <td><a target="_blank" href="orderDetails/{{$order->id}}">{{ $order->sellOrderId }}</a></td>
                                        <td>{{ $order->poNumber }}</td>
                                        <td>{{ $order->city }}</td>
                                        <td>{{ $order->state }}</td>
                                        <td>{{ $order->postalCode }}</td>
                                        <td>{{ $order->trackingNumber }}</td>
                                        <td><a target="_blank" href="https://bluecare.express/Tracking?trackingReference={{ $order->newTrackingNumber }}">{{ $order->newTrackingNumber }}</a></td>

                                        <td>{{ $order->upsTrackingNumber }}</td>
                                        @if($order->status!='shipped')
                                        <td><button name="shipBtn" id="ship{{$loop->iteration}}" data-id= {{$order->id}} data-track= {{$order->newTrackingNumber}} data-carrier= "Bluecare Express" class="btn btn-primary btn-sm">Ship</button></td>
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