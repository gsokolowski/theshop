@extends('admin.layouts.app')
@section('title', 'Orders')
@section('content-dashboard')
    <div class="row mb-5">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Orders ({{ $orders->count() }})
                    </h3>
                </div>
                <hr>
                <div class="card-body">
                    <table class="datatable">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Order ID</td>
                                <td>Customer</td>
                                <td>Products</td>
                                <td>Qty</td>
                                <td>Total</td>
                                <td>Coupon</td>
                                <td>Order Date</td>
                                <td>Delivery Status</td>
                                <td>Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $key => $order)
                                <tr>
                                    <td>{{ $key += 1 }}</td>
                                    <td>#{{ $order->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->user->name }}</strong><br>
                                            <small class="text-muted">{{ $order->user->email }}</small>
                                        </div>
                                    </td>
                                    <td width="200px">
                                        @foreach ($order->products as $product)
                                            <div class="d-flex align-items-center mb-1">
                                                @if ($product->thumbnail)
                                                    <img src="{{ asset('storage/'.$product->thumbnail) }}" alt="{{ $product->name }}" width="30" height="30" class="img-fluid rounded me-2" style="object-fit: cover;">
                                                @endif
                                                <span class="badge bg-light text-dark">{{ $product->name }}</span>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>{{ $order->qty }}</td>
                                    <td>${{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @if ($order->coupon)
                                            <span class="badge bg-info">{{ $order->coupon->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at }}</td>
                                    <td class="text-center">
                                        @if ($order->deliverd_at)
                                            <span class="badge bg-success">Delivered</span><br>
                                            <small class="text-muted">{{ $order->deliverd_at }}</small>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#updateDeliveryModal{{ $order->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="#" onclick="deleteItem({{ $order->id }})" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <form id="{{ $order->id }}" action="{{ route('admin.orders.destroy', $order->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                
                                <!-- Update Delivery Modal -->
                                <div class="modal fade" id="updateDeliveryModal{{ $order->id }}" tabindex="-1" aria-labelledby="updateDeliveryModalLabel{{ $order->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateDeliveryModalLabel{{ $order->id }}">Update Delivery Status - Order #{{ $order->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.orders.update', $order->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="deliverd_at{{ $order->id }}" class="form-label">Delivery Date & Time</label>
                                                        <input type="datetime-local" class="form-control" id="deliverd_at{{ $order->id }}" name="deliverd_at" value="{{ $order->deliverd_at ? \Carbon\Carbon::parse($order->deliverd_at)->format('Y-m-d\TH:i') : '' }}">
                                                        <small class="text-muted">Leave empty to mark as pending</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection