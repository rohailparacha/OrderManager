@extends('layouts.app', ['title' => __('Daily Report')])
@section('content')
@include('layouts.headers.cards')
<link id="bsdp-css" href="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet">
<script src="https://unpkg.com/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
<script>
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [month, day, year].join('/');
}

$(document).ready(function(){
    
    var startDate =<?php echo empty($date)?"0":json_encode($date); ?>;
    if(startDate==0)
        startDate = new Date();

    $('.input-group.date').data({date: startDate}).datepicker({
    todayBtn: "linked",
    todayHighlight: true,
    autoclose: true
});

$('#datepicked').val(formatDate(startDate));
var ctx = document.getElementById('chart').getContext('2d');

var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: <?php echo empty($labels)?"":json_encode($labels); ?>,
    datasets: <?php echo empty($datasets)?"":json_encode($datasets); ?>,
  },
  options: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        fontColor: "#000080",
        usePointStyle: false,
        fontSize: 16        
      }
    },
    scales: {
      xAxes: [{ stacked: true, maxBarThickness: 80 }],
      yAxes: [{ stacked: true, maxBarThickness: 80 }]
    }
  }
});
});
</script>


    <div class="container-fluid mt--7">
        <div class="row">
            <div class="col">
                <div class="card shadow">
                    <div class="card-header border-0">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h3 class="mb-0">{{ __('Daily Report') }}</h3>
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

                    <div class="row" style="margin-left:0px!important;">
                        <div class="col-4 offset-md-8 text-center" id="filters" style="float:right; padding-bottom:1%; margin-right:2%;">
                        <form action="dailyReport" class="navbar-search navbar-search-light form-inline" style="width:100%" method="get">                            
                            <label>Select Date:</label>
                            <div class="input-group date" style="border-radius:0px; border-color:lightgrey; width:170px; border-radius: 2em; margin-left:2%;">
                              <input type="text" name="datepicked" id="datepicked" style="border:0px;" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
                            </div>
                            <button type="submit" id="submitBtn" class="btn btn-success mt-0" style="margin-left: 5px;">{{ __('Filter') }}</button>
                            
                        </form>
                        </div>
                    </div>           

        
        <div class="col-xl-12" style="padding-bottom:2%;">
                <div class="card shadow">
                    <div class="card-header bg-transparent">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="text-uppercase text-muted ls-1 mb-1"></h6>
                                <h2 class="mb-0"></h2>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Chart -->
                        <div class="chart">
                            <canvas id="chart" class="chart-canvas"></canvas>
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
    <script src="{{ asset('argon') }}/vendor/chart.js/dist/Chart.min.js"></script>
    
@endpush