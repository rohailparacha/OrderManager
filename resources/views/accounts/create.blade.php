@extends('layouts.app', ['title' => __('Marketplace Account Management')])

@section('content')
@include('users.partials.header', ['title' => __('Add Account')])   

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
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Marketplace Account Management') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('accounts') }}" class="btn btn-sm btn-primary">{{ __('Back to list') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('storeaccount') }}" autocomplete="off">
                            @csrf
                            
                            <h6 class="heading-small text-muted mb-4">{{ __('Account information') }}</h6>
                            <div class="pl-lg-4">
                                <div class="form-group{{ $errors->has('store') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Store Name') }}</label>
                                    <input type="text" name="store" id="input-name" class="form-control form-control-alternative{{ $errors->has('store') ? ' is-invalid' : '' }}" placeholder="{{ __('Store Name') }}" value="{{ old('store') }}" required autofocus>

                                    @if ($errors->has('store'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('store') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group{{ $errors->has('username') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Username') }}</label>
                                    <input type="text" name="username" id="input-email" class="form-control form-control-alternative{{ $errors->has('username') ? ' is-invalid' : '' }}" placeholder="{{ __('Username') }}" value="{{ old('username') }}" required>

                                    @if ($errors->has('username'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->account_add->first('username') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                

                                <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-password">{{ __('Password') }}</label>
                                    <input type="password" name="password" id="input-password" class="form-control form-control-alternative{{ $errors->has('password') ? ' is-invalid' : '' }}" placeholder="{{ __('Password') }}" value="" required>
                                    
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>       

                                <div class="form-group{{ $errors->has('lag') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Lag Time') }}</label>
                                    <input type="text" name="lag" id="input-email" class="form-control form-control-alternative{{ $errors->has('lag') ? ' is-invalid' : '' }}" placeholder="{{ __('Lag Time') }}" value="{{ old('lag') }}" required>

                                    @if ($errors->has('lag'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->account_add->first('lag') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('quantity') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Quantity') }}</label>
                                    <input type="text" name="quantity" id="input-email" class="form-control form-control-alternative{{ $errors->has('quantity') ? ' is-invalid' : '' }}" placeholder="{{ __('Quantity') }}" value="{{ old('quantity') }}" required>

                                    @if ($errors->has('quantity'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->account_add->first('quantity') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('maxListing') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Max Listing Buffer') }}</label>
                                    <input type="text" name="maxListing" id="input-email" class="form-control form-control-alternative{{ $errors->has('maxListing') ? ' is-invalid' : '' }}" placeholder="{{ __('Max Listing Buffer') }}" value="{{ old('maxListing') }}" required>

                                    @if ($errors->has('maxListing'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->account_add->first('maxListing') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('informed') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Informed Id') }}</label>
                                    <input type="text" name="informed" id="input-email" class="form-control form-control-alternative{{ $errors->has('informed') ? ' is-invalid' : '' }}" placeholder="{{ __('Informed Account Id') }}" value="{{ old('informed') }}" required>

                                    @if ($errors->has('informed'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->account_add->first('informed') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="form-group{{ $errors->has('infaccount') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select Informed Account:') }}</label>
                                        <select class="form-control" name="infaccount" style="">                                
                                                    <option value=0>Select Account</option>
                                                    @foreach($accounts as $acc)
                                                    <option value={{$acc->id}}>{{$acc->name}}</option>    
                                                    @endforeach                                                                                                  
                                        </select>                                    
                                    
                                        @error('infaccount')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>

                                <div class="form-group{{ $errors->has('manager') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select Manager:') }}</label>
                                        <select class="form-control" name="manager" style="">                                
                                                    <option value=0>Select Managers</option>
                                                    @foreach($managers as $manager)
                                                    <option value={{$manager->id}}>{{$manager->name}}</option>    
                                                    @endforeach                                                                                                  
                                        </select>                                    
                                    
                                        @error('manager')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>

                                <div class="form-group{{ $errors->has('scaccount') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select SC Account:') }}</label>
                                        <select class="form-control" name="scaccount" style="">                                
                                                    <option value=0>Select Account</option>
                                                    @foreach($scaccounts as $account)
                                                    <option value={{$account->id}}>{{$account->name}}</option>    
                                                    @endforeach                                                                                                  
                                        </select>                                    
                                    
                                        @error('scaccount')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-success mt-4">{{ __('Save') }}</button>
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