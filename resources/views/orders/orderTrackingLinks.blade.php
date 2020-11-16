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
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Order Tracking Links') }}</h3>
                            </div>  
                            
                            <div class="col-6" style="text-align:right;">
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
                                    <th scope="col" width="10%">{{ __('Date') }}</th>
                                    <th scope="col" width="10%">{{ __('Marketplace') }}</th>
                                    <th scope="col" width="10%">{{ __('Store Name') }}</th>
                                    <th scope="col" width="10%">{{ __('Account') }}</th>
                                    <th scope="col" width="12%">{{ __('Sell Order Id') }}</th>
                                    <th scope="col" width="12%">{{ __('Purchase Order Id') }}</th>
                                    <th scope="col" width="10%">{{ __('Total PO Amount') }}</th>
                                    <th scope="col" width="10%">{{ __('Buyer Name') }}</th>                                                                        
                                    <th scope="col" width="10%">{{ __('Qty') }}</th>                                   
                                    
                                     <th scope="col" width="10%">{{ __('Tracking Link') }}</th>                                                                      
                                    <th scope="col" width="10%">{{ __('Action') }}</th>                                                                        
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                    
                                        <td width="9%">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>                                                                                
                                        <td width="9%">{{ $order->marketplace }}</td>
                                        <td width="9%">{{ $order->storeName }}</td>
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
                                        <td width="9%">{{ $order->sellOrderId }}</td>
                                        <td width="9%">{{ $order->poNumber }}</td>
                                        <td width="10%">
                                        
                                        {{number_format((float)$order->poTotalAmount , 2, '.', '')}}</td>
                                        
                                        <td width="9%">{{ $order->buyerName }}</td>                                                                               
                                        <td width="8%">{{ $order->quantity }}</td>
                                         
                                        <td><a target="_blank" href="{{$order->tlLink}}">Tracking Link</a></td>
                                        
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