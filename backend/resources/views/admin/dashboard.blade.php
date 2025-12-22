@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white">
                    <h3 class="card-title">Dashboard</h3>
                    <hr>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6 mb-2">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong class ="badge bg-dark">
                                                Total Orders
                                            </strong>
                                            <span class="badge bg-dark">
                                                {{ $todayOrders->count() }}
                                            </span>
                                        </div>                                
                                    </div>
                                    <div class="card-footer text-center bg-white">
                                        <span class="badge bg-dark">
                                            ${{ $todayOrders->sum('total') ?? 0 }}
                                        </span>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <div class="d-flex justify-content-between">
                                            <strong class="badge bg-primary">
                                                Yesterday's Orders
                                            </strong>
                                            <span class="badge bg-primary">
                                                {{ $yesterdayOrders->count() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center bg-white">
                                        <strong class="badge bg-primary">
                                            ${{ $yesterdayOrders->sum('total') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <div class="d-flex justify-content-between">
                                            <strong class="badge bg-danger">
                                                This Month Orders
                                            </strong>
                                            <span class="badge bg-danger">
                                                {{ $monthOrders->count() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center bg-white">
                                        <strong class="badge bg-danger">
                                            ${{ $monthOrders->sum('total') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white">
                                        <div class="d-flex justify-content-between">
                                            <strong class="badge bg-success">
                                                This Year Orders
                                            </strong>
                                            <span class="badge bg-success">
                                                {{ $yearOrders->count() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-footer text-center bg-white">
                                        <strong class="badge bg-success">
                                            ${{ $yearOrders->sum('total') }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </div>
@endsection



