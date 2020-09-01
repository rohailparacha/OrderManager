@extends('layouts.app', ['title' => __('Order Details')])

@section('content')
@include('layouts.headers.cards')
@inject('provider', 'App\Http\Controllers\orderController')
<style>
.col-md-3{
    max-width: 30%;
    flex: 0 0 30%;
    padding:2px!important;
}
.col-md-6{
    max-width: 40%;
    flex: 0 0 40%;
    padding:2px!important;
}

td,th {
  white-space: normal !important; 
  word-wrap: break-word;  
}
table {
  table-layout: fixed;
}

th
{
    text-align: center;
}

.specifictd{
    text-align: center;
}

</style>

<style>
td,th {
  white-space: normal !important; 
  word-wrap: break-word;
  padding-left:1rem!important;
  padding-right:1rem!important;  
}
table {
  table-layout: fixed;
}



@media (min-width: 768px)
{
    .main-content .container-fluid
    {
        padding-right: 12px !important;
        padding-left: 12px !important;
    }

    .container{
        margin:0px; 
        max-width:1250px;
    }
}
</style>

<script>

$(document).ready(function(){
 
  
 $('#btnCancel').on('click',function(event){
    $('#error').hide(); 
    $('#checkPass').modal('show');  
    $('#passTbx').val("");
 });

$('#btnBce').on('click',function(event){
    $('#bce').modal('show');
    $('#emptyTrackingBce').hide(); 
    $('#emptyCarrierBce').hide(); 
    $('#emptyShippingBce').hide();  
    $('#bceInput').hide(); 
    
});

 $('#updateBtn').on('click',function(event){
    var order = <?php echo json_encode($order); ?>;
    $('#error').hide(); 
    $('#modal-process').hide(); 
    $('#modal-edit').show(); 
    $('#process').modal('show');  
    $('#poTbx').val(order.poNumber);
    $('#amountTbx').val(order.poTotalAmount);   
    $('#accountTbx').val(order.account_id);   
    $('#emptyPO').hide(); 
    $('#emptyAmount').hide();  
    $('#emptyAccount').hide(); 
    $('#charcountError').hide();
 });

 

 $('#btnProcess').on('click',function(event){
    $('#error').hide(); 
    $('#modal-process').show(); 
    $('#modal-edit').hide(); 
    $('#process').modal('show');  
    $('#poTbx').val("");
    $('#amountTbx').val("");
    $('#accountTbx').val(0);
    $('#emptyPO').hide(); 
    $('#emptyAmount').hide(); 
    $('#emptyAccount').hide(); 
    $('#charcountError').hide();
 });

 $('#btnShip').on('click',function(event){
    $('#errorShipping').hide(); 
    $('#dupIssue').hide(); 
    $('#errorCarrier').hide(); 
    $('#modal-ship').show(); 
    $('#modal-edit').hide(); 
    $('#ship').modal('show');  
    $('#carrierTbx').val(0);
    $('#trackingTbx').val("");
 });

 $('#updateShip').on('click',function(event){
    var order = <?php echo json_encode($order); ?>;
    $('#errorShipping').hide(); 
    $('#dupIssue').hide(); 
    $('#errorCarrier').hide(); 
    $('#modal-ship').hide(); 
    $('#modal-ship-edit').show(); 
    $('#ship').modal('show');      
    $('#carrierTbx').val(order.carrierName);
    if(order.upsTrackingNumber=='')
        $('#trackingTbx').val(order.trackingNumber);
    else
        $('#trackingTbx').val(order.upsTrackingNumber);
 });

 $('#modal-ship-edit').on('click',function(event){ 
    
    var carrier = $('#carrierTbx').val();           
    
    var tracking = $('#trackingTbx').val();
    
    var id = $('#idTbx').val();  
        


    if(tracking.trim()=='' ||  carrier==0)
    {        
        if(tracking.trim()=='')
        {
            $('#emptyTracking').show(); 
            
        }
        else
        {
            $('#emptyTracking').hide(); 
        }
       
       if(carrier==0)
       {
            $('#emptyCarrier').show(); 
            
        }
        else
        {
            $('#emptyCarrier').hide(); 
        }

       
        return;
    }
    
    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'carrier': carrier.trim(),
               'id' : id,
               'tracking' : tracking.trim(),
               'status': 'update',
               'type':'ship'
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#errorShipping').hide();  
                   $('#dupIssue').hide(); 
                   document.location.reload();                       
               } 
               else if(data.startsWith("Tracking Number"))
               {
                    $('#dupIssue').text(data);
                    $('#dupIssue').show(); 
               }
               else
               {                                
                $('#errorShipping').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#errorShipping').show();
               }        
           });


 });

 $('#modal-edit').on('click',function(event){ 
    var amount = $('#amountTbx').val();           
    var po = $('#poTbx').val();
    var id = $('#idTbx').val();  
    var account = $('#accountTbx').val(); 
    
    if(po.trim()=='' || amount.trim()=='' || account==0)
    {        
        
        if(po.trim()=='')
        {
            $('#emptyPO').show(); 
            
        }
        else
        {
            $('#emptyPO').hide(); 
        }

        if(amount.trim()=='')
        {
            $('#emptyAmount').show(); 
        }
        else
        {
            $('#emptyAmount').hide(); 
        }

        
        if(account==0)
        {
            $('#emptyAccount').show(); 
        }
        else
        {
            $('#emptyAccount').hide(); 
        }
        

        return;
    }

    if(!isNaN(account))
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=19 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }

    if(account=='Cindy')
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=19 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }

    if(account=='ebay')
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=14 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }

    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'amount': amount.trim(),
               'id' : id.trim(),
               'po' : po.trim(),
               'status': 'update',
               'type':'process',
               'account':account.trim()
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#error2').hide();  
                   document.location.reload();                       
               } 
               else
               {                                
                $('#error2').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#error2').show();
               }        
           });


 });

 $('#btnConvert').on('click',function(event){ 
    var shipmentId = $('#shipmentTbxBce').val();

    var itemId = $('#itemTbxBce').val();
    
    var carrier = 'Amazon';        
    
    var tracking = $('#trackingTbxBce').val();
    
    var id = $('#idTbxBce').val();  
        


    if(tracking.trim()=='' ||  carrier==0 || (shipmentId.trim()=='' && itemId.trim()==''))
    {        
        if(tracking.trim()=='')
        {
            $('#emptyTrackingBce').show(); 
            
        }
        else
        {
            $('#emptyTrackingBce').hide(); 
        }
       
       if(carrier==0)
       {
            $('#emptyCarrierBce').show(); 
            
        }
        else
        {
            $('#emptyCarrierBce').hide(); 
        }

               
    
        if( shipmentId.trim()=='' && itemId.trim()=='')
        {
            $('#emptyShippingBce').show(); 
            
        }
        else
        {
            $('#emptyShippingBce').hide(); 
        }

        
        return;
    }

    
   
    $.ajax({               
               type: 'post',
               url: '/getManualBce',
               data: {
               'orderId': id,
               'shipmentId' : shipmentId.trim(),
               'itemId' : itemId.trim(),
               'trackingNumber' : tracking.trim(),
               'channel':'Walmart',
               'carrier':carrier.trim()
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },

               beforeSend: function(){
                    // Show image container
                    $("#loader").show();
                    $('#btnConvert').attr('disabled',true);
                },


               success: function (data) {
               console.log(data);
               if (data != 'Error') {
                   
                   $('#error2Bce').hide();  
                   $('#bceInput').val('Bluecare Express Tracking Number: '+data);
                   $('#bceInput').show();
                   $('#btnConvert').hide();
                   $('#btnBce').hide(); 
               } 
               else
               {                                
                   $('#bceInput').val('We cannot convert tracking number at this time');
                   $('#bceInput').show();
               }
             
               },               
                complete:function(data){
                    // Hide image container
                    $("#loader").hide();                    
                },
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#error2Bce').show();
               }        
           });

           
           

 });




 $('#modal-ship').on('click',function(event){ 
    var carrier = $('#carrierTbx').val();           
    var tracking = $('#trackingTbx').val();
    var id = $('#idTbx').val();  

    if(tracking.trim()=='' ||  carrier==0)
    {        
        if(tracking.trim()=='')
        {
            $('#emptyTracking').show(); 
            
        }
        else
        {
            $('#emptyTracking').hide(); 
        }
       
       if(carrier==0)
       {
            $('#emptyCarrier').show(); 
            
        }
        else
        {
            $('#emptyCarrier').hide(); 
        }

        return;
    }
  
    
    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'carrier': carrier.trim(),
               'id' : id.trim(),
               'tracking' : tracking.trim(),
               'status': 'new',
               'type':'ship'
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#errorShipping').hide();  
                   $('#dupIssue').hide();
                   document.location.reload();                       
               } 
               else if(data.startsWith("Tracking Number"))
               {                   
                    $('#dupIssue').text(data);
                    $('#dupIssue').show(); 
               }
               else
               {                                
                $('#errorShipping').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#errorShipping').show();
               }        
           });


 });

function onlyUnique(value, index, self) { 
    return self.indexOf(value) === index;
}

 $('#modal-process').on('click',function(event){ 
    var amount = $('#amountTbx').val();           
    var po = $('#poTbx').val();
    var id = $('#idTbx').val();  
    var account = $('#accountTbx').val(); 
    
    console.log(account);

    if(po.trim()=='' || amount.trim()=='' || account==0)
    {        
        if(po.trim()=='')
        {
            $('#emptyPO').show(); 
            
        }
        else
        {
            $('#emptyPO').hide(); 
        }

        if(amount.trim()=='')
        {
            $('#emptyAmount').show(); 
        }
        else
        {
            $('#emptyAmount').hide(); 
        }

        if(account==0)
        {
            $('#emptyAccount').show(); 
        }
        else
        {
            $('#emptyAccount').hide(); 
        }

        return;
    }

    if(!isNaN(account))
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=19 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }

    if(account=='Cindy')
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=19 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }
    
    if(account=='ebay')
    {        
        var n = po.trim().includes(",");
        if(po.trim().length!=14 && n==false)
        {
            $('#charcountError').show();
            return;
        }                
    }

    $.ajax({               
               type: 'post',
               url: '/updateOrder',
               data: {
               'amount': amount.trim(),
               'id' : id.trim(),
               'po' : po.trim(),
               'status': 'new',
               'type':'process',
               'account':account.trim()
               },
               headers: {
                   'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
               },
               success: function (data) {
               console.log(data);
               if (data == 'success') {
                   $('#process').modal('hide');
                   $('#error2').hide();  
                   document.location.reload();                       
               } 
               else
               {                                
                $('#error2').show();
               }
             
               },
               
               error: function(XMLHttpRequest, textStatus, errorThrown) {                
                   $('#error2').show();
               }        
           });


 });

 $('#modal-confirm-password').on('click',function(event){            
           var pass = $('#passTbx').val();           
           var id = $('#idTbx').val();           
         
           $.ajax({
               
           type: 'post',
           url: '/checkPass',
           data: {
           'password': pass,
           'id' : id
           },
           headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
           },
           success: function (data) {
           console.log(data);
           if (data == 'success') {
               $('#checkPass').modal('hide');
               $('#error').hide();  
               document.location.reload();                       
           } 
           else if(data== 'failure')
           {
            $('#error').text('Admin password is incorrect');                
            $('#error').show();
           }
               
           else if(data== 'deleteIssue')
           {
            $('#error').text('API returned error');                  
            $('#error').show();
           }
            else if(data== 'dbIssue')
            {
                $('#error').text('Issue while updating data in local db');                   
                $('#error').show();
            }
           },
           
           error: function(XMLHttpRequest, textStatus, errorThrown) {                
               $('#error').show();
           }        
       });
       })
});
</script>

<style>
td {
  white-space: normal !important; 
  word-wrap: break-word;  
}
</style>

<div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Order Details') }}</h3>
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

                    <div class="container" style="padding-bottom:5%; ">
                    <div class="row" style="margin-left:20px;">

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-dark"><H3 style="color:white!important;">Order Information</H3></div>
                            <div class="card-body">
                            <input type="hidden" class="form-control" id="marketplace" value="{{$order->marketplace}}">
                            <input type="hidden" class="form-control" id="store" value="{{$order->storeName}}">
                            <input type="hidden" class="form-control" id="date" value="{{$provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s'))}}">
                            <input type="hidden" class="form-control" id="orderNumber" value="{{$order->sellOrderId}}">
                            <input type="hidden" class="form-control" id="buyerName" value="{{$order->buyerName}}">
                            <input type="hidden" class="form-control" id="phone" value="{{$order->phone}}">
                            <input type="hidden" class="form-control" id="address1" value="{{$order->address1}}">                                    
                            <input type="hidden" class="form-control" id="address2" value="{{$order->address2}}">                                    
                            <input type="hidden" class="form-control" id="address3" value="{{$order->address3}}">                                    
                            <input type="hidden" class="form-control" id="city" value="{{$order->city}}">                                    
                            <input type="hidden" class="form-control" id="state" value="{{$order->state}}">                                    
                            <input type="hidden" class="form-control" id="country" value="{{$order->country}}">                                    
                            <input type="hidden" class="form-control" id="postalcode" value="{{$order->postalCode}}">                                    
                            <table>
                            <div class="form-line" style="display:none;">
                                <input type="text" class="form-control" id="idTbx" name="category" value="{{$order->id}}">                                    
                            </div>
                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>MarketPlace:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->marketplace}}</td>
                            </tr>

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Store:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->storeName}}</td>
                            </tr>
                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Date:</strong></td>
                            <td style="width:50%;padding: 5px;">{{ $provider::getIranTime(date_format(date_create($order->date), 'm/d/Y H:i:s')) }}</td>
                            </tr>
                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Order Number:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->sellOrderId}}</td>
                            </tr>                                             
                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Buyer Name:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->buyerName}}</td>
                            </tr>
                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Address:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->address1}} <br/> 
                            @if(!empty($order->address2))
                                {{$order->address2}}
                                <br/>
                            @endif
                            {{$order->city}} {{$order->state}} {{$order->postalCode}}</td>
                            </tr>

                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Phone:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->phone}}</td>
                            </tr>
                            
                            </table>
                            
                            
                                                   
                            </div>
                            
                        </div>
                    </div>


                   @if($order->status!='cancelled')
                    <div class="col-md-3">
                        <div class="card">
                        
                        @if($order->status!='shipped' && ($order->status!='cancelled' || auth()->user()->role==1 )&& $order->status!='processing')                    
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Processed</H3> </div>                            
                            <div class="card-body"><button id="btnProcess" class="btn btn-primary btn-md">Process</button></div>
                        
                        @elseif($order->status=="processing" || $order->status="shipped")
                       
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Processed<i id="updateBtn" style="float:right" class="fa fa-external-link-alt"></i></H3> </div>                            
                        
                        <div class="card-body" style="padding-right: 0.2rem;padding-left: 0.2rem;">
                            <table>
                            <div class="form-line" style="display:none;">
                                <input type="text" class="form-control" id="idTbx" name="category" value="{{$order->id}}">                                    
                            </div>
                            <tr>
                            <td style="width:45%;padding: 5px;"><strong>Purchase Order:</strong></td>
                            <td style="width:55%;padding: 5px;" >{{$order->poNumber}}</td>
                            </tr>

                            <tr>
                            <td style="width:45%;padding: 5px;"><strong>Total Amount:</strong></td>
                            <td style="width:55%;padding: 5px;" >{{ number_format((float)$order->poTotalAmount, 2, '.', '') }}</td>
                            </tr>

                            <tr>
                            <td style="width:45%;padding: 5px;"><strong>Account:</strong></td>
                            <td style="width:55%;padding: 5px;" >
                            @foreach($accounts as $account)
                                @if($account->id == $order->account_id)
                                    {{$account->email}}
                                @endif 
                            @endforeach
                            @if(!is_numeric($order->account_id))
                            {{$order->account_id}}
                            @endif
                            </td>
                            </tr>


                            </table>
                        </div>
                        @else
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Processed</H3> </div>                            
                        <div class="card-body"><button id="btnProcess" class="btn btn-primary btn-md" disabled>Process</button></div>
                        @endif    
                        
                        </div>
            
                        <div class="card">
                        <div class="card-body">
                        <div class="row">
                        <div class="col-lg-6"><strong>Ship Due:</strong><span style="display:block;">
                        {{ date_format(date_create($order->dueShip), 'm/d/Y') }}
                        </span></div>
                        <div class="col-lg-6"><strong>Delivery Due:</strong><span style="display:block;">
                        {{ date_format(date_create($order->dueDelivery), 'm/d/Y') }}
                        </span></div>
                        </div>
                        </div>
                        </div>
                    </div>
                    
                    @endif
                    @if($order->status!='cancelled')
                    <div class="col-md-3">
                        <div class="card">
                        
                        @if($order->status!='shipped' && $order->status!='cancelled' && $order->status=='processing')
                       
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Shipped</H3></div>
                        <div class="card-body"><button id="btnShip" class="btn btn-primary btn-md">Ship</button></div>
                        @elseif($order->status=="shipped")
                        
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Shipped<i id="updateShip" style="float:right" class="fa fa-external-link-alt"></i></H3></div>   
                                             
                        
                        <div class="card-body" style="padding-right: 0.2rem;padding-left: 0.2rem;">
                            <table>
                            <div class="form-line" style="display:none;">
                                <input type="text" class="form-control" id="idTbx" name="category" value="{{$order->id}}">                                    
                            </div>
                            <tr>
                            <td style="width:45%;padding: 5px;"><strong>Tracking Number:</strong></td>
                            <td style="width:55%;padding: 5px;" >
                                @if(empty($order->upsTrackingNumber))
                                {{$order->trackingNumber}}
                                @else
                                {{$order->upsTrackingNumber}}
                                @endif
                            </td>
                            </tr>

                            <tr>
                            <td style="width:45%;padding: 5px;"><strong>Carrier:</strong></td>
                            <td style="width:55%;padding: 5px;" >{{$order->carrier}}</td>
                            </tr>
                            </table>
                        </div>
                        @else
                        <div class="card-header bg-dark"><H3 style="color:white!important;">Shipped</H3></div>
                        <div class="card-body"><button id="btnShip" class="btn btn-primary btn-md" disabled>Ship</button></div>
                        @endif    
                    </div>                    

                    </div>
                    @endif
                    </div>
                    
                    
                    <div class="row">
                    
                    <div class="col-md-4 offset-md-8"  style="float:right;">
                    

                        @if(($order->status=='unshipped' || $order->status=='pending' || auth()->user()->role==1) && $order->status!='cancelled')            
                            <button id="btnCancel" class="btn btn-danger btn-md" style="float:right;"><i class="fa fa-window-close"></i>  Cancel Order</button>                                        
                        @endif

                        @if(!$order->converted && $order->status=='processing')
                            <button id="btnBce" class="btn btn-primary btn-md" style="float:right;margin-right:5px;"><i class="fa fa-retweet"></i>  BCE Conversion</button>                    
                        @endif

                        <a href="../reset/{{$order->id}}" class="btn btn-warning btn-md" style="color: black;background: yellow;border-color: yellow;float:right;margin-right:5px;">RESET</a>                                        
                    </div>
                    </div>
                    
                    
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-8">
                                <h3 class="mb-0">{{ __('Products') }}</h3>
                            </div>                            
                        </div>
                    </div>

                    <div class="container table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th width="9%" scope="col">{{ __('Amazon Image') }}</th>
                                    <th width="9%" scope="col">{{ __('WM Image') }}</th>
                                    <th width="18%" scope="col">{{ __('Product Name') }}</th>
                                    <th width="10%" scope="col">{{ __('SKU') }}</th>
                                    <th width="10%" scope="col">{{ __('UPC') }}</th>
                                    <th width="10%" scope="col">{{ __('WM ID') }}</th>
                                    <th width="8%" scope="col">{{ __('Quantity') }}</th>
                                    <th width="8%" scope="col">{{ __('Unit Price') }}</th>
                                    <th width="8%" scope="col">{{ __('Total Price') }}</th>
                                    <th width="10%" scope="col">{{ __('WM Link') }}</th>
                                    <th width="10%" scope="col">{{ __('Amazon Link') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $detail)
                                    <tr>
                                        <td width="9%" class="specifictd"><img src="{{ $detail->image }}" width="75px" height="75px"></td>
                                        <td width="9%" class="specifictd"><img src="{{ $detail->wmimage }}" width="75px" height="75px"></td>
                                        <td width="26%">{{ $detail->title }}</td>
                                        <td width="13%" class="specifictd">{{ $detail->SKU }}</td>
                                        <td width="13%" class="specifictd">{{ $detail->upc }}</td>
                                        <td width="13%" class="specifictd">{{ $detail->wmid }}</td>
                                        @if($detail->quantity>1)
                                        <td width="11%" style="color:red; font-size: 20px!important; " class="specifictd">{{ $detail->quantity }}</td>
                                        @else
                                        <td width="11%" class="specifictd">{{ $detail->quantity }}</td>
                                        @endif

                                        <td width="9%" class="specifictd">{{ number_format((float)$detail->unitPrice, 2, '.', '') }}</td>
                                        <td width="9%" class="specifictd">{{ number_format((float)$detail->totalPrice +(float)$detail->shippingPrice , 2, '.', '') }}</td>                                        
                                        <td width="9%" class="specifictd">
                                        <a href="https://www.walmart.com/ip/{{ $detail->wmid }}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a>
                                        </td>
                                        <td width="9%" class="specifictd">
                                        @if($detail->src=='Ebay')
                                        <a href="https://www.ebay.com/itm/{{$detail->SKU}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a>
                                        @else
                                        <a href="https://amazon.com/dp/{{$detail->SKU}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a>
                                        @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer py-4">
                        <nav class="d-flex justify-content-end" aria-label="...">
                            {{$details->links()}}
                        </nav>
                    </div>
                </div>
            </div>
        </div>
            

    </div>
<!-- Confirm Admin Password -->
<div class="modal fade" tabindex="-1" role="dialog" id="checkPass">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error" style="display:none">
            Admin Password Is Incorrect
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Confirm password to continue with order cancel:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Password:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="passTbx" name="category" >                                        
                   </div>
                    <div class="errorMsg">{!!$errors->survey_question->first('category');!!}</div>
               </div>
           </div>
       </div>
</div>




       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-confirm-password">@lang('Confirm Password')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Confirm Admin Password -->


<!-- Process Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="process">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="error2" style="display:none">
            Error while processing order. Please check the inputs below: 
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Process Order:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
            
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Purchase Order Number:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="poTbx" name="category" >                                        
                   </div>
                    <div id="emptyPO" style="color:red; display:none;">Purchase Order Number cannot be empty</div>
               </div>
           </div>
           <div id="charcountError" style="display:none;color:red;">
                PO Should be 19 characters for Amazon, and 14 for ebay orders on  gmail ids
            </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Total Amount:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="amountTbx" name="category" >                                        
                   </div>
                   <div id="emptyAmount" style="color:red; display:none;">Amount cannot be empty</div>
               </div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Account:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="accountTbx" name="userList" style="">                                
                        <option value="0">Select Account</option>                        
                        @foreach($accounts as $account)
                        <option value={{$account->id}}>{{$account->email}}</option>                                                           
                        @endforeach                              
                        <option value="ebay">eBay</option>
                        <option value="iHerb">iHerb</option>
                        <option value="Bonanza">Bonanza</option>
                        <option value="Target">Target</option>
                        <option value="Cindy">Cindy</option>                        
                        <option value="Jonathan">Jonathan</option>                        
                        <option value="Samuel">Samuel</option>                        
                        <option value="Other">Other</option>                        
                    </select>
                   </div>
                   
               </div>
               <div id="emptyAccount" style="color:red; display:none;">Please select an account</div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">                   
                   <div style="color:red;">{{ number_format((float)$order->totalAmount*0.85, 2, '.', '') }}</div>
                   
               </div>
           </div>
       </div>
</div>


       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-process">@lang('Process')</button>
        <button type="button" style="display:none" class="btn btn-primary" id="modal-edit">@lang('Update')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Process Modal -->

<!-- Process Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="ship">     
      
      <div class="modal-dialog" role="document">
      <div class="alert alert-danger" id="errorShipping" style="display:none">
            Error while shipping order. Please check the inputs below: 
       </div>
       
       <div class="alert alert-danger" id="dupIssue" style="display:none">             
       </div>

       <div class="alert alert-danger" id="errorCarrier" style="display:none">
            Please select carrier.
       </div>
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle">@lang('Ship Order:')</h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
            <input type="hidden" value="" id="catId" />
   <form class="form-horizontal" method="post" >
{{csrf_field()}}



<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Tracking Number:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="trackingTbx" name="category" >                                        
                   </div>
                   <div id="emptyTracking" style="color:red; display:none;">Tracking number cannot be empty</div>
               </div>
           </div>
       </div>
</div>
<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2">@lang('Carrier:')</label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                   <select class="form-control" id="carrierTbx" name="userList" style="">                                
                        <option value="0">Select Carrier</option>
                        @foreach($carriers as $carrier)
                        <option value={{$carrier->id}}>{{$carrier->name}}</option>                                                           
                        @endforeach                                                      
                    </select>
                   </div>
                   
               </div>
               <div id="emptyCarrier" style="color:red; display:none;">Please select a carrier</div>
           </div>
       </div>
</div>



       
   </form>
      </div>
       <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="modal-ship">@lang('Ship')</button>
        <button type="button" style="display:none" class="btn btn-primary" id="modal-ship-edit">@lang('Update')</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- Ship Modal -->


<!-- BCE Conversion Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="bce">     

      <div class="modal-dialog" role="document">
      
       
     
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title" id="addTitle"><strong>@lang('BCE Conversion:')</strong></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              
         <br/>
           </div>
        <div class="modal-body">
        
            <input type="hidden" value="" id="catIdBce" />
            <table>
                            <div class="form-line" style="display:none;">
                                <input type="text" class="form-control" id="idTbxBce" name="category" value="{{$order->id}}">                                    
                            </div>
                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>MarketPlace:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->marketplace}}</td>
                            </tr>                                                   

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Buyer Name:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->buyerName}}</td>
                            </tr>
                            

                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Line1:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->address1}} </td>
                            </tr>


                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>City:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->city}} </td>
                            </tr>

                            
                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>State:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->state}} </td>
                            </tr>

                            
                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Zip Code:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->postalCode}} </td>
                            </tr>
                           
                            <tr>
                            <td style="width:50%;padding: 5px;"><strong>Purchase Order:</strong></td>
                            <td style="width:50%;padding: 5px;">{{$order->poNumber}} </td>
                            </tr>
                            
                            </table>
   <form class="form-horizontal" method="post" >
{{csrf_field()}}


<br/><br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2"><strong>@lang('Tracking Number:')</strong></label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="trackingTbxBce" name="category" >                                        
                   </div>
                   <div id="emptyTrackingBce" style="color:red; display:none;">Tracking number cannot be empty</div>
               </div>
           </div>
       </div>
</div>

<br/>



<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2"><strong>@lang('Shipment ID:')</strong></label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="shipmentTbxBce" name="category" >                                        
                   </div>
                   <div id="emptyShippingBce" style="color:red; display:none;">Atleast one of Shipment Id or Item Id cannot be empty</div>
               </div>
           </div>
       </div>
</div>

<br/>
<div class="row clearfix">
       <div class="col-sm-3 form-control-label">
           <label for="email_address_2"><strong>@lang('Item ID:')</strong></label>
       </div>
       <div class="col-sm-9">
           <div class="form-group">
               <div class="form-line">
                   <div class="form-line">
                       <input type="text" class="form-control" id="itemTbxBce" name="category" >                                        
                   </div>
                   <div id="emptyItemBce" style="color:red; display:none;">Item Id cannot be empty</div>
               </div>
           </div>
       </div>
</div>



       
   </form>

        <div class="form-line">
                       <input type="text" class="form-control" id="bceInput" style="color:red; display:none;" disabled>                                        
                   </div>
      </div>
       <div class="modal-footer">
       <div id='loader' style='display: none;'>
            <img  src="{{ asset('argon') }}/img/brand/loader.gif"  width='32px' height='32px'>
        </div>
       <button type="button" class="btn btn-primary" id="btnConvert">@lang('Convert')</button>
       <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>                            
           </div>
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
</div>
<!-- BCE Conversion Modal -->

        @include('layouts.footers.auth')
    </div>
@endsection