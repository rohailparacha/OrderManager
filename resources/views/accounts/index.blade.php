@extends('layouts.app', ['title' => __('Marketplace Accounts Management')])

@section('content')
    @include('layouts.headers.cards')

    <div class="container-fluid mt--7">
    @if(Session::has('error_msg'))
        <div class="alert alert-danger alert-dismissable"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('error_msg')}}</div>
        @endif
        @if(Session::has('success_msg'))
        <div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('success_msg')}}</div>
        @endif
        <div class="row">
        
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Marketplace Accounts') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('createaccount') }}" class="btn btn-sm btn-primary">{{ __('Add account') }}</a>
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
                                    <th scope="col">{{ __('Store Name') }}</th>
                                    <th scope="col">{{ __('Username') }}</th>
                                    <th scope="col">{{ __('Password') }}</th>
                                    <th scope="col">{{ __('Lag Time') }}</th>
                                    <th scope="col">{{ __('SC Account') }}</th>
                                    <th scope="col">{{ __('Manager') }}</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td>{{ $account->store }}</td>
                                        <td>{{ $account->username }}</td>
                                        <td>{{ $account->password }}</td>
                                        <td>{{ $account->lagTime }}</td>
                                        <td>{{ $account->name }}</td>                                    
                                        <td>{{ $account->manager }}</td>                                        
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                                    
                                                        <form action="{{ route('destroyaccount', $account) }}" method="post">
                                                            @csrf                                                            
                                                            
                                                            <a class="dropdown-item" href="/account/{{$account->id}}/edit">{{ __('Edit') }}</a>
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this account?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Delete') }}
                                                            </button>
                                                        </form>    
                                                  
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $accounts->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection