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
                    <a class="nav-link active" href="#ordersTab" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-cart-arrow-down text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Orders') }}</span>
                    </a>

                    <div class="collapse show" id="ordersTab">
                        <ul class="nav nav-sm flex-column">                            
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(1,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrders') }}">
                                {{ __('New Orders') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersFlagged') }}">
                                {{ __('New Orders - Flagged') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersMultiItems') }}">
                                {{ __('New Orders - Multi Item') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersZero') }}">
                                {{ __('New Orders - Zero') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersPrice1') }}">
                                {{ __('New Orders - Price 1') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersPrice2') }}">
                                {{ __('New Orders - Price 2') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersExpensive') }}">
                                {{ __('New Orders - Expensive') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersMovie') }}">
                                {{ __('New Orders - Movie') }}
                            </a>
                            </li>
                            
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('newOrdersFood') }}">
                                {{ __('New Orders - Food') }}
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

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('dueComing') }}">
                                {{ __('Due Date Coming Soon') }}
                            </a>
                            </li>


                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(3,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('shippedOrders') }}">
                                    {{ __('Shipped Orders') }}
                                </a>
                            </li>
                            @endif

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('lookup') }}">
                                    {{ __('Zip Code Lookup') }}
                                </a>
                            </li>

                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(4,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cancelledOrders') }}">
                                    {{ __('Cancelled Orders') }}
                                </a>
                            </li>
                            @endif
                            @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(5,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item" style="display:none;">
                                <a class="nav-link" href="{{ route('conversions') }}">
                                    {{ __('BCE Conversions - In Transit') }}
                                </a>
                            </li>
                            
                            <li class="nav-item" style="display:none;">
                                <a class="nav-link" href="{{ route('deliveredConversions') }}">
                                    {{ __('BCE Conversions - Delivered') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('upsConversions') }}">
                                    {{ __('UPS Conversions - Waiting For Tracking') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('upsApproval') }}">
                                    {{ __('UPS Conversions - Waiting For Approval') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('upsShipped') }}">
                                    {{ __('UPS Conversions - Shipped') }}
                                </a>
                            </li>
                            @endif
                            
                            

                        </ul>
                    </div>
                </li>

                
                <!--  new sidebar end -->                
                @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(6,json_decode(auth()->user()->assigned_pages))))                       
                <li class="nav-item">
                    <a class="nav-link active" href="#returnCenter" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-exchange-alt text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Return Center') }}</span>
                    </a>

                    <div class="collapse show" id="returnCenter">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('returns') }}">
                                    {{ __('Waiting For Return') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('refunds') }}">
                                    {{ __('Waiting For Refund') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('completed') }}">
                                    {{ __('Completed') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                
                @endif

                @if(\App\settings::where('name','Cindy')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#cindy" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Cindy') }}</span>
                    </a>

                    <div class="collapse show" id="cindy">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('cindynew') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('cindyprocessed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('cindycancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('cindyreturn') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('cindyrefund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('cindycompleted') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orderFulfillmentSetting') }}">
                                    {{ __('Cindy - Order Fulfillment Setting') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endif
                @if(\App\settings::where('name','Vaughn')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#vaughn" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Voughn') }}</span>
                    </a>

                    <div class="collapse show" id="vaughn">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vaughnnew') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('vaughnprocessed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('vaughncancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('vaughnreturn') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('vaughnrefund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('vaughncompleted') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('vaughnSetting') }}">
                                    {{ __('Voughn - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                @if(\App\settings::where('name','Jonathan')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#jonathan" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Jonathan') }}</span>
                    </a>

                    <div class="collapse show" id="jonathan">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jonathannew') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathanprocessed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathancancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathanreturn') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathanrefund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathancompleted') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jonathanSetting') }}">
                                    {{ __('Jonathan - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                @if(\App\settings::where('name','Jonathan2')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#jonathan2" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Jonathan - No Discount') }}</span>
                    </a>

                    <div class="collapse show" id="jonathan2">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jonathan2new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathan2processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathan2cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathan2return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathan2refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('jonathan2completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('jonathan2Setting') }}">
                                    {{ __('Jonathan2 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif
                @if(\App\settings::where('name','Yaballe')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#yaballe" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Yaballe') }}</span>
                    </a>

                    <div class="collapse show" id="yaballe">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('yaballenew') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('yaballeprocessed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('yaballecancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('yaballereturn') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('yaballerefund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('yaballecompleted') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('yaballeSetting') }}">
                                    {{ __('Yaballe - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                @if(\App\settings::where('name','saleFreaks1')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#saleFreaks1" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('saleFreaks1') }}</span>
                    </a>

                    <div class="collapse show" id="saleFreaks1">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks1new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks1processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks1cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks1return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks1refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks1completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks1Setting') }}">
                                    {{ __('saleFreaks1 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                @if(\App\settings::where('name','saleFreaks2')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#saleFreaks2" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('saleFreaks2') }}</span>
                    </a>

                    <div class="collapse show" id="saleFreaks2">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks2new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks2processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks2cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks2return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks2refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks2completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks2Setting') }}">
                                    {{ __('saleFreaks2 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                @if(\App\settings::where('name','saleFreaks3')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#saleFreaks3" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('saleFreaks3') }}</span>
                    </a>

                    <div class="collapse show" id="saleFreaks3">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks3new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks3processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks3cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks3return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks3refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks3completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks3Setting') }}">
                                    {{ __('saleFreaks3 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                @if(\App\settings::where('name','saleFreaks4')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#saleFreaks4" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('saleFreaks4') }}</span>
                    </a>

                    <div class="collapse show" id="saleFreaks4">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks4new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks4processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks4cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks4return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks4refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks4completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks4Setting') }}">
                                    {{ __('saleFreaks4 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                @if(\App\settings::where('name','saleFreaks5')->get()->first()->sidebarCheck)
                <li class="nav-item">
                    <a class="nav-link active" href="#saleFreaks5" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-sort text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('saleFreaks5') }}</span>
                    </a>

                    <div class="collapse show" id="saleFreaks5">
                        <ul class="nav nav-sm flex-column">                            
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks5new') }}">
                                    {{ __('New Orders') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks5processed') }}">
                                    {{ __('Processed Orders') }}
                                </a>
                            </li>
                        
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks5cancel') }}">
                                    {{ __('Cancel Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks5return') }}">
                                    {{ __('Return Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks5refund') }}">
                                    {{ __('Refund Pending') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"  href="{{ route('saleFreaks5completed') }}">
                                    {{ __('Completed Returns') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('saleFreaks5Setting') }}">
                                    {{ __('saleFreaks5 - Order Fulfillment Setting') }}
                                </a>
                            </li>
                            
                        </ul>
                    </div>
                </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link active" href="#users" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-users text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Users') }}</span>
                    </a>

                    <div class="collapse show" id="users">
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
                    <a class="nav-link active" href="#repricing" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-tags text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Repricing') }}</span>
                    </a>

                    <div class="collapse show" id="repricing">
                        <ul class="nav nav-sm flex-column">   
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(7,json_decode(auth()->user()->assigned_pages))))                       
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products') }}">
                                    {{ __('Amazon Primary') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('secondaryproducts') }}">
                                    {{ __('Amazon Secondary') }}
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

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logsSecondary') }}">
                                    {{ __('Logs New') }}
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </li>

                @if(auth()->user()->role==1)
                <li class="nav-item">
                    <a class="nav-link active" href="#settings" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-cogs text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Settings') }}</span>
                    </a>

                    <div class="collapse show" id="settings">
                        <ul class="nav nav-sm flex-column">                            
                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('accounts') }}">
                                {{ __('Marketplace Accounts') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('pricingSettings') }}">
                                {{ __('New Order Price Settings') }}
                            </a>
                            </li>

                            <li class="nav-item">
                            <a class="nav-link" href="{{ route('stateSettings') }}">
                                {{ __('States Disable For Sheet') }}
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
                                <a class="nav-link" href="{{ route('scaccounts') }}">
                                    {{ __('SyncCentric Accounts') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('informed') }}">
                                    {{ __('Informed Setting') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('informedaccounts') }}">
                                    {{ __('Informed Accounts') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('amazonsettings') }}">
                                    {{ __('Amazon Product Settings') }}
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('flags') }}">
                                    {{ __('Order Flags') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('reasons') }}">
                                    {{ __('Blacklist Reasons') }}
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('afManager') }}">
                                    {{ __('Auto-Fulfillment Manager') }}
                                </a>
                            </li>
                            

                            

                        </ul>
                    </div>
                </li>

                

                @endif
                
                <li class="nav-item">
                    <a class="nav-link active" href="#reports" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fa fa-file-medical-alt text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Reports') }}</span>
                    </a>

                    <div class="collapse show" id="reports">
                        <ul class="nav nav-sm flex-column">        
                        @if(auth()->user()->role==1 || (!empty(auth()->user()->assigned_pages) &&in_array(10,json_decode(auth()->user()->assigned_pages))))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('report') }}">
                                    {{ __('Report') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('duplicate-record') }}">
                                    Duplicate Record
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('product.report') }}">
                                    Product Report
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sold.report') }}">
                                    Sold Report
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sales.report') }}">
                                    Sale Report
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('purchase.report') }}">
                                    Purchase Report
                                </a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dailyReport') }}">
                                    Daily Report
                                </a>
                            </li>
                            
                            

                        @endif
                        </ul>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="#accounting" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="navbar-examples">
                        <i class="fas fa-calculator text-primary" ></i>
                        <span class="nav-link-text text-primary" >{{ __('Accounting') }}</span>
                    </a>

                    <div class="collapse show" id="accounting">
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