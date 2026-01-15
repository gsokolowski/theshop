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
                        Coupons ({{ $coupons->count() }})
                    </h3>
                    <a href="{{route('admin.coupons.create')}}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <hr>
                <div class="card-body">
                    <table class="datatable">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>Name</td>
                                <td>Discount</td>
                                <td>Valid Until</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coupons as $key => $coupon)
                                <tr>
                                    <td>{{ $key += 1 }}</td>
                                    <td>{{ $coupon->name }}</td>
                                    <td>{{ $coupon->discount }}</td>
                                    <td>{{ $coupon->valid_until }}</td>
                                    <td>
                                        <a href="{{route('admin.coupons.edit',$coupon->id)}}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="deleteItem({{$coupon->id}})" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <form id="{{$coupon->id}}" action="{{route('admin.coupons.destroy',$coupon->id)}}" method="post">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection