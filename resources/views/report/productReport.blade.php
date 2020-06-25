@extends('layouts.app', ['title' => __('Product Report')])

@section('content')
@include('layouts.headers.cards')

@section('css')
<link href="{{ asset('argon') }}/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('argon') }}/vendor/datatables/buttons.bootstrap4.min.css" rel="stylesheet">
<link href="{{ asset('argon') }}/vendor/datatables/select.bootstrap4.min.css" rel="stylesheet">

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
    }
</style>
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
                    <div class="row">
                        <div class="col text-center" id="filters">
                            <form action="productReportFilter" class="form-inline" style="width:100%" method="post">
                                @csrf
                                <div style="width:100%; padding-bottom:2%;">
                                    <div class="form-group">
                                        <div style="padding-right:1%;">
                                            <select class="form-control" id="marketplace" name="marketplace" style="margin-right:0%;width:180px;">
                                                <option value="0">Marketplaces</option>
                                                <option value="1">Amazon</option>
                                                <option value="2">eBay</option>
                                                <option value="3">Walmart</option>                                                                        
                                            </select>
                                        </div>
                                        <button id="btnExport" class="btn btn-primary btn-md" style="color:white;float:right;margin-left:30px;">Export</button>       
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table class="table align-items-center table-flush dataTable" id="productReport">
                                    <thead class="thead-light">
                                        <tr>
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
<script src="http://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.buttons.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/buttons.bootstrap4.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/buttons.html5.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/buttons.flash.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/buttons.print.min.js"></script>
<script src="{{ asset('argon') }}/vendor/datatables/js/dataTables.select.min.js"></script>




<script>
    $(document).ready(function(){

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // $('.date-input').datepicker({
        //     format: 'yyyy-mm-dd',
        //     clearBtn: true,
        //     todayBtn: 'linked'
        // });

        var table = $('#productReport').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            order: [[ 0, "Asc" ]],
            // searchDelay: 2000,
            ajax: {
                url: "{{ route('product.report') }}",
                method: 'GET',
                data: function(newData){
                    newData.cid = $('#marketplace option:selected').val();
                    newData.fromDate = $('#fromDate').val();
                    newData.toDate = $('#toDate').val();
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

            lengthChange: false,
            // bFilter: false,

        });

        $('#marketplace, #fromDate, #toDate').change(function () {
            table.draw();
        });

    });
</script>
@endpush