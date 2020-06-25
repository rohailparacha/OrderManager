@extends('layouts.app', ['title' => __('New Orders')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
<script>


$(document).ready(function(){
    $("#check-all").click(function(){
    $('input:checkbox').not(this).prop('checked', this.checked);
        
});


    $(document).on("click", "#assignBtn", function(){		
        var rows=[];        
        $("input:checkbox").each(function(){
            var $this = $(this);

            if($this.is(":checked")){
                rows.push($this.attr("id"));

            }
        });

      
        var user = $('#users').val();

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
                url: '/assignManager',
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

   

 $(document).on("click", "#assign", function(){		
    $('#assignModal').modal('show');  
    $('#error').hide();
    $('#error2').hide();
    $('#users').val(0);
});
});
</script>
    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Assign Operators To Manager') }}</h3>
                            </div>    
                            
                            <div class="col-4 text-right">
                                <a id="assign" style="color:white;" class="btn btn-sm btn-primary">{{ __('Assign') }}</a>
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
                                    <th scope="col" width="5%"><input type="checkbox" id="check-all" /></th>
                                    <th scope="col" width="20%">{{ __('Serial') }}</th>
                                    <th scope="col" width="25%">{{ __('Operator') }}</th>
                                    <th scope="col" width="25%">{{ __('Assigned') }}</th>
                                    <th scope="col" width="25%">{{ __('Manager') }}</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($operators as $operator)
                                    <tr>
                                        <td><input type="checkbox" id="check-{{$operator->id}}" width="5%"/></td>                               
                                        <td width="20%">{{ $loop->iteration }}</td>
                                        <td width="25%">{{ $operator->name }}</td>
                                        <td width="25%">
                                        @if(empty($operator->manager_id))
                                            NO
                                        @else
                                            YES
                                        @endif
                                        </td>
                                        <td width="25%">{{ $managerArr[$operator->manager_id] }}</td>                                        
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$operators->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            

    <!-- Assign Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="assignModal">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
            Please select a user. 
       </div>

       <div class="alert alert-danger" id="error2" style="display:none">
            Please select one or more orders to assign. 
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Assign User to Orders:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Select Manager:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="users" name="userList" style="">                                
                        <option value=0>Select User</option>
                        @foreach($managers as $manager)
                        <option value={{$manager->id}}>{{$manager->name}}</option>                                                           
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