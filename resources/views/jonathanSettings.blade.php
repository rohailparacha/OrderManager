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
});

$( function() {
    var minAmount = <?php echo empty($settings->minAmount)?0:json_encode($settings->minAmount); ?>;
    var maxAmount = <?php echo empty($settings->maxAmount)?100:json_encode($settings->maxAmount); ?>;
    
    var switcher = <?php echo empty($settings->enabled)?0:json_encode($settings->enabled); ?>;
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

  $( "#price-range" ).slider({
    range: true,
    min: 0,
    max: 100,
    values: [ minAmount, maxAmount ],
    slide: function( event, ui ) {
      $( "#amount" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    }
  });

  $( "#amount" ).val( $( "#price-range" ).slider( "values", 0 ) +
    " - " + $( "#price-range" ).slider( "values", 1 ) );
} );

$( function() {
    var minQty = <?php echo empty($settings->minQty)?0:json_encode($settings->minQty); ?>;
    var maxQty = <?php echo empty($settings->maxQty)?20:json_encode($settings->maxQty); ?>;

  $( "#qty-range" ).slider({
    range: true,
    min: 0,
    max: 20,
    values: [ minQty, maxQty ],
    slide: function( event, ui ) {
      $( "#qtyRange" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    }
  });

  $( "#qtyRange" ).val( $( "#qty-range" ).slider( "values", 0 ) +
    " - " + $( "#qty-range" ).slider( "values", 1 ) );
} );
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
                        <form method="post" action="/jonathanStoreSettings" autocomplete="off" id="target">
                            @csrf
                            
                            <div class="row" style="padding-right:3%;">
                                <div class="col-md-6">
                                <h3 class=" text-muted mb-4">{{ __('Jonathan - Settings') }}</h3>
                                </div>
                                <div class="col-md-6">
                                <input type="text" readonly id="switch" name="switch" class="btn btn-success"  style="float:right;" value="Enable"/>
                                </div>
                            </div>
                        

                                <div style="padding-right:3%;">
                                <p id="price">                                      
                                    <label class="form-control-label" for="input-name">{{ __('Price Range:') }}</label>
                                    
                                </p>
                                   
                                </div>
                               
                                <div class="row">
                                <div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
                                    <input  type="checkbox" id="vehicle1" name="pricecheck" {{!empty($settings->amountCheck) && $settings->amountCheck==true?'checked':''}}>  
                                </div>
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input  class="form-control"   style="width:100%;" type="text" name= "amountFilter" id="amount" readonly/>
                                    </div>
                                    <div id="price-range" style="width:33%;"></div>
                                </div>
                                </div>
                               <br><br>

                               <div style="padding-right:3%;">
                                <p id="price">                                      
                                    <label class="form-control-label" for="input-name">{{ __('Quantity Range:') }}</label>
                                    
                                </p>
                                   
                                </div>
                               
                                <div class="row">
                                <div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
                                    <input  type="checkbox" id="vehicle1" name="qtyRangeCheck" {{!empty($settings->quantityRangeCheck) && $settings->quantityRangeCheck==true?'checked':''}}>  
                                </div>
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input  class="form-control"   style="width:100%;" type="text" name= "qtyRangeFilter" id="qtyRange" readonly/>
                                    </div>
                                    <div id="qty-range" style="width:33%;"></div>
                                </div>
                                </div>
                               <br><br>


                                <div class="form-group{{ $errors->has('role') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select Stores:') }}</label>
                                
                                
                                
                                <div class="row">
                                <div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
                                    <input type="checkbox" id="vehicle1" name="storecheck" {{!empty($settings->storesCheck) && $settings->storesCheck==true?'checked':''}}>  
                                </div>
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <select class="form-control" name="stores[]" id="pages"  multiple="multiple">                                                                                    
                                            @foreach($stores as $store)
                                                    <option value={{$store->id}} {{ isset($settings->stores) && in_array($store->id, json_decode($settings->stores))?"selected":"" }}>{{$store->store}}</option>
                                            @endforeach                                                                                                                                   
                                </select>                                    
                                    
                                        @error('stores')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                         @enderror
                                    </div>
                                
                                </div>
                                </div>

                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Max Daily Order:') }}</label>                                

                                <div class="row">
                                <div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
                                    <input  type="checkbox" id="vehicle1" name="dailyordercheck" {{!empty($settings->dailyOrderCheck) && $settings->dailyOrderCheck==true?'checked':''}}>  
                                </div>
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="maxDailyOrder" id="input-name" class="form-control form-inline" placeholder="{{ __('Max Daily Order') }}" value="{{ empty($settings->maxDailyOrder)?'0':$settings->maxDailyOrder  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('maxDailyOrder')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror

                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Max Daily Amount:') }}</label>                                

                                <div class="row">
                                <div class="col-sm-1 form-group" style="max-width:2%;margin-top: 0.6rem;">                                    
                                    <input  type="checkbox" id="vehicle1" name="dailyamtcheck" {{!empty($settings->dailyAmountCheck) && $settings->dailyAmountCheck==true?'checked':''}}>  
                                </div>
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="maxDailyAmount" id="input-name" class="form-control form-inline" placeholder="{{ __('Max Daily Amount') }}" value="{{ empty($settings->maxDailyAmount)?'0':$settings->maxDailyAmount  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('maxDailyAmount')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                
                                <br><br>
                                
                                <label class="form-control-label" for="input-name">{{ __('Discount:') }}</label>                                

                                <div class="row">
                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="discount" id="input-name" class="form-control form-inline" placeholder="{{ __('Discount %') }}" value="{{ empty($settings->discount)?'0':$settings->discount  }}" autofocus>
                                    </div>
                                </div>
                                
                                </div>
                                @error('discount')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Max Price Factor:') }}</label>                                

                                <div class="row">
                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="maxPrice" id="input-name" class="form-control form-inline" placeholder="{{ __('Max Price Factor') }}" value="{{ empty($settings->maxPrice)?'0':$settings->maxPrice  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('maxPrice')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                
                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Priority:') }}</label>                                

                                <div class="row">

                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="priority" id="input-name" class="form-control form-inline" placeholder="{{ __('Priority') }}" value="{{ empty($settings->priority)?'0':$settings->priority  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('priority')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror

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