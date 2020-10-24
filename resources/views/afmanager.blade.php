@extends('layouts.app', ['title' => __('Order Fulfillment Settings')])

@section('content')
    @include('users.partials.header', ['title' => __('Auto Fulfillment Manager')])   

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

    $('.switch').on('click',function(event){ 
        if(this.value=="Enable")
        {
            $(this).val('Disable')
            $(this).removeClass("btn-success").addClass("btn-danger");
        }            
        else    
        {
            $(this).val('Enable')
            $(this).removeClass("btn-danger").addClass("btn-success");            
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
                        <form method="post" action="/afStoreSettings" autocomplete="off" id="target">
                            @csrf
                            
                            <div class="row" style="padding-right:3%;">
                                <div class="col-md-6">
                                <h3 class=" text-muted mb-4">{{ __('Auto-Fulfillment Manager') }}</h3>
                                </div>                                
                            </div>

                        <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="40%" style="text-align:center; font-size:16px;">{{ __('Name') }}</th>
                                    <th scope="col" width="60%" style="text-align:center; font-size:16px;">{{ __('Action') }}</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($settings as $setting)
                                    <tr>
                                        <td style="text-align:center; font-size:16px;">{{ strtoupper($setting->name) }}</td>
                                        <td style="text-align:center;">
                                        @if($setting->sidebarCheck)
                                            <input style="float:center;" name="{{$setting->name}}" type="text" readonly class="switch btn btn-danger"  value="Disable"/>
                                        @else
                                            <input style="float:center;" name="{{$setting->name}}" type="text" readonly class="switch btn btn-success"  value="Enable"/>
                                        @endif
                                        </td>                                        
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                                
                               
                                <div class="text-center">
                                    <button type="submit"  id="submitBtn" class="btn btn-success mt-4">{{ __('Save') }}</button>
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