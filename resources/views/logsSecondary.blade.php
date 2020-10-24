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
                                <h3 class="mb-0">{{ __('New Process Logs') }}</h3>
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
                        <table class="table align-items-center table-flush" id="logsTable">
                            <thead class="thead-light">
                                <tr>                                    
                                    <th scope="col" width="15%">{{ __('Start Date') }}</th>
                                    <th scope="col" width="15%">{{ __('End Date') }}</th>
                                    <th scope="col" width="15%">{{ __('Original File') }}</th>
                                    <th scope="col" width="15%">{{ __('Final File') }}</th>
                                    <th scope="col" width="15%">{{ __('Duplicate ASINs File') }}</th>
                                    <th scope="col" width="15%">{{ __('Type') }}</th>     
                                    <th scope="col" width="15%">{{ __('Status') }}</th>                                                                                                      
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>                              
                                        <td width="10%">{{ $log->date_started }}</td>
                                        <td width="10%">{{ $log->date_completed }}</td>
                                        <td width="10%"><a href={{ $log->upload_link }}>Download</a></td>
                                        <td width="10%">
                                        @if(!empty($log->export_link))
                                        <a href={{ $log->export_link }}>Download</a>                                        
                                        @endif
                                        </td>
                                        <td width="10%">
                                        @if(!empty($log->dup_link))
                                        <a href={{ $log->dup_link }}>Download</a>                                        
                                        @endif
                                        </td>
                                        
                                        <td width="10%">{{ $log->action }}</td> 
                                        <td width="10%">{{ $log->status }}</td>                                           
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