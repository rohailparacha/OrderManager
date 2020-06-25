<nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
    <div class="container-fluid">
        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Brand -->
        <a class="navbar-brand pt-0" href="{{ route('home') }}">
            <img src="{{ asset('argon') }}/img/brand/logo-selleractive.svg" class="navbar-brand-img" alt="...">
        </a>
        <!-- User -->
        <ul class="nav align-items-center d-md-none">
            <li class="nav-item dropdown">
                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle">
                        <img alt="Image placeholder" src="{{ asset('argon') }}/img/theme/default_avatar.png">
                        </span>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ __('Welcome!') }}</h6>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                        <i class="ni ni-single-02"></i>
                        <span>{{ __('My profile') }}</span>
                    </a>
                    
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault();
                    document.getElementById('logout-form').submit();">
                        <i class="ni ni-user-run"></i>
                        <span>{{ __('Logout') }}</span>
                    </a>
                </div>
            </li>
        </ul>
        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenav-collapse-main">
            <!-- Collapse header -->
            <div class="navbar-collapse-header d-md-none">
                <div class="row">
                    <div class="col-6 collapse-brand">
                        <a href="{{ route('home') }}">
                            <img src="{{ asset('argon') }}/img/brand/logo-selleractive.svg">
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Form -->
            
            <!-- Navigation -->
            <ul class="navbar-nav">
                    <!-- new sidebar -->

                    <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-cart-arrow-down text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Orders') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">                            
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(1,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrders') }}">
                                {{ __('New Orders') }}
                            </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(2,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('processedOrders') }}">
                                   {{ __('Processed Orders') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(3,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shippedOrders') }}">
                                    {{ __('Shipped Orders') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(4,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cancelledOrders') }}">
                                    {{ __('Cancelled Orders') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(5,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('conversions') }}">
                                    {{ __('BCE Conversions') }}
                                </a>
                            </li>
                            @endif
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('autofulfillconversions') }}">
                                    {{ __('Order Fulfillment BCE Conversions') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('autofulfillCancel') }}">
                                    {{ __('Order Fulfillment Cancel Alert') }}
                                </a>
                            </li>

                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(6,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('returns') }}">
                                    {{ __('Return Center') }}
                                </a>
                            </li>
                            @endif

                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(6,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('autoFulfill') }}">
                                    {{ __('Auto-Fulfill Orders') }}
                                </a>
                            </li>
                            @endif
                            

                        </ul>
                    </div>
                </li>

                
                <!--  new sidebar end -->                
                
                
                
                <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-users text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Users') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">                            
                            @if(auth()->user()->role==1 )
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('user.index') }}">
                                    {{ __('User Management') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 )
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('managers') }}">
                                    {{ __('Managers') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || auth()->user()->role==2)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orderassign') }}">
                                    {{ __('Assign User to Orders') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>
                
                
                <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-tags text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Repricing') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">   
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(7,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products') }}">
                                    {{ __('Amazon Products') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(8,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('ebayProducts') }}">
                                    {{ __('eBay Products') }}
                                </a>
                            </li>
                            @endif
                            
                             <li class="nav-item">
                                <a class="nav-link" href="{{ route('walmartProducts') }}">
                                    {{ __('Walmart Products') }}
                                </a>
                            </li>
                            
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(9,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logs') }}">
                                    {{ __('Logs') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>

                @if(auth()->user()->role==1)
                <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-cogs text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Settings') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">                            
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('accounts') }}">
                                {{ __('Marketplace Accounts') }}
                            </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('carriers') }}">
                                   {{ __('Carriers') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('gmailAccounts') }}">
                                    {{ __('Gmail Integration') }}
                                </a>
                            </li>
                          
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('strategies') }}">
                                    {{ __('Amazon Pricing Strategy') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('ebaystrategies') }}">
                                    {{ __('Ebay Pricing Strategy') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('blacklist') }}">
                                    {{ __('Blacklist') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orderFulfillmentSetting') }}">
                                    {{ __('Order Fulfillment Setting') }}
                                </a>
                            </li>

                            

                            

                        </ul>
                    </div>
                </li>

                

                @endif
                
                <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-file-medical-alt text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Reports') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">        
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(10,json_decode(auth()->user()->assigned_pages))))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report') }}">
                                    {{ __('Report') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('product.report') }}">
                                    Product Report
                                </a>
                            </li>

                        @endif
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="#navbar-examples" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fas fa-calculator text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Accounting') }}</span>
                    </a>

                    <div class="collapse show" id="navbar-examples">
                        <ul class="nav nav-sm flex-column">                                
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('bankaccounts') }}">
                                    {{ __('Bank Accounts') }}
                                </a>
                            </li>                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('categories') }}">
                                    {{ __('Categories') }}
                                </a>
                            </li>      
                             <li class="nav-item">
                                <a class="nav-link" href="{{ route('transactions') }}">
                                    {{ __('Pending Transactions') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('processedtransactions') }}">
                                    {{ __('Processed Transactions') }}
                                </a>
                            </li>                                              
                        </ul>
                    </div>
                </li>
            

            </ul>
            <!-- Divider -->
            <hr class="my-3">
         
        </div>
    </div>
</nav>