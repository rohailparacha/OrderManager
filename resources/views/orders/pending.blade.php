@extends('layouts.app', ['title' => __('Shipped Orders')])

@section('content')
@include('layouts.headers.cards')

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Pending Orders') }}</h3>
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
                                    <th scope="col">{{ __('Store Name') }}</th>
                                    <th scope="col">{{ __('Sell Order Id') }}</th>
                                    <th scope="col">{{ __('Buyer Name') }}</th>
                                    <th scope="col">{{ __('Quantity') }}</th>
                                    <th scope="col">{{ __('Total Amount') }}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr>
                                        
                                        <td>{{ date_format(date_create($order->date), 'm/d/Y H:i:s') }}</td>
                                        <td>{{ $order->marketplace }}</td>
                                        <td>{{ $order->storeName }}</td>
                                        <td>{{ $order->sellOrderId }}</td>
                                        <td>{{ $order->buyerName }}</td>
                                        <td>{{ $order->quantity }}</td>
                                        <td>{{ $order->totalAmount }}</td>
                                        <td><a href="orderDetails/{{$order->id}}" class="btn btn-primary btn-sm">Details</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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