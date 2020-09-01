@extends('layouts.app', ['title' => __('Daily Report')])
@section('content')
@include('layouts.headers.cards')

<script>


$(document).ready(function(){

var ctx = document.getElementById('chart').getContext('2d');

var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Risk Level','Risk Level 2','Risk Level 3'],
    datasets: [
      {
        label: 'Low',
        data: [67.8,21,33],
        backgroundColor: 'red',
      },
      {
        label: 'Moderate',
        data: [20.7,15,16],
        backgroundColor: 'blue',
      },
      {
        label: 'High',
        data: [11.4, 19, 25],
        backgroundColor: 'orange',
      }
    ],
  },
  options: {
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