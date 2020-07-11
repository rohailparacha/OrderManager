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
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Process Logs') }}</h3>
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
                                    <th scope="col" width="12%">{{ __('Start Time') }}</th>
                                    <th scope="col" width="12%">{{ __('Finished Time') }}</th>
                                    <th scope="col" width="10%">{{ __('Total Identifiers') }}</th>
                                    <th scope="col" width="10%">{{ __('Total Errors') }}</th>
                                    <th scope="col" width="10%">{{ __('Total Successful') }}</th>
                                    <th scope="col" width="10%">{{ __('Stage') }}</th>
                                    <th scope="col" width="10%">{{ __('Status') }}</th>
                                    <th scope="col" width="26%">{{ __('Error') }}</th>                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>                                                                         
                                        <td width="12%">{{ $provider::getIranTime(date_format(date_create($log->date_started), 'm/d/Y H:i:s')) }}</td>                                                                               
                                        <td width="12%">
                                        @if(!empty($log->date_completed))
                                            {{ $provider::getIranTime(date_format(date_create($log->date_completed), 'm/d/Y H:i:s')) }}
                                        @endif
                                        </td>                                                                                                               
                                        <td width="10%">{{ $log->identifiers }}</td>
                                        <td width="10%">{{ $log->errorItems }}</td>
                                        <td width="10%">{{ $log->successItems }}</td>
                                        <td width="10%">{{ $log->stage }}</td>
                                        <td width="10%">{{ $log->status }}</td>
                                        <td width="26%">{{ $log->error }}</td>                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                  

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$logs->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            
        @include('layouts.footers.auth')
    </div>
@endsection