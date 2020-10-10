@extends('layouts.app', ['title' => __('Report')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
                                <h3 class="mb-0">Duplicate Record</h3>
                                 
                            </div> 
                           
                            <div class="col-6" style="text-align:right;">
                               
                            
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

                    <div class="row" style="margin-left:0px!important;">
                        <div class="col-12 text-center" id="filters">
                            <form action="search-filter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                                @csrf
                                <div style="width:100%; padding-bottom:1%;">
                                    <div class="form-group">
                                        <div style="padding-right:1%;">
                                            <input type="text" class="form-control" name="poNumber" id="poNumber" placeholder="Purchase Order No.">
                                        </div>

                                        <div>
                                        
                                        <input type="submit" value="Filter" class="btn btn-primary btn-md">
                                      
                                        </div>

                                             
                                    </div>
                                </div>
                                
                                

                            </form>   
                                      
                            
                        </div>
                    </div>


                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Marketplace') }}</th>
                                    <th scope="col">{{ __('Store Name') }}</th>
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Sell Total') }}</th>
                                    <th scope="col">{{ __('Purchase Account') }}</th>
                                    <th scope="col">{{ __('Purchase Order ID') }}</th>
                                    <th scope="col">{{ __('Purchase Total') }}</th>
                                    <th scope="col">{{ __('Carrier Name') }}</th>
                                    <th scope="col">{{ __('Tracking Number') }}</th>
                                    <th scope="col">{{ __('Status') }}</th>
                                    <th scope="col">{{ __('View') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                              @if($orders->count() >0)
                              @foreach ($orders as $order)

                                   
                                   
                                    <tr>
                                        
                                        <td>{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                                        <td>{{ $order->marketplace }}</td>
                                        <td>{{ $order->storeName }}</td>
                                        <td>{{ $order->buyerName }}</td>
                                        <td>{{ $order->sellOrderId }}</td>                                                                                                                        
                                        <td>{{ number_format((float)$order->totalAmount +(float)$order->shippingPrice , 2, '.', '') }}</td>
                                        <td>
                                        @foreach($accounts as $account)
                                            @if($account->id == $order->account_id)
                                                {{$account->email}}
                                            @endif 
                                        @endforeach
                                        @if(!is_numeric($order->account_id))
                                        {{$order->account_id}}
                                        @endif
                                        </td>
                                        <td><a target="_blank" href="https://www.amazon.com/progress-tracker/package/ref=ppx_yo_dt_b_track_package?_encoding=UTF8&itemId=klpjsskrrrpoqn&orderId={{$order->poNumber}}">{{ $order->poNumber }}</a></td>  
                                        <td>{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>
                                        @if(!empty($order->carrierName))
                                        <td>{{ $carrierArr[$order->carrierName] }}</td>
                                        @else
                                        <td>{{ $order->carrierName }}</td>
                                        @endif
                                        @if(empty($order->upsTrackingNumber))
                                        <td>{{ $order->trackingNumber }}</td>
                                        @else
                                        <td>{{ $order->upsTrackingNumber }}</td>
                                        @endif
                                        <td>{{ $order->status }}</td>
                                        <td><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
                                    </tr>
                                  
                                    @endforeach
                                    @endif
                                   
                            </tbody>
                        </table>
                    </div>
                     @if($orders->count() >0)
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
                    @endif
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection