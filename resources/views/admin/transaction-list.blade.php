@extends('layouts.admin')

@section('title', 'Transaction Management')
@push('styles')
<style>
    .content-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
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

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .modal-content {
        border-radius: 15px;
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

    .alert {
        border-radius: 10px;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
    }

    /* Status badges */
    .badge-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    /* Action buttons grouping */
    .action-buttons {
        white-space: nowrap;
    }

    .action-buttons .btn {
        margin: 0 1px;
        min-width: 80px;
    }

    /* DataTable custom styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-top: 1rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 6px !important;
        margin: 0 2px;
    }
    div#transaction-data-table_filter {
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
                    <h1 class="m-0 text-info">
                        <i class="fas fa-users mr-2"></i> Transaction Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Transactions</li>
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
                                <div class="col-md-3">
                                    <label for="filterFeatureType" class="form-label">Feature Type</label>
                                    <select id="filterFeatureType" class="form-control">
                                        <option value="">All Features</option>
                                        <option value="Ghost">Ghost</option>
                                        <option value="Boost">Boost</option>
                                        <option value="Pin">Pin</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterPaymentStatus" class="form-label">Payment Status</label>
                                    <select id="filterPaymentStatus" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="succeeded">Succeeded</option>
                                        <option value="pending">Pending</option>
                                        <option value="failed">Failed</option>
                                        <option value="refunded">Refunded</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterPlatform" class="form-label">Platform</label>
                                    <select id="filterPlatform" class="form-control">
                                        <option value="">All Platforms</option>
                                        <option value="0">Android</option>
                                        <option value="1">iOS</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="filterDateRange" class="form-label">Date Range</label>
                                    <input type="text" id="filterDateRange" class="form-control" placeholder="Select date range">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label for="filterSearch" class="form-label">Search by User-Name / Transaction ID</label>
                                    <input type="text" id="filterSearch" class="form-control" placeholder="Enter search term...">
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
                        <div class="card-header bg-gradient-info text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-list mr-2"></i>Transaction List
                                </h3>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0" id="transaction-data-table">
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
                                                <i class="fas fa-box mr-1"></i>Feature Type
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
                                                <i class="fas fa-calendar mr-1"></i>Start Date
                                            </th>
                                            <th class="border-0">
                                                <i class="fas fa-calendar-check mr-1"></i>End Date
                                            </th>
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
<script>
    let table = $('#transaction-data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.transaction-list') }}",
            data: function(d) {
                d.feature_type = $('#filterFeatureType').val();
                d.payment_status = $('#filterPaymentStatus').val();
                d.platform = $('#filterPlatform').val();
                d.search_term = $('#filterSearch').val();
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
                data: 'feature_type',
                name: 'feature_type',
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
                data: 'start_time',
                name: 'start_time',
                render: function(data, type) {
                    if (!data || data === '-') return '-';
                    if (type === 'display') return data;
                    return data;
                }
            },
            {
                data: 'end_time',
                name: 'end_time',
                render: function(data, type) {
                    if (!data || data === '-') return '-';
                    if (type === 'display') return data;
                    return data;
                }
            },
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
    $('#filterFeatureType, #filterPaymentStatus, #filterPlatform').on('change', function() {
        table.draw();
    });

    // Search on enter or change
    $('#filterSearch').on('keyup change', function() {
        table.draw();
    });
</script>
@endsection