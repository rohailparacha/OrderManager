<!-- Top navbar -->
<nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="{{ route('home') }}">{{ __('Dashboard') }}</a>
        <!-- Form -->
        @if(request()->route()->getName()=='newOrders' ||request()->route()->getName()=='dueComing' ||request()->route()->getName()=='newOrdersFlagged'  ||request()->route()->getName()=='newOrdersExpensive' ||request()->route()->getName()=='processedtransactions' ||request()->route()->getName()=='transactions' ||request()->route()->getName()=='blacklist'  ||request()->route()->getName()=='returns'||request()->route()->getName()=='refunds'||request()->route()->getName()=='completed' || request()->route()->getName()=='processedOrders' || request()->route()->getName()=='cancelledOrders' || request()->route()->getName()=='report'   ||request()->route()->getName()=='conversions' ||request()->route()->getName()=='conversions2' ||request()->route()->getName()=='upsConversions'||request()->route()->getName()=='upsApproval'||request()->route()->getName()=='upsShipped'||request()->route()->getName()=='deliveredConversions' || request()->route()->getName()=='shippedOrders'|| request()->route()->getName()=='products' || request()->route()->getName()=='secondaryproducts' || request()->route()->getName()=='product.report' || request()->route()->getName()=='sold.report')
        <form method="post" action="search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

         @elseif(request()->route()->getName() =='duplicate-record')
        <form method="post" action="duplicate-search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='cindyprocessed'||request()->route()->getName()=='cindynew'||request()->route()->getName()=='cindybce'||request()->route()->getName()=='cindyreturn'||request()->route()->getName()=='cindycancel'||request()->route()->getName()=='cindyrefund'||request()->route()->getName()=='cindycompleted')
        <form method="post" action="cindysearch" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='vaughnprocessed'||request()->route()->getName()=='vaughnnew'||request()->route()->getName()=='vaughnbce'||request()->route()->getName()=='vaughnreturn'||request()->route()->getName()=='vaughncancel'||request()->route()->getName()=='vaughnrefund'||request()->route()->getName()=='vaughncompleted')
        <form method="post" action="vaughnsearch" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='jonathanprocessed'||request()->route()->getName()=='jonathannew'||request()->route()->getName()=='jonathanbce'||request()->route()->getName()=='jonathanreturn'||request()->route()->getName()=='jonathancancel'||request()->route()->getName()=='jonathanrefund'||request()->route()->getName()=='jonathancompleted')
        <form method="post" action="jonathansearch" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='jonathan2processed'||request()->route()->getName()=='jonathan2new'||request()->route()->getName()=='jonathan2bce'||request()->route()->getName()=='jonathan2return'||request()->route()->getName()=='jonathan2cancel'||request()->route()->getName()=='jonathan2refund'||request()->route()->getName()=='jonathan2completed')
        <form method="post" action="jonathan2search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='newOrdersMinus'||request()->route()->getName() =='newOrdersChecked'||request()->route()->getName() =='newOrdersMultiItems'||request()->route()->getName()=='newOrdersPrice1'||request()->route()->getName()=='newOrdersPrice2'||request()->route()->getName()=='newOrdersZero'||request()->route()->getName()=='newOrdersMovie'||request()->route()->getName()=='newOrdersFood'||request()->route()->getName()=='newOrdersExpensive')
        <form method="post" action="newSearch" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='yaballeprocessed'||request()->route()->getName()=='yaballenew'||request()->route()->getName()=='yaballebce'||request()->route()->getName()=='yaballereturn'||request()->route()->getName()=='yaballecancel'||request()->route()->getName()=='yaballerefund'||request()->route()->getName()=='yaballecompleted')
        <form method="post" action="yaballesearch" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>
        @elseif(request()->route()->getName() =='saleFreaks1processed'||request()->route()->getName()=='saleFreaks1new'||request()->route()->getName()=='saleFreaks1bce'||request()->route()->getName()=='saleFreaks1return'||request()->route()->getName()=='saleFreaks1cancel'||request()->route()->getName()=='saleFreaks1refund'||request()->route()->getName()=='saleFreaks1completed')
        <form method="post" action="saleFreaks1search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='saleFreaks2processed'||request()->route()->getName()=='saleFreaks2new'||request()->route()->getName()=='saleFreaks2bce'||request()->route()->getName()=='saleFreaks2return'||request()->route()->getName()=='saleFreaks2cancel'||request()->route()->getName()=='saleFreaks2refund'||request()->route()->getName()=='saleFreaks2completed')
        <form method="post" action="saleFreaks2search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='saleFreaks3processed'||request()->route()->getName()=='saleFreaks3new'||request()->route()->getName()=='saleFreaks3bce'||request()->route()->getName()=='saleFreaks3return'||request()->route()->getName()=='saleFreaks3cancel'||request()->route()->getName()=='saleFreaks3refund'||request()->route()->getName()=='saleFreaks3completed')
        <form method="post" action="saleFreaks3search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='saleFreaks4processed'||request()->route()->getName()=='saleFreaks4new'||request()->route()->getName()=='saleFreaks4bce'||request()->route()->getName()=='saleFreaks4return'||request()->route()->getName()=='saleFreaks4cancel'||request()->route()->getName()=='saleFreaks4refund'||request()->route()->getName()=='saleFreaks4completed')
        <form method="post" action="saleFreaks4search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName() =='saleFreaks5processed'||request()->route()->getName()=='saleFreaks5new'||request()->route()->getName()=='saleFreaks5bce'||request()->route()->getName()=='saleFreaks5return'||request()->route()->getName()=='saleFreaks5cancel'||request()->route()->getName()=='saleFreaks5refund'||request()->route()->getName()=='saleFreaks5completed')
        <form method="post" action="saleFreaks5search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">

        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" id="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>

        @elseif(request()->route()->getName()=='ebayProducts' || request()->route()->getName()=='walmartProducts')
        <form method="post" action="../search" class="navbar-search navbar-search-dark form-inline mr-3 d-none d-md-flex ml-lg-auto">
        @csrf
            <div class="form-group mb-0">
                <div class="input-group input-group-alternative">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input class="form-control" placeholder="Search" name="searchQuery" type="text">
                    <input class="form-control" name="route" type="hidden" value={{ request()->route()->getName()}}>
                </div>
            </div>
        </form>
        @endif
        <!-- User -->
        <ul class="navbar-nav align-items-center d-none d-md-flex">
            <li class="nav-item dropdown">
                <a class="nav-link pr-0" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <div class="media align-items-center">
                        <span class="avatar avatar-sm rounded-circle">
                            <img alt="Image placeholder" src="{{ asset('argon') }}/img/theme/default_avatar.png">
                        </span>
                        <div class="media-body ml-2 d-none d-lg-block">
                            <span class="mb-0 text-sm  font-weight-bold">{{ auth()->user()->name }}</span>
                        </div>
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
    </div>
</nav>