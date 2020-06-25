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
 
  
        $('#btnAddCat').on('click',function(event){ 
            $('#addCat').modal('show');  
            $('#editTitle').hide();
            $('#modal-que-edit').hide(); 
            $('#addTitle').show();
            $('#modal-que-save').show();
            $('#editSuccess').hide();
            $('#addSuccess').hide();
            $('#error').hide(); 
            $('#skuTbx').val('');
            $('#nameTbx').val('');
            $('#idTbx').val('');
            $('#descTbx').val('');
            $('#brandTbx').val('');
            $('#primaryTbx').val('');
            $('#secondaryTbx').val('');
            $('#priceTbx').val('');        
            $('#idtypeTbx').val(0);
            $('#accountTbx').val(0);
            $('#strategyTbx').val(0);
            $('#categoryTbx').val(0);
            $('.print-error-msg').hide();
            $('#skuTbx').attr('readonly',false);
        });

       
        
        $('#addCat').on('show.bs.modal', function(e) {    
            var link     = $(e.relatedTarget),
            id = link.data("id");
            var data;

            $.ajax({
                
                type: 'get',
                url: '/getProduct/'+id,
                data: {
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $('#error').hide();            
                    var obj = JSON.parse(data);
                    console.log(data);
                    $('#catId').val(obj.id);            
                    $('#skuTbx').val(obj.sku);
                    $('#nameTbx').val(obj.name);
                    $('#idTbx').val(obj.productId);
                    $('#descTbx').val(obj.description);
                    $('#brandTbx').val(obj.brand);
                    $('#primaryTbx').val(obj.primaryImg);
                    $('#secondaryTbx').val(obj.secondaryImg);
                    $('#priceTbx').val(obj.ebayPrice);     
                    var val=0;
                    if(obj.productIdType=='UPC')   
                        val = 1;
                    else if(obj.productIdType=='EAN')   
                        val = 2; 
                    else if(obj.productIdType=='ISBN')   
                        val = 3; 
                    else if(obj.productIdType=='GTIN')   
                        val = 4; 
                    
                    $('#idtypeTbx').val(val);
                    $('#accountTbx').val(obj.account_id);
                    $('#strategyTbx').val(obj.strategy_id);
                    $('#categoryTbx').val(obj.category_id);


                    $('#addTitle').hide();
                    $('#modal-que-save').hide();  
                    $('#editTitle').show();
                    $('#modal-que-edit').show();  
                    $('#editSuccess').hide();
                    $('#addSuccess').hide();    
                    $('.print-error-msg').hide();
                    $('#skuTbx').attr('readonly',true);
                },
                
                error: function(XMLHttpRequest, textStatus, errorThrown) {                
                    
                }        
            });

            
           
    
    });

        $('#modal-que-save').on('click',function(event){                       
            var sku = $('#skuTbx').val();
            var name = $('#nameTbx').val();
            var id = $('#idTbx').val();
            var desc = $('#descTbx').val();
            var brand=  $('#brandTbx').val();
            var primaryImg = $('#primaryTbx').val();
            var secondaryImg = $('#secondaryTbx').val();
            var price = $('#priceTbx').val();        
            var idType = $('#idtypeTbx option:selected').val();
            var account = $('#accountTbx option:selected').val();
            var strategy = $('#strategyTbx option:selected').val();
            var category = $('#categoryTbx option:selected').val();

           
            $.ajax({
                
            type: 'post',
            url: '/addEbayProduct',
            data: {
            'sku': sku,
            'name':name,
            'id' : id,
            'desc':desc,
            'brand':brand,
            'primaryImg':primaryImg,
            'secondaryImg':secondaryImg,
            'price':price,
            'idType':idType,
            'strategy':strategy,
            'category':category,
            'account' : account
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
                $("#addSuccess").show().delay(3000).fadeOut();
            } else
                printErrorMsg(data.error);
            },
            
            error: function(XMLHttpRequest, textStatus, errorThrown) {                
                $('#error').show();
            }        
        });
        })

        function printErrorMsg (msg) {
            $(".print-error-msg").find("ul").html('');
            $(".print-error-msg").css('display','block');
            $.each( msg, function( key, value ) {
                $(".print-error-msg").find("ul").append('<li>'+value+'</li>');
            });
        }

        $('#modal-que-edit').on('click',function(event){                       
            var pid = $('#catId').val();
            var sku = $('#skuTbx').val();
            var name = $('#nameTbx').val();
            var id = $('#idTbx').val();
            var desc = $('#descTbx').val();
            var brand=  $('#brandTbx').val();
            var primaryImg = $('#primaryTbx').val();
            var secondaryImg = $('#secondaryTbx').val();
            var price = $('#priceTbx').val();        
            var idType = $('#idtypeTbx option:selected').val();
            var account = $('#accountTbx option:selected').val();
            var strategy = $('#strategyTbx option:selected').val();
            var category = $('#categoryTbx option:selected').val();



            $.ajax({
                
            type: 'post',
            url: '/updateEbayProduct',
            data: {
            'pid':pid,
            'sku': sku,
            'name':name,
            'id' : id,
            'desc':desc,
            'brand':brand,
            'primaryImg':primaryImg,
            'secondaryImg':secondaryImg,
            'price':price,
            'idType':idType,
            'strategy':strategy,
            'category':category,
            'account': account
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
                $("#addSuccess").show().delay(3000).fadeOut();
            } else
                printErrorMsg(data.error);
            },
            
            error: function(XMLHttpRequest, textStatus, errorThrown) {                
                $('#error').show();
            }        
        });
        })
    
        $(document).on("click", "#export", function(){		

            try{
                var categoryFilter= "<?php echo $categoryFilter; ?>";
                var strategyFilter = "<?php echo $strategyFilter; ?>";
                var amountFilter = "<?php echo $minAmount; ?>"+" - "+"<?php echo $maxAmount; ?>";
                var daterange = "<?php echo $dateRange; ?>";

            var query = {                
                            categoryFilter:categoryFilter,
                            strategyFilter:strategyFilter,
                            daterange:daterange,
                            amountFilter:amountFilter
                        }


            var url = "/ebayproductexport?" + $.param(query)

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
                                <h3 class="mb-0">{{ __('eBay Products') }}</h3>
                            </div>
                            <div class="col-8" style="float:right; ">
                            <form class="form-inline" action="/ebayupload" method="post" enctype="multipart/form-data" style="float:right;">
                            {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="file" class="form-control" name="file" />                
                            
                                    <input type="submit" class="btn btn-primary" value="Import" style="margin-left:10px;"/>
                                    <input type="button" id="btnAddCat" class="btn btn-primary" value="Add Product"/>      
                                    @if(!empty($search) && $search==1)
                                    <a href="{{ route($route) }}"class="btn btn-primary btn-md">Go Back</a>
                                    @endif                               
                                </div>
                            
                            </form>
                            
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
                <form action="../ebayproductsfilter" class="navbar-search navbar-search-light form-inline" style="width:100%" method="post">
                    @csrf
                    <div style="width:100%; padding-bottom:2%;">
                        <div class="form-group">
                            <div style="padding-right:3%;">
                                <select class="form-control" name="categoryFilter" style="margin-right:0%;width:160px;">
                                    <option value="0">Category</option>
                                    @foreach($categories as $category)
                                        <option value='{{$category->id}}' {{ isset($categoryFilter) && $category->id == $categoryFilter?"selected":"" }}>{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>                       
                            
                        
                            <div style="padding-right:3%;">
                                <select class="form-control" name="strategyFilter" style="margin-right:0%;width:160px;">
                                    <option value="0">Strategy</option>
                                    @foreach($strategies as $strategy)
                                        <option value='{{$strategy->id}}' {{ isset($strategyFilter) && $strategy->id == $strategyFilter?"selected":"" }}>{{$strategy->code}}</option>
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
                            
                            <a href="./ebaytemplate" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:10px; margin-bottom:20px; ">Template File</a>   
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
                                    <th scope="col" class="prodth">{{ __('SKU') }}</th>
                                    <th scope="col" class="prodth">{{ __('Title') }}</th>
                                    <th scope="col" class="prodth">{{ __('Store Name') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Product Id Type') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Product ID') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Category') }}</th>                                        
                                    <th scope="col" class="prodth">{{ __('Pricing Strategy') }}</th>   
                                    <th scope="col" class="prodth">{{ __('eBay Price') }}</th>  
                                    <th scope="col" class="prodth">{{ __('Our Price') }}</th>   
                                    <th scope="col" class="prodth">{{ __('Link') }}</th>                                                                         
                                    <th scope="col" class="prodth"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td class="prodtd">{{ date('m/d/Y H:i:s',strtotime('+14 hours',strtotime($product->created_at))) }}</td>
                                        <td width="8%" class="prodtd"><img src="{{ $product->primaryImg }}" width="75px" height="75px"></td>
                                        <td class="prodtd">{{ $product->sku }}</td>
                                        <td class="prodtd">{{ $product->name }}</td>
                                        <td class="prodtd">{{$accountArr[$product->account_id]}}</td>
                                        <td class="prodtd">{{$product->productIdType}}</td>
                                        <td class="prodtd">{{$product->productId}}</td>
                                        <td class="prodtd">{{$categoryArr[$product->category_id]}}</td>                                    
                                        <td class="prodtd">{{$strategyArr[$product->strategy_id]}}</td>
                                        <td class="prodtd">{{number_format((float)$product->ebayPrice , 2, '.', '')}}</td>
                                        <td class="prodtd">{{number_format((float)$product->price , 2, '.', '')}}</td>                                                               
                                        <td width="8%" class="specifictd prodtd"><a href="https://www.ebay.com/itm/{{$product->sku}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>

                                        <td class="text-right prodtd">
                                            <div class="dropdown">
                                                <a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </a>
                                                <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">                                    
                                                        <form action="{{ route('ebayProductDelete', $product->id) }}" method="post">
                                                            @csrf
                                                            @method('delete')                                                                                                                                                         
                                                            <a class="dropdown-item"  data-toggle="modal" data-target="#addCat" data-id="{{$product->id}}" id="btnEditCat" href="#">{{ __('Edit') }}</a>
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
            
<!-- Add Question Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="addCat">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
      @lang('Some fields are incorrect or missing below:')
       </div>
       <div class="alert alert-success" id="addSuccess" style="display:none">
               @lang('Product Added Successfully')
       </div>   
       <div class="alert alert-success" id="editSuccess" style="display:none">
               @lang('Product Updated Successfully')
       </div>   
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Add New Product')</h4>
            <h4 class="modal-title" id="editTitle">@lang('Update Product')</h4>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />

            <div class="alert alert-danger print-error-msg" style="display:none">
                <ul></ul>
            </div>
   <form class="form-horizontal" method="post" >
{{csrf_field()}}
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Account:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="accountTbx" style="">                                
                                                        <option value=0>Select Account</option>
                                                      @foreach($accounts as $account)
                                                      <option value={{$account->id}}>{{$account->store}}</option>
                                                      @endforeach                                         
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('SKU:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="email" class="form-control" id="skuTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>


<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Product Name:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="nameTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Product Id Type:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="idtypeTbx" style="">                                
                                                        <option value=0>Select Type</option>
                                                        <option value=1>UPC</option>
                                                        <option value=2>EAN</option>
                                                        <option value=3>ISBN</option>
                                                        <option value=4>GTIN</option>                                                    
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Product Id:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="idTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Description:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <textarea type="textarea"  rows="4" cols="100" class="form-control" id="descTbx" name="category" required></textarea>
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Brand:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="brandTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Primary Image:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="primaryTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Secondary Image:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="secondaryTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('eBay Price:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="priceTbx" name="category" required>                                        
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Pricing Strategy:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="strategyTbx" style="">                                
                                <option value=0>Select Strategy</option>
                                @foreach($strategies as $strategy)
                                <option value={{$strategy->id}}>{{$strategy->code}}</option>
                                @endforeach                                                        
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>

<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Category:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                    <select class="form-control" id="categoryTbx" style="">                                
                                <option value=0>Select Category</option>
                                @foreach($categories as $cat)
                                <option value={{$cat->id}}>{{$cat->name}}</option>
                                @endforeach                                                        
                    </select>  
                   </div>
                    
               </div>
           </div>
       </div>
</div>



       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-que-save">@lang('Add Product')</button>
        <button type="button" class="btn btn-primary" id="modal-que-edit">@lang('Edit Product')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Add Question Modal End -->
        @include('layouts.footers.auth')
    </div>
@endsection