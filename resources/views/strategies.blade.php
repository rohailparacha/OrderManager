@extends('layouts.app', ['title' => __('Strategy Management')])

@section('content')
    @include('layouts.headers.cards')
    <script>
$(document).ready(function(){
 
  
        $('#btnAddCat').on('click',function(event){ 
            $('.print-error-msg').hide();
        $('#addCat').modal('show');  
        $('#editTitle').hide();
        $('#modal-que-edit').hide(); 
        $('#addTitle').show();
        $('#modal-que-save').show();
        $('#editSuccess').hide();
        $('#addSuccess').hide();

        $('#codeTbx').val(''); 
        $('#valTbx').val(''); 
        $('#breakevenTbx').val(''); 
        $('#typeTbx').val(0); 
        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {        
        
        var link     = $(e.relatedTarget),
        id = link.data("id"),
        type = link.data("type"),
        breakeven = link.data("breakeven"),
        val = link.data("val"),
        code = link.data("code");

        $('#catId').val(id);            
        $('#codeTbx').val(code); 
        $('#valTbx').val(val); 
        $('#breakevenTbx').val(breakeven); 
        $('#typeTbx').val(type); 

        $('#addTitle').hide();
        $('#modal-que-save').hide();  
        $('#editTitle').show();
        $('#modal-que-edit').show();  
        $('#editSuccess').hide();
        $('#addSuccess').hide();
        $('.print-error-msg').hide();
    
    });

        $('#modal-que-save').on('click',function(event){ 
           
            var code = $('#codeTbx').val();
            var type = $('#typeTbx').val();
            var value = $('#valTbx').val();
            var breakeven = $('#breakevenTbx').val();
            $.ajax({
                
            type: 'post',
            url: '/addStrategy',
            data: {
            'code': code,
            'type': type,
            'value': value,
            'breakeven': breakeven,
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


        
       function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }
        })

        $('#modal-que-edit').on('click',function(event){ 
           
            var code = $('#codeTbx').val();
            var type = $('#typeTbx').val();
            var value = $('#valTbx').val();
            var breakeven = $('#breakevenTbx').val();
            var id = $('#catId').val();
            
           $.ajax({
               
           type: 'post',
           url: '/editStrategy',
           data: {
            'code': code,
            'type': type,
            'value': value,
            'breakeven': breakeven,
            'id':id
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


       function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }
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
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Strategies') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <input type="button" id="btnAddCat" class="btn btn-sm btn-primary" value="Add Strategy"/>                                    
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
                                    <th scope="col">{{ __('Serial') }}</th>
                                    <th scope="col">{{ __('Code') }}</th>
                                    <th scope="col">{{ __('Breakeven') }}</th>
                                    <th scope="col">{{ __('Type') }}</th>                                   
                                    <th scope="col">{{ __('No. of Products') }}</th>                                   
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($strategies as $strategy)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $strategy->code }}</td>
                                        <td>{{ $strategy->breakeven }}%</td>
                                        <td>{{ $strategy->value }} {{ $strategy->type==1?'$':'%' }}</td>      
                                        <td>{{ $strategy->count}}</td>                                  
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('strategyDelete', $strategy->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-code="{{$strategy->code}}" data-id="{{$strategy->id}}"  data-breakeven="{{$strategy->breakeven}}"  data-val="{{$strategy->value}}"  data-type="{{$strategy->type}}"   id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this strategy?") }}') ? this.parentElement.submit() : ''">
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
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $strategies->links() }}
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
               @lang('Strategy Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Strategy Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add New Strategy')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Strategy')</h4>
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
           <label for="email_address_2">@lang('Strategy Code:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="codeTbx" name="code" >                                        
                   </div>
                  
               </div>
           </div>
       </div>
</div>

<br/><br/>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Breakeven:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="breakevenTbx" name="breakeven" >                                        
                   </div>
                   
               </div>
           </div>
       </div>
</div>

<br/><br/>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Type:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="row form-line" style="padding-left: 15px;padding-right: 15px;">
                       <input class="form-control col-md-4" style="margin-right:10px;" type="text" class="form-control" id="valTbx" name="value" >                                        
                       <select class="form-control col-md-4" name="type" id="typeTbx" style="padding-left:5px;">                                
                                                        <option value=0>Select Type</option>
                                                        <option value=1>$</option>
                                                        <option value=2>%</option>                                                    
                        </select>  
                   </div>
             
               </div>
           </div>
       </div>
</div>

   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Strategy')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Strategy')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
        @include('layouts.footers.auth')
    </div>
@endsection