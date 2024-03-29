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

    .report td, .repor th {
        white-space: normal !important; 
        word-wrap: break-word;  
        padding-left:1rem!important;
        padding-right:1rem!important;
    }

    .report th
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
                        @if(request()->route()->getName() != 'sold.report')
                            <div class="col-6" style="text-align:right;">
                                <a href="{{ url()->previous() }}"class="btn btn-primary btn-md">Go Back</a>
                            </div>
                        @endif
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

                                <div style="padding-right:1%;">
                                    <div class="form-group">
                                        <input class="form-control" type="text" id="daterange" name="daterange" value="{{ $daterange ?? ''}}" />
                                    </div>
                                </div>

                                <div style="padding-right:3%;">
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
                                </div>


                                <div style="padding-right:1%;">
                                    <select class="form-control" id="daysRange" name="daysRange" style="margin-right:0%;width:180px;">
                                        <option value="0">Days</option>
                                        <option value="30" @if($daysRange == 30) selected @endif>30 Days</option>
                                        <option value="60" @if($daysRange == 60) selected @endif>60 Days</option>
                                        <option value="90" @if($daysRange == 90) selected @endif>90 Days</option>
                                        <option value="120" @if($daysRange == 120) selected @endif>120 Days</option>
                                    </select>
                                </div>


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
                                <table class="table table-hover table-sm report" id="soldReport">
                                    <thead class="thead-light">
                                        <tr>
                                            <th scope="col" width="7%">{{ __('Image') }}</th>
                                            <th scope="col" width="7%">{{ __('Store Name') }}</th>
                                            <th scope="col" width="7%">{{ __('ASIN') }}</th>
                                            <th scope="col" width="30%">{{ __('Title') }}</th>
                                            <th scope="col" width="7%">{{ __('Date') }}</th>
                                            <th scope="col" width="7%">{{ __('30 Days') }}</th>
                                            <th scope="col" width="7%">{{ __('60 Days') }}</th>
                                            <th scope="col" width="7%">{{ __('90 Days') }}</th>
                                            <th scope="col" width="7%">{{ __('120 Days') }}</th>
                                            <th scope="col" width="7%">{{ __('Total Sold') }}</th>
                                            <th scope="col" width="7%">{{ __('Link') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($products->count())
                                            @foreach($products as $product)
                                                <tr>
                                                    <td width="7%"><img src="{{ $product->image }}" width="75px" height="75px"></td>
                                                    <td width="7%" class="specifictd">{{ $product->account }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->asin }}</td>
                                                    <td width="30%">{{ $product->title }}</td>
                                                    <td width="7%" class="specifictd">{{ date('m/d/Y', strtotime($product->created_at)) }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->{'30days'} }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->{'60days'} }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->{'90days'} }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->{'120days'} }}</td>
                                                    <td width="7%" class="specifictd">{{ $product->sold }}</td>
                                                    <td width="7%" class="specifictd"><a href="https://amazon.com/dp/{{$product->asin}}" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-external-link-alt"></i> Product</a></td>
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

        $(function () {
            debugger;
            var min_sold = {{ $min_sold }};
            var max_sold = {{ $max_sold }};
            $("#sold-range").slider({
                range: true,
                min: min_sold,
                max: max_sold,
                values: [{{  $filtered_min_sold  }}, {{ $filtered_max_sold }}],
                slide: function (event, ui) {
                    $("#sold").val(ui.values[0] + " - " + ui.values[1]);
                    $("#min_sold").val(ui.values[0]);
                    $("#max_sold").val(ui.values[1]);
                }
            });

            $("#sold").val($("#sold-range").slider("values", 0) +
                " - " + $("#sold-range").slider("values", 1));
        });



    });
</script>

@endpush