@extends('layouts.app', ['title' => __('Order Fulfillment Settings')])

@section('content')
    @include('users.partials.header', ['title' => __('Jonathan - Order Fulfillment Settings')])   

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
input[type="checkbox"][readonly] {
  pointer-events: none;
}
</style>
<script>
$(document).ready(function() {
$('#pages').multiselect({
        includeSelectAllOption: true,
        enableClickableOptGroups: true
    });

    $('#switch').on('click',function(event){ 
        if(this.value=="Enable")
        {
            $(this).val('Disable')
            $(this).removeClass("btn-success").addClass("btn-danger");
            $("#target :input").not("#switch").not('#submitBtn').prop("readonly", false);
        }            
        else    
        {
            $(this).val('Enable')
            $(this).removeClass("btn-danger").addClass("btn-success");
            $("#target :input").not("#switch").not('#submitBtn').prop("readonly", true); 
        }


        });

        $( function() {

    var switcher = <?php echo empty($settings->statesCheck)?0:json_encode($settings->statesCheck); ?>;
    if(switcher==0)
    {
        $('#switch').val('Enable')
        $('#switch').removeClass("btn-danger").addClass("btn-success");
        $("#target :input").not("#switch").not('#submitBtn').prop("readonly", true); 
    }
    else
    {
        $('#switch').val('Disable')
        $('#switch').removeClass("btn-success").addClass("btn-danger");
        $("#target :input").not("#switch").not('#submitBtn').prop("readonly", false);
    }

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

        <div class="row">
            <div class="col-xl-12 order-xl-1">
                <div class="card bg-secondary shadow">
                   
                    <div class="card-body">
                        <form method="post" action="/storeStateSettings" autocomplete="off" id="target">
                            @csrf
                            
                            <div class="row" style="padding-right:3%;">
                                <div class="col-md-6">
                                <h3 class=" text-muted mb-4">{{ __('State - Settings') }}</h3>
                                </div>
                                <div class="col-md-6">
                                <input type="text" readonly id="switch" name="switch" class="btn btn-success"  style="float:right;" value="Enable"/>
                                </div>
                            </div>


                                <div class="form-group{{ $errors->has('role') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select States:') }}</label>
                                
                                
                                
                                <div class="row">                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <select class="form-control" name="states[]" id="pages"  multiple="multiple">                                                                                    
                                            @foreach($states as $state)
                                                    <option value={{$state->code}} {{ isset($state->code) && in_array($state->code, json_decode($settings->states))?"selected":"" }}>{{$state->code}} - {{$state->name}}</option>
                                            @endforeach                                                                                                                                   
                                </select>                                    
                                    
                                        @error('states')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                         @enderror
                                    </div>
                                
                                </div>
                                </div>

                                <br><br>
                                </div>
                               
                                <div class="text-center">
                                    <button  id="submitBtn" type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        @include('layouts.footers.auth')
    </div>
@endsection