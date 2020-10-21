@extends('layouts.app', ['title' => __('Cancelled Orders')])

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

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Orders') }}</h3>
                            </div>       
                            <div class="col-6" style="text-align:right;">
                                Showing {{$order_details->toArray()['from']}} - {{$order_details->toArray()['to']}} of {{$order_details->toArray()['total']}} records
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
                                    <th scope="col">{{ __('Marketplace') }}</th>
                                    <th scope="col">{{ __('Account') }}</th>
                                    <th scope="col">{{ __('Store Name') }}</th>
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Purchase Order Id') }}</th>
                                    <th scope="col">{{ __('Purchase Total') }}</th>
                                    <th scope="col">{{ __('Carrier Name') }}</th>
                                    <th scope="col">{{ __('Tracking Number') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('View') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if($order_details->count())
                                    @foreach ($order_details as $detail)
                                        @if($detail->order != null)
                                            <tr>
                                                <td>{{ $provider::getIranTime(date_format(date_create($detail->order->date), 'm/d/Y H:i:s')) }}</td>
                                                <td>{{ $detail->order->marketplace }}</td>
                                                <td>{{ $detail->order->storeName }}</td>
                                                <td>{{ $detail->asin->account }}</td>
                                                <td>{{ $detail->order->buyerName }}</td>
                                                <td>{{ $detail->order->sellOrderId }}</td>
                                                <td>{{ $detail->order->poNumber }}</td>
                                                <td>{{ number_format((float)$detail->order->poTotalAmount, 2, '.', '') }}</td>
                                                <td>
                                                    @if(!is_null($detail->order->carrierName))
                                                        {{ $carrierArr[$detail->order->carrierName] }}
                                                    @endif
                                                </td>
                                                <td>{{ $detail->order->trackingNumber }}</td>
                                                <td>{{ $detail->order->status }}</td>
                                                <td><a href="{{ route('orderDetails', ['id'=> $detail->order_id]) }}" class="btn btn-primary btn-sm">Details</a></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif



                            </tbody>
                        </table>
                    </div>
                    <div class="row" style="padding-right:2%">
                    <div class="col-md-4 offset-md-8" style="text-align:right">
                    <span>Showing {{$order_details->toArray()['from']}} - {{$order_details->toArray()['to']}} of {{$order_details->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$order_details->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection