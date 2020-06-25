@extends('layouts.app', ['title' => __('Product Report')])

@section('content')
@include('layouts.headers.cards')

@section('css')
<link href="{{ asset('argon') }}/vendor/datatables/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('argon') }}/vendor/datatables/css/buttons.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('argon') }}/vendor/datatables/css/select.bootstrap4.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">


<!-- <style>
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
    }
</style> -->
@endsection

<div class="container-fluid mt--7">
    <div class="row">
        <div class="col">
            <div class="card shadow">
                <div class="card-header border-0">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h3 class="mb-0">{{ __('Product Report') }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="productReportFilter" class="form-inline" style="width:100%" method="post">
                        @csrf
                        <div class="row">
                            <div class="col" id="filters">
                                <div class="form-group">
                                    <div style="padding-right:1%;">
                                        <select class="form-control" id="storeName" name="storeName" style="margin-right:0%;width:180px;">
                                            <option value="">Store Name</option>
                                            @foreach($stores as $store)
                                                <option value="{{ $store }}">{{ $store }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <select class="form-control" id="export" name="export">
                                        <option id=""> Export </option>
                                        <option id="csv">Export as CSV</option>
                                        <option id="excel">Export as XLS</option>
                                        <option id="copy">Copy to clipboard</option>
                                        <option id="pdf">Export as PDF</option>
                                        <option id="print">Print</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <input class="form-control" type="text" name="daterange" value="{{$dateRange ?? ''}}" />
                                    <input type="hidden" id="fromDate" name="fromDate">
                                    <input type="hidden" id="toDate" name="toDate">
                                </div>
                            </div>


                            <div class="col">
                                <div class="form-group row">
                                    <label for="sold" class="col-md-4 col-form-label form-control-label">Sold:</label>
                                    <div class="col-md-8">
                                        <input class="form-control" type="text" id="sold" name="sold" readonly style="border:0; font-weight:bold;">
                                        <p>
                                            <div id="slider-range"></div>
                                        </p>

                                        <input type="hidden" id="sold_min" name="sold_min">
                                        <input type="hidden" id="sold_max" name="sold_max">
                                    </div>
                                </div>



                            </div>


                        </div>
                    </form>
                    <br>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush dataTable" id="productReport">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Image') }}</th>
                                            <th>{{ __('ASIN') }}</th>
                                            <th>{{ __('Store Name') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Sold') }}</th>
                                            <th>{{ __('Returned') }}</th>
                                            <th>{{ __('Cancelled') }}</th>
                                            <th>{{ __('Net') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footers.auth')
</div>
@endsection

@push('js')
<script src="http://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.6.2/js/buttons.print.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/buttons.bootstrap4.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<!-- <script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.buttons.min.js"></script> -->
<!-- <script src="{{ asset('argon') }}/vendor/datatables/js/buttons.html5.min.js"></script> -->
<!-- <script src="{{ asset('argon') }}/vendor/datatables/js/buttons.flash.min.js"></script> -->
<!-- <script src="{{ asset('argon') }}/vendor/datatables/js/buttons.print.min.js"></script> -->
<!-- <script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.select.min.js"></script> -->


<script>
    $(document).ready(function(){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });



        var table = $('#productReport').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 150,
            order: [[ 0, "Asc" ]],
            // searchDelay: 2000,
            ajax: {
                url: "{{ route('product.report') }}",
                method: 'GET',
                data: function(newData){
                    newData.storeName = $('#storeName option:selected').val();
                    newData.fromDate = $('#fromDate').val();
                    newData.toDate = $('#toDate').val();
                    newData.sold_min = $('#sold_min').val();
                    newData.sold_max = $('#sold_max').val();
                },
                beforeSend: function(jqXHR, settings){
                    // 
                },

                statusCode: {
                    200: function(responseObject, textStatus, errorThrown) {
                        // SETTING THE VALUES HERE WILL PUT SCRIPT IN INFINITE LOOP
                    }
                },
            },
            columns: [
                {data: 'image', name: 'image'},
                {data: 'asin', name: 'asin'},
                {data: 'account', name: 'account'},
                {data: 'created_at', name: 'created_at'},
                {data: 'sold', name: 'sold'},
                {data: 'returned', name: 'returned'},
                {data: 'cancelled', name: 'cancelled'},
                {data: 'net', name: 'net'},
            ],

            language: {
                paginate: {
                    previous: '<i class="fas fa-angle-left"></i>',
                    next: '<i class="fas fa-angle-right"></i>'
                }
            },

            lengthChange: true,
            // bFilter: false,

            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ],

            initComplete: function() {
                var $buttons = $('.dt-buttons').hide();
                var $srch = $('#productReport_filter').hide();

                $('#export').on('change', function() {
                    var btnClass = $(this).find(":selected")[0].id 
                    ? '.buttons-' + $(this).find(":selected")[0].id 
                    : null;
                    if (btnClass) $buttons.find(btnClass).click(); 
                })
            }

        });

        $.fn.DataTable.ext.pager.numbers_length = 13;

        $('#storeName, #fromDate, #toDate').change(function () {
            table.draw();
        });


        $('input[name="daterange"]').daterangepicker({
            opens: 'left'
        }, function(start, end, label) {
            console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
            $('#fromDate').val(start.format('YYYY-MM-DD'));
            $('#toDate').val(end.format('YYYY-MM-DD'));
            table.draw();
        });

        var search = $.fn.dataTable.util.throttle(
            function(val) {
                table.search(val).draw();
            },
            400  // Search delay in ms
        );

        // $('#dSearch').keyup(function(){
        //     search(this.value);
        // });

        $('#searchQuery').keyup(function(){
            // table.search($(this).val()).draw();
            search(this.value);
        });

        $( "#slider-range" ).slider({
        range: true,
        min: {{ $minAmount }},
        max: {{ $maxAmount }},
        values: [ {{ $minAmount }}, {{ $maxAmount }} ],
        slide: function( event, ui ) {
            $( "#sold" ).val(ui.values[ 0 ] + " - " + ui.values[ 1 ] );
            $("#sold_min").val(ui.values[ 0 ]);
            $("#sold_max").val(ui.values[ 1 ]);
        }
        });

        $( "#sold" ).val($( "#slider-range" ).slider( "values", 0 ) +
          " - " + $( "#slider-range" ).slider( "values", 1 ) );

    });
</script>

  <script>
  $( function() {
      
  } );
  </script>



@endpush