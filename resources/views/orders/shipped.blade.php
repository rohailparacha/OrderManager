@extends('layouts.app', ['title' => __('Shipped Orders')])

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
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Shipped Orders') }}</h3>
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
                                        <td width="8%">{{ $order->quantity }}</td>
                                        <td width="8%">{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td width="8%">{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>                                
                                        
                                        <td width="10%">
                                        @if(empty($order->newTrackingNumber))
                                            {{$order->trackingNumber}}
                                        @else
                                            {{$order->newTrackingNumber}}
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