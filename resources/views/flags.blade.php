@extends('layouts.app', ['title' => __('Order Flags Management')])

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
        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {        
        
        var link     = $(e.relatedTarget),
        id = link.data("id"),
        name = link.data("name"),
        color = link.data('color')    
        ;

        console.log(link.data());
        console.log(id);
        console.log(name);
        $('#catId').val(id);
        $('#categoryTbx').val(name);
        $('#colorTbx').val(color);
        $('#addTitle').hide();
        $('#modal-que-save').hide();  
        $('#editTitle').show();
        $('#modal-que-edit').show();  
        $('#editSuccess').hide();
        $('#addSuccess').hide();
    
    });

        $('#modal-que-save').on('click',function(event){ 
           
            var obj = $('#categoryTbx').val();    
            var color = $('#colorTbx').val();            
            $.ajax({
                
            type: 'post',
            url: '/addFlag',
            data: {
            'name': obj, 
            'color':color          
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
        })

        $('#modal-que-edit').on('click',function(event){ 
           
           var obj = $('#categoryTbx').val();           
           var id = $('#catId').val();
           var color= $('#colorTbx').val();

           $.ajax({
               
           type: 'post',
           url: '/editFlag',
           data: {
           'name': obj,
           'color':color,
           'id': id
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
               $('#error').show();
           },
           
           error: function(XMLHttpRequest, textStatus, errorThrown) {                
               $('#error').show();
           }        
       });
       })
    
    
    
    });
</script>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Flags') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <input type="button" id="btnAddCat" class="btn btn-sm btn-primary" value="Add Flag"/>                                    
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
                                    <th scope="col">{{ __('Flag Name') }}</th>
                                    <th scope="col">{{ __('Color') }}</th>                                    
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($flags as $flag)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>                                        
                                        <td>{{ $flag->name }}</td>
                                        <td>{{ $flag->color }}</td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('flagDelete', $flag->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat"  data-name="{{$flag->name}}" data-color="{{$flag->color}}"  data-id="{{$flag->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this flag?") }}') ? this.parentElement.submit() : ''">
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
                            {{ $flags->links() }}
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
               @lang('Flag Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Flag Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add New Flag')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Flag')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Flag Name:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="categoryTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>
<br/><br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Background Color:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="colorTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>

       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Flag')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Flag')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
        @include('layouts.footers.auth')
    </div>
@endsection