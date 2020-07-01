@extends('layouts.app', ['title' => __('Product Report')])

@section('content')
@include('layouts.headers.cards')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<style>
    .card .table td, 
    .card .table th {
        padding-right: .5rem;
        padding-left: .5rem;
    }

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
                            <h3 class="mb-0">{{ __('Sold Report') }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('sold.report') }}" class="form-inline" style="width:100%" method="get">

                        <input type="hidden" id="fromDate" name="fromDate" value="{{ $fromDate ?? '' }}">
                        <input type="hidden" id="toDate" name="toDate" value="{{ $toDate ?? '' }}">

                        <input type="hidden" id="min_sold" name="min_sold" value="{{ $filtered_min_sold ?? '' }}">
                        <input type="hidden" id="max_sold" name="max_sold" value="{{ $filtered_max_sold ?? '' }}">
                        <input type="hidden" id="filtered_min_sold" name="filtered_min_sold" value="{{ $filtered_min_sold ?? '' }}">
                        <input type="hidden" id="filtered_max_sold" name="filtered_max_sold" value="{{ $filtered_max_sold ?? '' }}">

                        <div style="width:100%; padding-bottom:2%;">
                            <div class="form-group focused">

                                <div style="padding-right:1%;">
                                    <select class="form-control" id="storeName" name="storeName" style="margin-right:0%;width:180px;">
                                        <option value="">Store Name</option>
                                        @foreach($stores as $store)
                                            <option value="{{ $store }}" @if($storeName == $store) selected @endif>{{ $store }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- <div style="padding-right:1%;">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="daterange" name="daterange" value="{{ $daterange ?? ''}}" />
                                    </div>
                                </div> -->

                                <!-- <div style="padding-right:3%;">
                                    <p id="price">
                                        <label for="sold">Sold</label>
                                        <input class="form-control" style="width:200px;" type="text" name="sold" id="sold"
                                            readonly="">
                                    </p>
                                    <div id="sold-range" style="width:200px;"
                                        class="ui-slider ui-corner-all ui-slider-horizontal ui-widget ui-widget-content">
                                        <div class="ui-slider-range ui-corner-all ui-widget-header" style="left: 0%; width: 100%;"></div><span
                                            tabindex="0" class="ui-slider-handle ui-corner-all ui-state-default" style="left: 0%;"></span><span
                                            tabindex="0" class="ui-slider-handle ui-corner-all ui-state-default" style="left: 100%;"></span>
                                    </div>
                                </div> -->

                                <input type="submit" value="Filter" class="btn btn-primary btn-md">
                                <input type="submit" value="Export" class="btn btn-primary btn-md" id="btnExport" name="btnExport">

                            </div>
                        </div>
                   </form>

                    <br>

                    <div class="row">
                        <div class="col">
                            <div class="row">
                                <div class="col">
                                    <div class="float-right">
                                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of total {{$products->total()}} entries
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-sm" id="productReport">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>{{ __('Image') }}</th>
                                            <th>{{ __('Store Name') }}</th>
                                            <th>{{ __('ASIN') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Sold 30 Days') }}</th>
                                            <th>{{ __('Sold 60 Days') }}</th>
                                            <th>{{ __('Sold 90 Days') }}</th>
                                            <th>{{ __('Sold 120 Days') }}</th>
                                            <th>{{ __('Total Sold') }}</th>
                                            <th>{{ __('Link') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($products->count())
                                            @foreach($products as $product)
                                                <tr>
                                                    <td><img src="{{ $product->image }}" width="75px" height="75px"></td>
                                                    <td>{{ $product->account }}</td>
                                                    <td>{{ $product->asin }}</td>
                                                    <td>{{ $product->title }}</td>
                                                    <td>{{ date('m/d/Y', strtotime($product->created_at)) }}</td>
                                                    <td class="text-center">{{ $product->sales30days }}</td>
                                                    <td class="text-center">{{ $product->sales60days }}</td>
                                                    <td class="text-center">{{ $product->sales90days }}</td>
                                                    <td class="text-center">{{ $product->sales120days }}</td>
                                                    <td class="text-center">{{ $product->totalSold }}</td>
                                                    <td><a href="https://amazon.com/dp/{{$product->asin}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="11" class="text-center"> No records found. </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="row" style="margin-top:15px;">
                                <div class="col">
                                    <div class="float-right">{{ $products->appends(request()->except('page'))->links() }}</div>
                                </div>
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
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>


<script>
    $(document).ready(function(){

        $('#daterange').daterangepicker({
            opens: 'left',
            maxDate: moment()
        }, function(start, end, label) {
            $('#fromDate').val(start.format('YYYY-MM-DD'));
            $('#toDate').val(end.format('YYYY-MM-DD'));
        });

{{{--
        // $(function () {
        //     debugger;
        //     var min_sold = {{ $min_sold }};
        //     var max_sold = {{ $max_sold }};
        //     $("#sold-range").slider({
        //         range: true,
        //         min: min_sold,
        //         max: max_sold,
        //         values: [{{ $filtered_min_sold ?? $min_sold }}, {{ $filtered_max_sold ?? $max_sold }}],
        //         slide: function (event, ui) {
        //             $("#sold").val(ui.values[0] + " - " + ui.values[1]);
        //             $("#min_sold").val(ui.values[0]);
        //             $("#max_sold").val(ui.values[1]);
        //         }
        //     });

        //     $("#sold").val($("#sold-range").slider("values", 0) +
        //         " - " + $("#sold-range").slider("values", 1));
        // });
--}}}





    });
</script>

@endpush