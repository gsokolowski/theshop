@extends('admin.layouts.app')
@section('title', 'Coupons')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Coupons
                    </h3>
                    <a href="{{route('admin.coupons.create')}}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <hr>
                <!-- Display validation errors at top of form -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!--  form for edit coupon -->
                <form action="{{route('admin.coupons.update', $coupon->id)}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror"
                            name="name"
                            id="name"
                            value="{{old('name', $coupon->name)}}"
                            placeholder="Enter coupon name"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="discount">Discount</label>
                        <input 
                            type="number" 
                            class="form-control @error('discount') is-invalid @enderror"
                            name="discount"
                            id="discount"
                            value="{{old('discount', $coupon->discount)}}"
                            placeholder="Enter discount"
                            step="0.01">
                        @error('discount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="valid_until">Valid Until</label>
                        <input 
                            type="datetime-local" 
                            class="form-control @error('valid_until') is-invalid @enderror"
                            name="valid_until"
                            id="valid_until"
                            value="{{old('valid_until', $coupon->valid_until ? \Carbon\Carbon::parse($coupon->valid_until)->format('Y-m-d\TH:i') : '')}}"
                            placeholder="Enter valid until date">
                        @error('valid_until')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                        Update
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection