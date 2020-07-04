@extends('layouts.app', ['title' => __('Product Report')])

@section('content')
@include('layouts.headers.cards')

@section('css')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

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
                            <h3 class="mb-0">{{ __('Sale Report') }}</h3>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('sales.report') }}" class="form-inline" style="width:100%" method="get">

                        <input type="hidden" id="fromDate" name="fromDate" value="{{ $fromDate ?? '' }}">
                        <input type="hidden" id="toDate" name="toDate" value="{{ $toDate ?? '' }}">

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

                                <div style="padding-right:1%;">
                                    <select class="form-control" id="chartType" name="chartType" style="margin-right:0%;width:180px;">
                                        <option value="0">Chart Type</option>
                                        <option value="qty" @if($chartType == 'qty') selected @endif>Qty Chart</option>
                                        <option value="amt" @if($chartType == 'amt') selected @endif>Amount Chart</option>
                                    </select>
                                </div>

                                <input type="submit" value="Filter" class="btn btn-primary btn-md">
                                <input type="submit" value="Export" class="btn btn-primary btn-md" id="btnExport" name="btnExport">

                            </div>
                        </div>
                   </form>

                    <br>

                    <div class="row">
                        <div class="col float-left">
                            <h3>@if($chartType == 'amt') Sales Amount Chart @else Sales Quantity Chart @endif</h3>

                            <div id="chart_div"></div>

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

<script>
    $(document).ready(function(){

        $('#daterange').daterangepicker({
            opens: 'left',
            maxDate: moment(),
            maxSpan: {
                days: 30
            },
        }, function(start, end, label) {
            $('#fromDate').val(start.format('YYYY-MM-DD'));
            $('#toDate').val(end.format('YYYY-MM-DD'));
        });

    });
</script>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      // Load the Visualization API and the corechart package.
      google.charts.load('current', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = google.visualization.arrayToDataTable(
            <?php echo json_encode($chart); ?>
        );

        // Set chart options
        var options = {
            width: 1200,
            height: 600,
            legend: { position: 'top', maxLines: 3 },
            bar: { groupWidth: '75%' },
            isStacked: true,
        };

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>

@endpush