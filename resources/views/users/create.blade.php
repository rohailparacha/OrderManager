@extends('layouts.app', ['title' => __('User Management')])

@section('content')
    @include('users.partials.header', ['title' => __('Add User')])   

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/js/bootstrap-multiselect.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.15/css/bootstrap-multiselect.css">

<script>
$(document).ready(function() {
$('#pages').multiselect({
        includeSelectAllOption: true,
        enableClickableOptGroups: true
    });
});
</script>

<div class="container-fluid mt--7">
        <div class="row">
            <div class="col-xl-12 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('User Management') }}</h3>
                            </div>
                            <div class="col-4 text-right">
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-primary">{{ __('Back to list') }}</a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="post" action="{{ route('user.store') }}" autocomplete="off">
                            @csrf
                            
                            <h6 class="heading-small text-muted mb-4">{{ __('User information') }}</h6>
                            <div class="pl-lg-4">
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Name') }}</label>
                                    <input type="text" name="name" id="input-name" class="form-control form-control-alternative{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="{{ __('Name') }}" value="{{ old('name') }}" required autofocus>

                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="form-group{{ $errors->has('email') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-email">{{ __('Email') }}</label>
                                    <input type="email" name="email" id="input-email" class="form-control form-control-alternative{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="{{ __('Email') }}" value="{{ old('email') }}" required>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
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
                                <div class="form-group">
                                    <label class="form-control-label" for="input-password-confirmation">{{ __('Confirm Password') }}</label>
                                    <input type="password" name="password_confirmation" id="input-password-confirmation" class="form-control form-control-alternative" placeholder="{{ __('Confirm Password') }}" value="" required>
                                </div>
                                
                                <div class="form-group{{ $errors->has('role') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select Role:') }}</label>
                                        <select class="form-control" name="role" style="">                                
                                                    <option value=0>Select Role</option>
                                                    <option value=1>Admin</option>
                                                    <option value=2>Manager</option>
                                                    <option value=3>User</option>                                                                                                        
                                        </select>                                    
                                    
                                        @error('role')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>

                                <div class="form-group{{ $errors->has('role') ? ' has-danger' : '' }}">
                                <label class="form-control-label" for="input-role">{{ __('Select Pages:') }}</label>
                                <select class="form-control" name="permissions[]" id="pages"  multiple="multiple">                                                                                    
                                            <optgroup label="Orders">
                                                    <option value=1>New Orders</option>
                                                    <option value=2>Processed Orders</option>
                                                    <option value=3>Shipped Orders</option>
                                                    <option value=4>Cancelled Orders</option>
                                                    <option value=5>BCE Conversions</option>
                                                    <option value=6>Return Center</option>                                                                                                        
                                            </optgroup>

                                            <optgroup label="Repricing">
                                                    <option value=7>Amazon Products</option>
                                                    <option value=8>eBay Products</option>
                                                    <option value=9>Logs</option>
                                            </optgroup>

                                            <optgroup label="Reports">
                                                    <option value=10>Report</option>
                                            </optgroup>
                                        </select>                                    
                                    
                                        @error('role')
                                            <div class="permissions" style="color:red;">{{ $message }}</div>
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