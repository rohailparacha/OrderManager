@extends('layouts.app', ['title' => __('Carriers Management')])

@section('content')
    @include('layouts.headers.cards')
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
                                <h3 class="mb-0">{{ __('Get Keepa Response') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                                                  
                            </div>    
                        </div>
                    </div>
                  

<div class="row" style="padding-left:10%; padding-top:3%; padding-bottom:3%;">
<form action="/getkeepa" class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('ASIN:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="categoryTbx" name="asin" >                                        
                   </div>
                    @error('asin')
                        <div class="permissions" style="color:red;">{{ $message }}</div>
                    @enderror
               </div>
           </div>
       </div>
</div>

<br/><br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Offers Count:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="aliasTbx" name="offer" >                                        
                   </div>
                   @error('offer')
                        <div class="permissions" style="color:red;">{{ $message }}</div>
                    @enderror
               </div>
           </div>
       </div>
</div>
    <div class="text-center">
        <button type="submit" class="btn btn-success mt-4">{{ __('Get Response') }}</button>
    </div>
       
   </form>
                </div>
                </div>
            </div>
        </div>
            

        @include('layouts.footers.auth')
    </div>
@endsection