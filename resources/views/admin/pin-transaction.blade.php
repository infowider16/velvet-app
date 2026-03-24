@extends('layouts.admin')

@section('title', 'Pin Transactions')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .content-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    .bg-gradient-pin {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
    }

    .card-header {
        border-radius: 15px 15px 0 0 !important;
    }

    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 1rem 0.75rem;
    }

    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        margin: 0 2px;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #17a2b8;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        transform: scale(1.02);
    }

    .badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    div#pin-transaction-table_filter {
        display:none;
    }
</style>
@endpush
@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <!--<h1 class="m-0 text-info">-->
                    <!--    <i class="fas fa-thumbtack mr-2"></i> Pin Transactions-->
                    <!--</h1>-->
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">Transaction Management</li>
                        <li class="breadcrumb-item active">Pin</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Row -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom">
                            <h5 class="mb-0">
                                <i class="fas fa-filter mr-2"></i>Filters & Search
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="filterPaymentStatus" class="form-label">Payment Status</label>
                                    <select id="filterPaymentStatus" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="succeeded">Succeeded</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterPlatform" class="form-label">Platform</label>
                                    <select id="filterPlatform" class="form-control">
                                        <option value="">All Platforms</option>
                                        <option value="0">Android</option>
                                        <option value="1">iOS</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="filterDateRange" class="form-label">Date Range</label>
                                    <input type="text" id="filterDateRange" class="form-control" placeholder="Select date range" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label for="filterSearch" class="form-label">Search by User ID / User Name / Transaction ID</label>
                                    <input type="text" id="filterSearch" class="form-control" placeholder="Enter User ID, User Name, or Transaction ID...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Table Row -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-lg border-0">
                        <div class="card-header bg-gradient-pin text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-thumbtack mr-2"></i>Pin Transaction List
                                </h3>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="pin-transaction-table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-0">#</th>
                                            <th class="border-0">
                                                <i class="fas fa-key mr-1"></i>Transaction ID
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-user mr-1"></i>User Name
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-details mr-1"></i>Plan Details
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-money-bill mr-1"></i>Amount
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-check-circle mr-1"></i>Status
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-shopping-cart mr-1"></i>Purchased Date
                                            </th>
                                            <!--<th class="border-0">-->
                                            <!--    <i class="fas fa-calendar mr-1"></i>Start Date-->
                                            <!--</th>-->
                                            <!--<th class="border-0">-->
                                            <!--    <i class="fas fa-calendar-check mr-1"></i>End Date-->
                                            <!--</th>-->
                                            <th class="border-0">
                                                <i class="fas fa-mobile mr-1"></i>Platform
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    let table = $('#pin-transaction-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.transaction-pin-list') }}",
            data: function(d) {
                d.payment_status = $('#filterPaymentStatus').val();
                d.platform = $('#filterPlatform').val();
                d.search_term = $('#filterSearch').val();
                d.date_range = $('#filterDateRange').val();
            }
        },
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                orderable: false,
                searchable: false
            },
            {
                data: 'transaction_id',
                name: 'transaction_id',
                defaultContent: '-'
            },
            {
                data: 'name',
                name: 'name',
                defaultContent: '-'
            },
            {
                data: 'plan_details',
                name: 'plan_details',
                defaultContent: '-'
            },
            {
                data: 'amount',
                name: 'amount',
                defaultContent: '-'
            },
            {
                data: 'payment_status',
                name: 'payment_status',
                defaultContent: '-'
            },
            {
                data: 'created_at',
                name: 'created_at',
                defaultContent: '-'
            },
            // {
            //     data: 'start_time',
            //     name: 'start_time',
            //     render: function(data, type) {
            //         if (!data || data === '-') return '-';
            //         if (type === 'display') return data;
            //         return data;
            //     }
            // },
            // {
            //     data: 'end_time',
            //     name: 'end_time',
            //     render: function(data, type) {
            //         if (!data || data === '-') return '-';
            //         if (type === 'display') return data;
            //         return data;
            //     }
            // },
            {
                data: 'platform',
                name: 'platform'
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        responsive: true
    });

    // Filter on change
    $('#filterPaymentStatus, #filterPlatform').on('change', function() {
        table.draw();
    });

    // Search on enter or change
    $('#filterSearch').on('keyup change', function() {
        table.draw();
    });
</script>
@endsection
