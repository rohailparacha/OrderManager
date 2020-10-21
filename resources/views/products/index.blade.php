@extends('layouts.app', ['title' => __('New Orders')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\productsController')
<style>
td,th {
  white-space: normal !important; 
  word-wrap: break-word;  
  padding-left:1rem!important;
  padding-right:1rem!important;
}
th
{
    text-align: center;
}

.specifictd{
    text-align: center;
}
table {
  table-layout: fixed;
}

.btn-sm{
    font-size:0.65rem;
}

@media (min-width: 768px)
{
    .main-content .container-fluid
    {
        padding-right: 6px !important;
        padding-left: 6px !important;
    }
}

 
#form
 { width: 400px}
  

</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script>

$(document).ready(function(){
        
        $('#addCat').on('show.bs.modal', function(e) {        
        
        var link     = $(e.relatedTarget),
        id = link.data("id"),        
        title = link.data('title')    
        ;

        $('#catId').val(id);
        $('#titleTbx').val(title);
        $('#editTitle').show();
        $('#product-edit').show();  
        $('#editSuccess').hide();
        
    
    });

    $('#product-edit').on('click',function(event){ 
           
           var title = $('#titleTbx').val();
           var id = $('#catId').val();

           $.ajax({
               
           type: 'post',
           url: '/editAmzProduct',
           data: {
           'title': title,
           'id': id
           },
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
           success: function (data) {
           console.log(data);
           if (data == 'success') {
               $('#add-modal').modal('hide');
               $('#error').hide();
               document.location.reload();
               $("#editSuccess").show().delay(3000).fadeOut();
           } else
               $('#error').show();
           },
           
           error: function(XMLHttpRequest, textStatus, errorThrown) {                
               $('#error').show();
           }        
       });
       })

    var price = <?php echo json_encode($maxPrice); ?>;
    var sellers = <?php echo json_encode($maxSellers); ?>;


    var minSeller = <?php echo json_encode($minSeller); ?>;
    var maxSeller = <?php echo json_encode($maxSeller); ?>;
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

$( function() {
  
  $( "#sellers-range" ).slider({
    range: true,
    min: 0,
    max: sellers,
    values: [ minSeller, maxSeller ],
    slide: function( event, ui ) {
      $( "#sellers" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
    }
  });

  $( "#sellers" ).val( $( "#sellers-range" ).slider( "values", 0 ) +
    " - " + $( "#sellers-range" ).slider( "values", 1 ) );
} );

$(document).on("click", "#export", function(){		

try{
    var accountFilter = "<?php echo $accountFilter; ?>";
    var strategyFilter = "<?php echo $strategyFilter; ?>";
    var amountFilter = "<?php echo $minAmount; ?>"+" - "+"<?php echo $maxAmount; ?>";
    var sellerFilter = "<?php echo $minSeller; ?>"+" - "+"<?php echo $maxSeller; ?>";    

var query = {                
                accountFilter:accountFilter,
                strategyFilter:strategyFilter,
                sellerFilter:sellerFilter,
                amountFilter:amountFilter
            }


var url = "/productexport?" + $.param(query)

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

        @if(Session::has('count_msg'))
        <div class="alert alert-info"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>{{Session::get('count_msg')}}</div>
        @endif

      
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Amazon Primary Products') }}</h3>
                            </div>                              
                            
                        </div>

                        <div class="row align-items-center pt-5 pb-3" >
                            <div class="col-4">
                            <form class="form-inline" action="/manualReprice" method="post" enctype="multipart/form-data" style="float:left;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file"  style="width:250px!important"/>                
                            
                                    <input type="submit" class="btn btn-primary" value="Repricing" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>
                            </div>  

                            <div class="col-4" style="float:right; ">
                            <form class="form-inline" action="/upload" method="post" enctype="multipart/form-data" style="float:right;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file" style="width:225px!important"/>                
                            
                                    <input type="submit" class="btn btn-primary" value="Add Products" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>
                            
                            </div> 

                            <div class="col-4" style="float:right; ">
                            <form class="form-inline" action="/deleteProducts" method="post" enctype="multipart/form-data" style="float:right;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file" style="width:225px!important" />                
                            
                                    <input type="submit" class="btn btn-primary" value="Delete Products" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>
                            
                            </div> 
                            
                        </div>

                        <div class="row align-items-center" style="padding-top:2%;">                            
                        <a href="./repTemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">Repricing Template</a>   
                        <a href="./addTemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">Add Products Template</a>   
                        <a href="./delTemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">Delete Products Template</a>            
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
                    <div class="row" style="margin-left:0px!important;">
                 <div class="col-12 text-center" id="filters">
                <form action="productsfilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                    @csrf
                    <div style="width:100%; padding-bottom:2%;">
                        <div class="form-group">
                            <div style="padding-right:3%;">
                                <select class="form-control" name="accountFilter" style="margin-right:0%;width:200px;">
                                    <option value="0">Account</option>
                                    @foreach($accounts as $account)
                                        <option value='{{$account->id}}' {{ isset($accountFilter) && $account->id == $accountFilter?"selected":"" }}>{{$account->store}}</option>
                                    @endforeach
                                </select>
                            </div>


                        <div style="padding-right:3%;">
                            <p id="fba">
                                <label for="sellers">FBA Sellers</label>
                                <input  class="form-control" style="width:200px;" type="text" name="sellerFilter" id="sellers" readonly/>
                            </p>
                                <div id="sellers-range" style="width:200px;"></div>
                        </div>
                            
                            
                            <div style="padding-right:3%;">
                                <select class="form-control" name="strategyFilter" style="margin-right:0%;width:200px;">
                                    <option value="0">Strategy</option>
                                    @foreach($strategies as $strategy)
                                        <option value='{{$strategy->id}}' {{ isset($strategyFilter) && $strategy->id == $strategyFilter?"selected":"" }}>{{$strategy->code}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div style="padding-right:3%;">
                            <p id="price">
                                <label for="amount">Price Range</label>
                                <input  class="form-control"   style="width:200px;" type="text" name= "amountFilter" id="amount" readonly/>
                            </p>
                                <div id="price-range" style="width:200px;"></div>
                            </div>
                            

                            <div>
                            
                            <input type="submit" value="Filter" class="btn btn-primary btn-md">
                            <a id="export" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px;">Export</a>   
                            
                            </div>                            

                                 
                        </div>
                    </div>
                    
                    
                    <p style="float:right;padding-left:80%;">Last Run:
                    {{ $provider::getIranTime(date_format(date_create($last_run), 'm/d/Y H:i:s')) }}                                        
                    </p>
                </form>  

                
                <a href="./repricing" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px; margin-bottom:20px; ">Repricing</a>   
                <a href="./template" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px; margin-bottom:20px; ">Template File</a>   
                  
                <form method="post" style="float:right;" class="form-inline" action="getfile" autocomplete="off">
                            @csrf
                                                        
                            <div class="form-group">
                                        <select class="form-control" name="range" style="">                                
                                                    <option value=0>Select Range</option>
                                                    @for ($i = 1; $i <= $products->toArray()['total']; $i = $i + 100000)
                                                        @if($i+100000>$products->toArray()['total'])
                                                        <option value="{{ $i }}">{{ $i }} - {{$products->toArray()['total']}}</option>
                                                        @else
                                                        <option value="{{ $i }}">{{ $i }} - {{$i+99999}}</option>
                                                        @endif
                                                    @endfor
                                                                                                                                                       
                                        </select>                                    
                                    
                                        @error('role')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>
                               
                                <div class="form-group text-center" style="float:right; margin-top:-27px; padding-left:5px;">
                                    <button type="submit" class="btn btn-primary mt-4">{{ __('Download Products') }}</button>
                                </div>                            
                </form>
                
                <form method="post" style="float:right; padding-right:30px;" class="form-inline" action="exportAsins" autocomplete="off">
                            @csrf
                                                        
                            <div class="form-group">
                                        <select class="form-control" name="range" style="">                                
                                                    <option value=0>Select Range</option>
                                                    @for ($i = 1; $i <= $products->toArray()['total']; $i = $i + 20000)
                                                        @if($i+20000>$products->toArray()['total'])
                                                        <option value="{{ $i }}">{{ $i }} - {{$products->toArray()['total']}}</option>
                                                        @else
                                                        <option value="{{ $i }}">{{ $i }} - {{$i+19999}}</option>
                                                        @endif
                                                    @endfor
                                                                                                                                                       
                                        </select>                                    
                                    
                                        @error('role')
                                            <div class="error" style="color:red;">{{ $message }}</div>
                                         @enderror
                                </div>
                               
                                <div class="form-group text-center" style="float:right; margin-top:-27px; padding-left:5px;">
                                    <button type="submit" class="btn btn-success mt-4">{{ __('Export ASINs') }}</button>
                                </div>                            
                </form>
                
                @if(!empty($search) && $search==1)
                    <a href="{{ route($route) }}"class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px; margin-bottom:20px; ">Go Back</a>
                @endif 
                <div>        
                    
                </div>             
                
            </div>

            
        </div>

        <div class="row" style="padding-top:2%; padding-bottom:2%;">
        
        <div class="col-md-6 offset-md-6">
                <a href="./wmtemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">WM Template</a> 
                            <form class="form-inline" action="/uploadwm" method="post" enctype="multipart/form-data" style="float:right;">
                            {{ csrf_field() }}
                                <div class="form-group">

                                    <input type="hidden" value="1" name="route" />

                                    <input type="file" class="form-control" name="file" />                
                            
                                    <input type="submit" class="btn btn-primary" value="Import WM" style="margin-left:10px;"/>
                                   
                                </div>
                            
                            </form>
                </div>
        </div>
                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" width="8%" >{{ __('Image') }}</th>
                                    <th scope="col" width="8%" >{{ __('WM Image') }}</th>
                                    <th scope="col" width="9%" >{{ __('Created Date') }}</th>
                                    <th scope="col" width="7%">{{ __('Store Name') }}</th>
                                    <th scope="col" width="9%">{{ __('ASIN') }}</th>
                                    
                                    <th scope="col" width="9%">{{ __('UPC') }}</th>
                                    <th scope="col" width="9%">{{ __('WM ID') }}</th>
                                    
                                    <th scope="col" width="18%">{{ __('Title') }}</th>                                    
                                    <th scope="col" width="8%">{{ __('Primary/Secondary') }}</th>
                                    <th scope="col" width="8%">{{ __('30-Day Sold') }}</th>                                    
                                    <th scope="col" width="8%">{{ __('Price') }}</th>                                    
                                    <th scope="col" width="8%">{{ __('WM Link') }}</th>    
                                    <th scope="col" width="8%">{{ __('Link') }}</th>                                    
                                    <th scope="col" width="6%">{{ __('Action') }}</th>
                                    <th scope="col" width="3%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>                                                                             
                                        <td width="8%"><img src="{{ $product->image }}" width="75px" height="75px"></td>
                                        <td width="8%">
                                        @if(!empty($product->wmimage))
                                            <img src="{{ $product->wmimage }}" width="75px" height="75px">
                                        @endif
                                        </td>
                                        <td width="9%">{{ date_format(date_create($product->created_at), 'm/d/Y') }}</td> 
                                        <td width="8%" class="specifictd">{{ $product->account }}</td>
                                        <td width="9%" class="specifictd">{{ $product->asin }}</td>
                                        <td width="9%" class="specifictd">{{ $product->upc }}</td>
                                        <td width="9%" class="specifictd">{{ $product->wmid }}</td>
                                        <td width="20%">{{ $product->title }}</td>             
                                        <td width="20%">
                                            {{$product->isPrimary}}
                                        </td>                                        
                                        <td width="8%"  class="specifictd"><a href="./productReport/orders?asin={{$product->asin}}&status=sold">{{ $product->{'30days'} }}</a></td>                                        
                                        <td width="8%" class="specifictd">{{ number_format((float)$product->price, 2, '.', '') }}</td>                                        
                                        <td width="9%" class="specifictd">
                                        <a href="https://www.walmart.com/ip/{{ $product->wmid }}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a>
                                        </td>
                                        <td width="8%" class="specifictd"><a href="https://amazon.com/dp/{{$product->asin}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>
                                        <td width="6%" class="specifictd">
                                        <a class="btn btn-primary btn-sm" href="deleteProduct/{{$product->id}}" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                        
                                        </td>
                                        <td width="4%" class="text-right">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                    <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-title= "{{$product->title}}" data-id="{{$product->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
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
                            {{$products->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>

  <!-- Edit Product Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="addCat">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
      @lang('Title is either empty or has invalid characters')
       </div>
       
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Product Title Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">            
            <h4 class="modal-title" id="editTitle">@lang('Update Product Title')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Title:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="titleTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>

<br/>
       
   </form>
      </div>
       <div class="modal-footer">        
        <button type="button" class="btn btn-primary" id="product-edit">@lang('Edit Product')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Edit Product Modal End -->          
        @include('layouts.footers.auth')
    </div>
@endsection