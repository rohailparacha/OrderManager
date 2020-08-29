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
<script>
$(document).ready(function(){
    $('tr').on('shown.bs.collapse', function(){
    $(this).prev('tr').find(".fa-plus-square").removeClass("fa-plus-square").addClass("fa-minus-square");
  }).on('hidden.bs.collapse', function(){
    $(this).prev('tr').find(".fa-minus-square").removeClass("fa-minus-square").addClass("fa-plus-square");
  });
  
    $("#logsTable").on('click','tr',function(e) {
        var row = $(this).attr('data-target').replace('#','').split('-')[1];
        var id = $(this).attr('data-target');
        $.ajaxSetup({

        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });
        $.ajax({
            url: './getLogs',
            method: 'post',
            data: {id:row},
            success: function(result) {
                var jsondata = $.parseJSON(result);
                html = '<table class="table table-flush">';
                for (a=0; a<jsondata.length;a++)
                {
                    html+= '<tr><td width="5%"></td><td style="width:11%">'+jsondata[a].name+'</td>';
                    html+='<td style="width:11%">'+jsondata[a].date_started+'</td>';
                    html+='<td style="width:11%">'+jsondata[a].date_completed+'</td>';
                    html+='<td style="width:9%">'+jsondata[a].totalItems+'</td>';
                    html+='<td style="width:9%">'+jsondata[a].errorItems+'</td>';
                    html+='<td style="width:9%">'+jsondata[a].successItems+'</td>';
                    html+='<td style="width:9%">'+jsondata[a].stage+'</td>';
                    html+='<td style="width:9%">'+jsondata[a].status+'</td>';
                    html+='<td style="width:24%">'+jsondata[a].error+'</td>';
                    html+='</tr>';
                }
                
                html+='</table>'
                $('div'+id).html(html);
            }});
        
    });
});
</script>

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
                        <table class="table align-items-center table-flush" id="logsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="3%"></th>
                                    <th scope="col" width="11%">{{ __('Batch Name') }}</th>
                                    <th scope="col" width="11%">{{ __('Start Time') }}</th>
                                    <th scope="col" width="11%">{{ __('Finished Time') }}</th>
                                    <th scope="col" width="9%">{{ __('Total Identifiers') }}</th>
                                    <th scope="col" width="9%">{{ __('Total Errors') }}</th>
                                    <th scope="col" width="9%">{{ __('Total Successful') }}</th>
                                    <th scope="col" width="9%">{{ __('Stage') }}</th>
                                    <th scope="col" width="9%">{{ __('Status') }}</th>
                                    <th scope="col" width="6%">{{ __('Action') }}</th> 
                                    <th scope="col" width="12%">{{ __('Error') }}</th> 
                                    <th scope="col" width="6%">{{ __('SC Account') }}</th> 
                                                                      
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr data-toggle="collapse" data-target= "#logs-{{$log->id}}" class="accordion-toggle">                              
                                        <td style="text-align:center; font-size: 15px;"><i class="fa fa-plus-square"></i></td>
                                        <td>Main Process</td>                 
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
                                        @if($log->cnt>0)
                                        <td width="10%">In Progress</td>
                                        @else
                                        <td width="10%">{{ $log->status }}</td>
                                        @endif
                                        
                                        <td width="10%">{{ $log->action }}</td>
                                        <td width="12%">{{ $log->error }}</td>     
                                        <td width="6%">{{ $log->scaccount }}</td>      
    
                                    </tr>

                                    <tr>
                                        <td colspan="11" class="hiddenRow">
                                            <div class="accordian-body collapse" id="logs-{{$log->id}}">
                                            
                                            </div> </td>
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