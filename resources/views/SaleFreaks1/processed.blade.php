@extends('layouts.app', ['title' => __('Processed Orders')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
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
                                <h3 class="mb-0">{{ __('SaleFreaks1 - Processed Orders') }}</h3>
                            </div>                            
                            
                            <div class="col-6" style="text-align:right; float:right;">
                            @if(!empty($search) && $search==1)
                                <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
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

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th width="10%" scope="col">{{ __('Date') }}</th>
                                    <th width="10%" scope="col">{{ __('Marketplace') }}</th>
                                    <th width="10%" scope="col">{{ __('Store Name') }}</th>
                                    <th width="10%" scope="col">{{ __('Sell Order Id') }}</th>
                                    <th width="10%" scope="col">{{ __('Purchase Order Id') }}</th>
                                    <th width="10%" scope="col">{{ __('Buyer Name') }}</th>
                                    <th width="7%" scope="col">{{ __('Qty') }}</th>
                                    <th width="9%" scope="col">{{ __('Sell Total Amount') }}</th>
                                    <th width="9%" scope="col">{{ __('Purchase Total Amount') }}</th>
                                    <th width="9%" scope="col">{{ __('Cancel/BCE') }}</th>
                                    <th width="9%" scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>                                                                               
                                        <td width="10%">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td width="10%">{{ $order->marketplace }}</td>
                                        <td width="10%">{{ $order->storeName }}</td>
                                        <td width="10%">{{ $order->sellOrderId }}</td>
                                        <td width="10%">{{ $order->poNumber }}</td>
                                        <td width="10%">{{ $order->buyerName }}</td>
                                        <td width="7%">{{ $order->quantity }}</td>
                                        <td width="9%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td width="9%">{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>
                                        <td width="9%">{{ $order->bce}}  {{ $order->cancel}}</td>
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