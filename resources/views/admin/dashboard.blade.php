@extends('layouts.admin')

@section('title', 'Dashboard')

@section('styles')
<!-- Custom styles for dashboard -->
@endsection

@section('content')
<!-- Page Title Header Starts-->
<div class="row page-title-header">
    <div class="col-12">
        <div class="page-header">
            <h4 class="page-title">Dashboard</h4>
            <!-- <div class="quick-link-wrapper w-100 d-md-flex flex-md-wrap">
                <ul class="quick-links">
                    <li><a href="#">ICE Market data</a></li>
                    <li><a href="#">Own analysis</a></li>
                    <li><a href="#">Historic market data</a></li>
                </ul>
                <ul class="quick-links ml-auto">
                    <li><a href="#">Settings</a></li>
                    <li><a href="#">Analytics</a></li>
                    <li><a href="#">Watchlist</a></li>
                </ul>
            </div> -->
        </div>
    </div>
   <div class="col-md-12">
       <!-- <div class="page-header-toolbar">
            <div class="btn-group toolbar-item" role="group" aria-label="Basic example">
                <button type="button" class="btn btn-secondary"><i class="mdi mdi-chevron-left"></i></button>
                <button type="button" class="btn btn-secondary">{{ date('d/m/Y') }} - {{ date('d/m/Y', strtotime('+30 days')) }}</button>
                <button type="button" class="btn btn-secondary"><i class="mdi mdi-chevron-right"></i></button>
            </div>
            <div class="filter-wrapper">
                <div class="dropdown toolbar-item">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownsorting" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">All Day</button>
                    <div class="dropdown-menu" aria-labelledby="dropdownsorting">
                        <a class="dropdown-item" href="#">Last Day</a>
                        <a class="dropdown-item" href="#">Last Month</a>
                        <a class="dropdown-item" href="#">Last Year</a>
                    </div>
                </div>
                <a href="#" class="advanced-link toolbar-item">Advanced Options</a>
            </div>
            <div class="sort-wrapper">
                <button type="button" class="btn btn-primary toolbar-item">New</button>
                <div class="dropdown ml-lg-auto ml-3 toolbar-item">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownexport" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Export</button>
                    <div class="dropdown-menu" aria-labelledby="dropdownexport">
                        <a class="dropdown-item" href="#">Export as PDF</a>
                        <a class="dropdown-item" href="#">Export as DOCX</a>
                        <a class="dropdown-item" href="#">Export as CDR</a>
                    </div>
                </div>
            </div>
        </div>-->
    </div> 
</div>
<!-- Page Title Header Ends-->

<!-- Stats Cards Row -->
<div class="row">
    <!-- <div class="col-md-12 grid-margin">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex">
                            <div class="wrapper">
                                <h3 class="mb-0 font-weight-semibold">32,451</h3>
                                <h5 class="mb-0 font-weight-medium text-primary">Visits</h5>
                                <p class="mb-0 text-muted">+14.00(+0.50%)</p>
                            </div>
                            <div class="wrapper my-auto ml-auto ml-lg-4">
                                <canvas height="50" width="100" id="stats-line-graph-1"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-md-0 mt-4">
                        <div class="d-flex">
                            <div class="wrapper">
                                <h3 class="mb-0 font-weight-semibold">15,236</h3>
                                <h5 class="mb-0 font-weight-medium text-primary">Impressions</h5>
                                <p class="mb-0 text-muted">+138.97(+0.54%)</p>
                            </div>
                            <div class="wrapper my-auto ml-auto ml-lg-4">
                                <canvas height="50" width="100" id="stats-line-graph-2"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-md-0 mt-4">
                        <div class="d-flex">
                            <div class="wrapper">
                                <h3 class="mb-0 font-weight-semibold">7,688</h3>
                                <h5 class="mb-0 font-weight-medium text-primary">Conversation</h5>
                                <p class="mb-0 text-muted">+57.62(+0.76%)</p>
                            </div>
                            <div class="wrapper my-auto ml-auto ml-lg-4">
                                <canvas height="50" width="100" id="stats-line-graph-3"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mt-md-0 mt-4">
                        <div class="d-flex">
                            <div class="wrapper">
                                <h3 class="mb-0 font-weight-semibold">1,553</h3>
                                <h5 class="mb-0 font-weight-medium text-primary">Downloads</h5>
                                <p class="mb-0 text-muted">+138.97(+0.54%)</p>
                            </div>
                            <div class="wrapper my-auto ml-auto ml-lg-4">
                                <canvas height="50" width="100" id="stats-line-graph-4"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</div>

<!-- Charts and Tables Section -->
 <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-0">Welcome to the dashboard</h4>
                <div class="d-flex flex-column flex-lg-row">

                  </div>
            </div>
        </div>
    </div>
    <!--
    <div class="col-md-4 grid-margin stretch-card">
        <div class="card">
            <div class="card-body d-flex flex-column">
                <div class="wrapper">
                    <h4 class="card-title mb-0">Net Profit Margin</h4>
                    <p>Started collecting data from {{ date('F Y') }}</p>
                    <div class="mb-4" id="net-profit-legend"></div>
                </div>
                <canvas class="my-auto mx-auto" height="250" id="net-profit"></canvas>
            </div>
        </div>
    </div> -->
</div>
@endsection

@section('scripts')
<!-- Custom js for dashboard -->
<!-- <script src="{{ asset('assets/js/demo_1/dashboard.js') }}"></script> -->
@endsection