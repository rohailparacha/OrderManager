@extends('layouts.app', ['title' => __('Blacklist Management')])

@section('content')
    @include('layouts.headers.cards')
    <script>
$(document).ready(function(){
 
  
        $('#btnAddCat').on('click',function(event){ 
        $('#addCat').modal('show');  
        $('#editTitle').hide();
        $('#modal-que-edit').hide(); 
        $('#addTitle').show();
        $('#modal-que-save').show();
        $('#editSuccess').hide();
        $('#addSuccess').hide();
        $('#skuTbx').val('');  
        $('#allowanceTbx').val('');  
        $('#reasonTbx').val(0); 
        $(".print-error-msg").hide();
        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {        
        
        var link     = $(e.relatedTarget),
        id = link.data("id"),
        sku = link.data("sku"),
        reason = link.data("reason"),
        allowance = link.data("allowance")         
        ;
        $(".print-error-msg").hide();
        
        $('#catId').val(id);   
        $('#skuTbx').val(sku);  
        $('#reasonTbx').val(reason);         
        $('#allowanceTbx').val(allowance);

        $('#addTitle').hide();
        $('#modal-que-save').hide();  
        $('#editTitle').show();
        $('#modal-que-edit').show();  
        $('#editSuccess').hide();
        $('#addSuccess').hide();
    
    });

        $('#modal-que-save').on('click',function(event){ 
           
            var sku = $('#skuTbx').val();
            var reason = $('#reasonTbx').val();
            var allowance = $('#allowanceTbx').val();
            $.ajax({
                
            type: 'post',
            url: '/addBlacklist',
            data: {
            'sku': sku,
            'reason' : reason,
            'allowance' : allowance     
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

        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }

        $('#modal-que-edit').on('click',function(event){ 
           
           var id = $('#catId').val();
           
           var sku = $('#skuTbx').val();
           var reason = $('#reasonTbx').val();
           var allowance = $('#allowanceTbx').val();
           $.ajax({
               
           type: 'post',
           url: '/editBlacklist',
           data: {
           'sku': sku,
           'id': id,
           'reason': reason,
           'allowance' : allowance
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
               $("#editSuccess").show().delay(3000).fadeOut();
           } else
               printErrorMsg(data.error);
           },
           
           error: function(XMLHttpRequest, textStatus, errorThrown) {                
               $('#error').show();
           }        
       });
       })
    
    
    
    });
</script>
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
                            <div class="col-2">
                                <h3 class="mb-0">{{ __('Blacklist Management') }}</h3>
                            </div>
                            <div class="col-10 text-right">
                            <form class="form-inline" action="/blacklistImport" method="post" enctype="multipart/form-data" style="float:left;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file" />                
                            
                                    <input type="submit" class="btn btn-primary btn-md" value="Import" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>
                                
                                
                                <a href="blacklistExport" class="btn btn-primary btn-md" style="color:white;">Export</a>                                     
                                <input type="button" id="btnAddCat" class="btn btn-md btn-primary" value="Add Product"/>                                    
                                <a href="./blacklistTemplate" class="btn btn-primary btn-md" style="color:white;">Template File</a>   
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
                                    <th scope="col">{{ __('SKU') }}</th>                                     
                                    <th scope="col">{{ __('Reason') }}</th>      
                                    <th scope="col">{{ __('Allowance') }}</th>                                     
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($blacklist as $bl)
                                    <tr>
                                        <td>{{ $bl->date }}</td>
                                        <td>{{ $bl->sku }}</td>
                                        <td>{{ $bl->reason }}</td>  
                                        <td>{{ $bl->allowance }}</td>                                        
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('blacklistDelete', $bl->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-date="{{$bl->date}}" data-allowance ="{{$bl->allowance}}" data-sku="{{$bl->sku}}"  data-reason="{{$bl->reason}}" data-id="{{$bl->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this item from blacklist?") }}') ? this.parentElement.submit() : ''">
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
                        <span>Showing {{$blacklist->toArray()['from']}} - {{$blacklist->toArray()['to']}} of {{$blacklist->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $blacklist->links() }}
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
       <div class="alert alert-success" id="addSuccess" style="display:none">
               @lang('Product Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Product Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add Product')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Product')</h4>
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
           <label for="email_address_2">@lang('SKU:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="skuTbx" >                                        
                   </div>
                  
               </div>
           </div>
       </div>
</div>

<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Reason:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="reasonTbx" name="userList" style="">                                
                        <option value="0">Select Type</option>        
                        @foreach($reasons as $reason)                                           
                        <option value={{$reason->name}}>{{$reason->name}}</option>                   
                        @endforeach
                    </select>
                   </div>
                   
               </div>
               <div id="emptyType" style="color:red; display:none;">Please select a Reason</div>
           </div>
       </div>
</div>

<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Allowance:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="allowanceTbx" >                                        
                   </div>
                  
               </div>
           </div>
       </div>
</div>

<br/>
       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Product')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Product')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
        @include('layouts.footers.auth')
    </div>
@endsection