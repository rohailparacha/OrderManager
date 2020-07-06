@extends('layouts.app', ['title' => __('Gmail Integration Management')])

@section('content')
    @include('layouts.headers.cards')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style>
td.prodtd,th.prodth {
  white-space: normal !important; 
  word-wrap: break-word;  
  padding-left:1rem!important;
  padding-right:1rem!important;
}
th.prodth
{
    text-align: center;
}

.specifictd{
    text-align: center;
}
.prodtable {
  table-layout: fixed;
}

@media (min-width: 768px)
{
    .main-content .container-fluid
    {
        padding-right: 12px !important;
        padding-left: 12px !important;
    }
}

 

  

</style>

    <script>
var price = <?php echo json_encode($maxPrice); ?>;
var minAmount = <?php echo json_encode($minAmount); ?>;
var maxAmount = <?php echo json_encode($maxAmount); ?>;
$( function() {
  
  $( "#price-range" ).slider({
    range: true,
    min: 0,
    max: price,
    values: [ minAmount, maxAmount ],
    slide: function( event, ui ) {
      $( "#amount" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    }
  });

  $( "#amount" ).val( $( "#price-range" ).slider( "values", 0 ) +
    " - " + $( "#price-range" ).slider( "values", 1 ) );
} );

$(function() {
  $('input[name="dateRange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
});

$(document).ready(function(){ 
    
        $(document).on("click", "#export", function(){		

            try{
                var sellersFilter= "<?php echo $sellersFilter; ?>";                
                var amountFilter = "<?php echo $minAmount; ?>"+" - "+"<?php echo $maxAmount; ?>";
                var daterange = "<?php echo $dateRange; ?>";

            var query = {                
                            sellersFilter:sellersFilter,
                            daterange:daterange,
                            amountFilter:amountFilter
                        }


            var url = "/walmartproductexport?" + $.param(query)

            window.location = url;

            }
            catch{
                
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
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <h3 class="mb-0">{{ __('Walmart Products') }}</h3>
                            </div>
                            <div class="col-8" style="text-align:right; ">
                            @if(!empty($search) && $search==1)
                                    <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                            @endif  
                            </div> 
                              
                        </div>
                    </div>
                    
                    <div class="col-12">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Filters Section -->
                    <div class="row" style="padding-top:2%; margin-left:0px!important;">
                 <div class="col-12 text-center" id="filters">
                <form action="../walmartproductsfilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                    @csrf
                    <div style="width:100%; padding-bottom:2%;">
                        <div class="form-group">
                            
                            <div style="padding-right:3%;">
                                <select class="form-control" name="sellersFilter" style="margin-right:0%;width:160px;">
                                    <option value="0">Sellers</option>
                                    @foreach($sellers as $seller)
                                        <option value='{{$seller->seller}}' {{ isset($sellersFilter) && $seller->seller == "$sellersFilter"?"selected":"" }}>{{$seller->seller}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="padding-right:3%;">
                            <p id="price">
                                <label for="amount">Ebay Price Range</label>
                                <input  class="form-control"   style="width:160px;" type="text" name= "amountFilter" id="amount" readonly/>
                            </p>
                                <div id="price-range" style="width:160px;"></div>
                            </div>
                            
                            <div style="padding-right: 1%; float:right; width=160px; ">                                
                                <input class="form-control" type="text" name="dateRange" value="{{$dateRange ?? ''}}" />
                            </div>

                            <div>
                            
                            <input type="submit" value="Filter" class="btn btn-primary btn-md">
                            
                            <a id="export" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px;">Export</a>   
                            </div>

                                 
                        </div>
                    </div>
                    
                    

                </form>                  
                
            </div>

            
        </div>

                    <!-- End Filters Section -->
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="prodth">{{ __('Date/Time') }}</th>
                                    <th scope="col" class="prodth">{{ __('Image') }}</th>
                                    <th scope="col" class="prodth">{{ __('Title') }}</th>
                                    <th scope="col" class="prodth">{{ __('Attribute') }}</th>
                                    <th scope="col" class="prodth">{{ __('Attribute Value') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Seller Name') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Price') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Link') }}</th>                                                                         
                                    <th scope="col" class="prodth"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="prodtd">{{ date('m/d/Y H:i:s',strtotime($product->created_at)) }}</td>
                                        <td width="8%" class="prodtd"><img src="{{ $product->image }}" width="75px" height="75px"></td>
                                        <td class="prodtd">{{ $product->name }}</td>
                                        <td class="prodtd">{{$product->productIdType}}</td>
                                        <td class="prodtd">{{$product->productId}}</td>
                                        <td class="prodtd">{{$product->seller}}</td>
                                        <td class="prodtd">{{number_format((float)$product->price , 2, '.', '')}}</td>                                                               
                                        <td width="8%" class="specifictd prodtd"><a href="https://www.walmart.com/{{$product->link}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>

                                        <td class="text-right prodtd">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('walmartProductDelete', $product->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                         
                                                            @if(auth()->user()->role==1|| auth()->user()->role==2)
                                                            <button type="button" class="dropdown-item" onclick="confirm('{{ __("Are you sure you want to delete this product?") }}') ? this.parentElement.submit() : ''">
                                                                {{ __('Delete') }}
                                                            </button>
                                                            @endif
                                                        </form>                                                       
                                                </div>
                                            </div>
                                        </td>
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="padding-right:2%">
                    <div class="col-md-4 offset-md-8" style="text-align:right">
                        <span>Showing {{$products->toArray()['from']}} - {{$products->toArray()['to']}} of {{$products->toArray()['total']}} records</span>        
                    </div>
                  
                    </div>

                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{ $products->links() }}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            

        @include('layouts.footers.auth')
    </div>
@endsection