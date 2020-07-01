@extends('layouts.app', ['title' => __('User Management')])

@section('content')
    @include('layouts.headers.cards')

    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Keepa Response') }}</h3>
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

                    <div class="row" style="padding-left:5%;">
                    <strong>Tokens Left:&nbsp;</strong>{{empty($tokensLeft)?0:$tokensLeft}}
                    </div>
                    <div class="row" style="padding-left:5%;padding-top:2%;">
                    <strong>Tokens Consumed:&nbsp;</strong>{{empty($tokensConsumed)?0:$tokensConsumed}}
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">{{ __('Seller Id') }}</th>
                                    <th scope="col">{{ __('Lowest Price') }}</th>
                                    <th scope="col">{{ __('Last Seen') }}</th>                                    
                                  
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($offers as $offer)
                                    <tr>
                                        <td>{{ $offer->sellerId }}</td>
                                        <td>${{ $offer->price/100 }}</td>
                                        <td>{{ $offer->lastSeen }}</td>
                                    
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>                    
                    
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection