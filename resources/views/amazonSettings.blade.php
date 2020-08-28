@extends('layouts.app', ['title' => __('Order Fulfillment Settings')])

@section('content')
    @include('users.partials.header', ['title' => __('Amazon Product Settings')])   

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
                            
                        <form method="post" action="/storeAmazonSettings" autocomplete="off" id="target">
                            @csrf
                            
                            <div class="row" style="padding-right:3%;">
                                <div class="col-md-6">
                                <h3 class=" text-muted mb-4">{{ __('Amazon Product Settings') }}</h3>
                                </div>
                               
                            </div>
                        
                            
                                
                                <label class="form-control-label" for="input-name">{{ __('Sold X Days:') }}</label>                                

                                <div class="row">
                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="soldDays" id="input-name" class="form-control form-inline" placeholder="{{ __('Sold X Days') }}" value="{{ empty($settings->soldDays)?'':$settings->soldDays  }}" autofocus>
                                    </div>
                                </div>
                                
                                </div>
                                @error('discount')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Sold Value (Y):') }}</label>                                

                                <div class="row">
                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="soldQty" id="input-name" class="form-control form-inline" placeholder="{{ __('Sold Value (Y)') }}" value="{{ empty($settings->soldQty)?'':$settings->soldQty  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('maxPrice')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                
                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Created Created within (Z) Days Ago:') }}</label>                                

                                <div class="row">

                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="createdBefore" id="input-name" class="form-control form-inline" placeholder="{{ __('Created within (Z) Days Ago') }}" value="{{ empty($settings->createdBefore)?'':$settings->createdBefore  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('priority')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror

                                <br><br>
                                </div>
                               
                                <div class="text-center">
                                    <button type="submit" id="submitBtn" class="btn btn-success mt-4">{{ __('Save') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
@include('layouts.footers.auth')    
    </div>
       
    
	
@endsection