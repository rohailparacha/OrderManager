@extends('layouts.app', ['title' => __('Accounting')])

@section('content')

@include('layouts.headers.cards')

@inject('provider', 'App\Http\Controllers\orderController')

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
    <script>
$(document).ready(function(){
        $(function() {
    $('input[name="dateRange"]').daterangepicker({
        opens: 'left'
    }, function(start, end, label) {
        console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    });
    });
    

    $("#check-all").click(function(){
    $('input:checkbox').not(this).prop('checked', this.checked);
    
});

    $('#addCat').on('show.bs.modal', function(e) {    
            var link     = $(e.relatedTarget),
            id = link.data("id");
            var data;
            console.log(id);


            $.ajax({
                
                type: 'get',
                url: '/getTransaction/'+id,
                data: {
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $('#error').hide();            
                    var obj = JSON.parse(data);
                    console.log(data);
                    $('#catId').val(obj.id);            
                    $('#dateTbx').val(obj.date);            
                    $('#descTbx').val(obj.description);
                    $('#debitTbx').val(obj.debitAmount);
                    $('#creditTbx').val(obj.creditAmount);
                    $('#banksTbx').val(obj.bank_id);
                    $('#catTbx').val(obj.category_id); 
                    $('#addTitle').hide();
                    $('#modal-que-save').hide();  
                    $('#editTitle').show();
                    $('#modal-que-edit').show();  
                    $('#editSuccess').hide();
                    $('#addSuccess').hide();    
                    $('.print-error-msg').hide();
                    $('#skuTbx').attr('readonly',true);
                },
                
                error: function(XMLHttpRequest, textStatus, errorThrown) {                
                    
                }        
            });

            
           
    
    });

    function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }

        $('#modal-que-edit').on('click',function(event){                       
            var id = $('#catId').val();
            var date = $('#dateTbx').val();
            var description = $('#descTbx').val();
            var debit = $('#debitTbx').val();
            var credit = $('#creditTbx').val();
            var bank = $('#banksTbx').val();
            var category = $('#catTbx').val();


            $.ajax({
                
            type: 'post',
            url: '/editTransaction',
            data: {
            'id':id,
            'date': date,
            'description':description,
            'debit' : debit,
            'credit':credit,
            'bank':bank,
            'category':category            
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data) {
            console.log(data);
            if (data == 'success') {
                $('#add-modal').modal('hide');
                $('#error').hide();
                document.location.reload();
                $("#addSuccess").show().delay(3000).fadeOut();
            } else
                printErrorMsg(data.error);
            },
            
            error: function(XMLHttpRequest, textStatus, errorThrown) {                
                $('#error').show();
            }        
        });
        })
 

        $(document).on("click", "#assignBtn", function(){		
        var rows=[];        
        $("input:checkbox").each(function(){
            var $this = $(this);

            if($this.is(":checked")){
                rows.push($this.attr("id"));

            }
        });

      
        var user = $('#categoryAssign').val();

        if(user==0)
        {
            $('#error').show();             
            return;
        }

        if(rows.length == 0)
        {
                $('#error2').show();
                return;
        }
        
        $.ajax({
                
                type: 'post',
                url: '/assignTransaction',
                data: {
                'rows': rows,
                'user': user
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                console.log(data);
                if (data == 'success') {
                    $('#add-modal').modal('hide');
                    $('#error').hide();
                    document.location.reload();
                    $("#addSuccess").show().delay(3000).fadeOut();
                } else
                    $('#error').show();
                },
                
                error: function(XMLHttpRequest, textStatus, errorThrown) {                
                    $('#error').show();
                }        
            });

 });

 $(document).on("click", "#export", function(){		

try{
    var dateRange = "<?php echo empty($dateRange)?"":$dateRange; ?>";
    var categoryFilter = "<?php echo empty($categoryFilter)?"":$categoryFilter; ?>";
    var bankFilter = "<?php echo empty($bankFilter)?"":$bankFilter; ?>";        

var query = {                
                dateRange:dateRange,
                categoryFilter:categoryFilter,
                bankFilter:bankFilter
            }


var url = "/processedtransactionexport?" + $.param(query)

window.location = url;

}
catch{
    
}
}); 

 $(document).on("click", "#assign", function(){		
    $('#assignModal').modal('show');  
    $('#error').hide();
    $('#error2').hide();
    $('#categoryAssign').val(0);
});

});
</script>
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
                            <div class="col-4">
                                <h3 class="mb-0">{{ __('Accounting - Processed Transactions') }}</h3>
                            </div>
                            <div class="col-8" style="float:right; ">
                            
                            
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
                        <form action="processedtransactionFilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                            @csrf
                            <div style="width:100%; padding-bottom:2%;">
                                <div class="form-group">
                                <div style="padding-right: 1%; float:right; width=170px; ">                                
                                    <input class="form-control" type="text" name="dateRange" value="{{$dateRange ?? ''}}" />
                                </div>                             

                            <div style="padding-right:1%;">
                                <select class="form-control" name="categoryFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value='{{$category->id}}' {{ isset($categoryFilter) && $category->id == $categoryFilter?"selected":"" }}>{{$category->category}}</option>
                                    @endforeach
                                </select>
                            </div>
                                    
                            <div style="padding-right:1%;">
                                <select class="form-control" name="bankFilter" style="margin-right:0%;width:180px;">
                                    <option value="0">Bank Account</option>
                                    @foreach($banks as $bank)
                                        <option value='{{$bank->id}}' {{ isset($bankFilter) && $bank->id == $bankFilter?"selected":"" }}>{{$bank->name}}</option>
                                    @endforeach
                                </select>
                            </div>                            
                                    
                                    <input type="submit" value="Filter" class="btn btn-primary btn-md" style="margin-left:8px;">             
                                    <a id="export" class="btn btn-primary btn-md" style="color:white;float:right;">Export</a>     
                                    @if(!empty($search) && $search==1)
                                    <a href="{{ route($route) }}"class="btn btn-primary btn-md" style="margin-left:20%;">Go Back</a>
                                    @endif                                                              
                                </div>
                                
                            </div>
                            
                            
                            
                        </form>   
                          
                        
                    </div>

                    
                </div>
                
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="4%"><input type="checkbox" id="check-all" /></th>
                                    <th scope="col">{{ __('Date') }}</th>
                                    <th scope="col">{{ __('Bank Account') }}</th>                                     
                                    <th scope="col">{{ __('Description') }}</th>  
                                    <th scope="col">{{ __('Debit Amount') }}</th>  
                                    <th scope="col">{{ __('Credit Amount') }}</th>  
                                    <th scope="col">{{ __('Category') }}</th>                                     
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $transaction)
                                    <tr>     
                                        <td><input type="checkbox" id="check-{{$transaction->id}}" width="4%"/></td>                                     
                                        <td>{{ date_format(date_create($provider::getIranTime($transaction->date)), 'm/d/Y') }}</td>
                                        <td>{{ $transaction->name }}</td>
                                        <td>{{ $transaction->description }}</td>
                                        @if(empty($transaction->debitAmount))
                                        <td></td>
                                        @else
                                        <td>{{number_format((float)$transaction->debitAmount , 2, '.', '')}}</td>
                                        @endif
                                        @if(empty($transaction->creditAmount))
                                        <td></td>
                                        @else
                                        <td>{{number_format((float)$transaction->creditAmount , 2, '.', '')}}</td>
                                        @endif
                                        <td>{{ $transaction->category }}</td>                                                                                                                    
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('transactionDelete', $transaction->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-id="{{$transaction->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this transaction?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Delete') }}
                                                            </button>
                                                            @endif
                                                        </form>                                                       
                                                </div>
                                            </div>
                                        </td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row" style="padding-right:2%">
                    <div class="col-md-4 offset-md-8" style="text-align:right">
                        <span>Showing {{$transactions->toArray()['from']}} - {{$transactions->toArray()['to']}} of {{$transactions->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $transactions->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Question Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="addCat">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
      @lang('Some fields are incorrect or missing below:')
       </div>       
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Transaction Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">            
            <h4 class="modal-title" id="editTitle">@lang('Update Transaction')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
            <div class="alert alert-danger print-error-msg" style="display:none">
                <ul></ul>
            </div>
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Date:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="date" class="form-control" id="dateTbx" name="date" >                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Bank Account:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="banksTbx" name="userList" style="">                                
                        <option value="0">Select Bank</option>                                                   
                        @foreach($banks as $bank)                                                 
                            <option value="{{$bank->id}}">{{$bank->name}}</option>
                        @endforeach
                    </select>
                   </div>
                   
               </div>
               <div id="emptyType" style="color:red; display:none;">Please select a Type</div>
           </div>
       </div>
</div>

<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Description:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="descTbx" >                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Debit Amount:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="debitTbx" >                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Credit Amount:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="creditTbx" >                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Category:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="catTbx"  style="">                                
                        <option value="0">Select Category</option>                                                   
                        @foreach($categories as $category)                                                 
                            <option value="{{$category->id}}">{{$category->category}}</option>
                        @endforeach
                    </select>
                   </div>
                   
               </div>
               <div id="emptyType" style="color:red; display:none;">Please select a Type</div>
           </div>
       </div>
</div>
       
   </form>
      </div>
       <div class="modal-footer">        
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Transaction')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
    <!-- Assign Modal -->
    <div class="modal fade" tabindex="-1" role="dialog" id="assignModal">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
            Please select a category. 
       </div>

       <div class="alert alert-danger" id="error2" style="display:none">
            Please select one or more transactions to assign. 
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Assign category:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Select category:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="categoryAssign" name="userList" style="">                                
                        <option value=0>Select Category</option>
                        @foreach($categories as $category)
                        <option value={{$category->id}}>{{$category->category}}</option>                                                           
                        @endforeach                                                      
                    </select>
                   </div>
                   
               </div>
           </div>
       </div>
</div>
      
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="assignBtn">@lang('Assign')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Assign Modal -->
        @include('layouts.footers.auth')
    </div>
@endsection