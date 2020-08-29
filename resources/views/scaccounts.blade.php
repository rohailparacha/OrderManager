@extends('layouts.app', ['title' => __('SyncCentric Accounts Management')])

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
        $(".print-error-msg").hide();
        $('#error').hide();
        $( "#new" ).prop( "checked", false );
        $( "#primary" ).prop( "checked", false );
        $( "#secondary" ).prop( "checked", false );
        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {        
        
        var link     = $(e.relatedTarget),
        id = link.data("id"),
        campaign = link.data("campaign"),
        token = link.data('token'),   
        name = link.data('name');
        products = link.data('products');
        
        if(jQuery.inArray('1', products) !== -1)
            $( "#primary" ).prop( "checked", true );
        else
            $( "#primary" ).prop( "checked", false );
            
        if(jQuery.inArray('2', products) !== -1)
            $( "#secondary" ).prop( "checked", true );
        else
            $( "#secondary" ).prop( "checked", false );
        
        if(jQuery.inArray('3', products) !== -1)
            $( "#new" ).prop( "checked", true );
        else
            $( "#new" ).prop( "checked", false );

        $(".print-error-msg").hide();
        $('#catId').val(id);
        $('#campaignTbx').val(campaign);
        $('#nameTbx').val(name);
        $('#tokenTbx').val(token);
        $('#pages').val(products);
        $('#addTitle').hide();
        $('#modal-que-save').hide();  
        $('#editTitle').show();
        $('#modal-que-edit').show();  
        $('#editSuccess').hide();
        $('#addSuccess').hide();
        $('#error').hide();
    });

        $('#modal-que-save').on('click',function(event){ 
           
            var campaign = $('#campaignTbx').val();
            var token = $('#tokenTbx').val();
            var name = $('#nameTbx').val();

            var prods = [];

            if($('#primary').is(":checked"))
                prods.push(1);
            if($('#secondary').is(":checked"))
                prods.push(2);
            if($('#new').is(":checked"))
                prods.push(3);    
                            
            $.ajax({
                
            type: 'post',
            url: '/addSCAccount',
            data: {
            'campaign': campaign,
            'token' : token,
            'name':name,
            'products':prods
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
           
           var campaign = $('#campaignTbx').val();
           var token = $('#tokenTbx').val();
           var id = $('#catId').val();
           var name = $('#nameTbx').val();

           var prods = [];

           if($('#primary').is(":checked"))
                prods.push(1);
            if($('#secondary').is(":checked"))
                prods.push(2);
            if($('#new').is(":checked"))
                prods.push(3);           
           

           $.ajax({
               
           type: 'post',
           url: '/editSCAccount',
           data: {
           'campaign': campaign,
           'id': id,
           'token' : token,
           'name':name,
           'products':prods
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
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('SyncCentric Accounts') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <input type="button" id="btnAddCat" class="btn btn-sm btn-primary" value="Add SC Account"/>                                    
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
                                    <th scope="col">{{ __('Name') }}</th>    
                                    <th scope="col">{{ __('Token') }}</th>    
                                    <th scope="col">{{ __('Campaign') }}</th>                                    
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $account->name }}</td>
                                        <td>{{ $account->token }}</td>
                                        <td>{{ $account->campaign }}</td>
                                        <td class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('scAccountDelete', $account->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-products = "{{$account->products}}" data-campaign= "{{$account->campaign}}" data-token="{{$account->token}}" data-name="{{$account->name}}" data-id="{{$account->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this account?") }}') ? this.parentElement.submit() : ''">
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
                            {{ $accounts->links() }}
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
               @lang('Account Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Account Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add New Account')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Account')</h4>
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
           <label for="email_address_2">@lang('Name:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="nameTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>

<br/><br/>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Campaign:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="campaignTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>

<br/><br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Token:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="tokenTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>

<br/><br/>

<div class="row">
<div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
    <input  type="checkbox" id="primary" }}>  
</div>

<div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Primary Products')</label>
</div>

<div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
    <input  type="checkbox" id="secondary" name="pricecheck" {{!empty($settings->amountCheck) && $settings->amountCheck==true?'checked':''}}>  
</div>

<div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Secondary Products')</label>
</div>


<div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
    <input  type="checkbox" id="new" name="pricecheck" {{!empty($settings->amountCheck) && $settings->amountCheck==true?'checked':''}}>  
</div>

<div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('New Products')</label>
</div>

</div>
       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Account')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Account')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
        @include('layouts.footers.auth')
    </div>
@endsection