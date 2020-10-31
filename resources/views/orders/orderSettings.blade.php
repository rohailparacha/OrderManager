@extends('layouts.app', ['title' => __('New Order Pricing Settings')])

@section('content')
@include('users.partials.header', ['title' => __('New Order Pricing Settings')])   


<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


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
                        <form method="post" action="/pricingSettingsStore" autocomplete="off" id="target">
                            @csrf
                            
                            <div class="row" style="padding-right:3%;">
                                <div class="col-md-6">
                                <h3 class=" text-muted mb-4">{{ __('New Order Pricing Settings') }}</h3>
                                </div>
                            </div>
                            
                                <br><br>
                                <label class="form-control-label" for="input-name">{{ __('Price 1:') }}</label>                                

                                <div class="row">
                                
                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="price1" id="input-name" class="form-control form-inline" placeholder="{{ __('Price 1') }}" value="{{ empty($settings->price1)?'0':$settings->price1  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('price1')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
                                @enderror
                                
                                <br><br>
                                
                                <label class="form-control-label" for="input-name">{{ __('Price 2:') }}</label>                                

                                <div class="row">

                                <div class="col-sm-6 form-group">
                                    <div class="i-checks">
                                        <label class="control-label"> 
                                        <input type="text" name="price2" id="input-name" class="form-control form-inline" placeholder="{{ __('Price 2') }}" value="{{ empty($settings->price2)?'0':$settings->price2  }}" autofocus>
                                    </div>
                                </div>
                                </div>
                                @error('price2')
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