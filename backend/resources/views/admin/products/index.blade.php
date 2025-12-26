@extends('admin.layouts.app')
@section('title', 'Products')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Products ({{ $products->count() }})
                    </h3>
                    <a href="{{route('admin.products.create')}}" class="btn btn-sm btn-primary">
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
                                <td>Slug</td>
                                <td>Qty</td>
                                <td>Price</td>
                                <td>Images</td>
                                <td>Category</td>
                                <td>Brand</td>
                                <td>Colors</td>
                                <td>Sizes</td>
                                <td>Status</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $key => $product)
                                <tr>
                                    <td>{{ $key += 1 }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->slug }}</td>
                                    <td>{{ $product->qty }}</td>
                                    <td>{{ $product->price }}</td>
                                    <td>
                                        @if ($product->thumbnail)
                                            <img src="{{ asset('storage/'.$product->thumbnail) }}" alt="{{ $product->name }}" width="50" class="img-fluid rounded-3xl">
                                        @endif
                                        @if ($product->first_image)
                                            <img src="{{ asset('storage/'.$product->first_image) }}" alt="{{ $product->name }}" width="50" class="img-fluid rounded-3xl">
                                        @endif
                                        @if ($product->second_image)
                                            <img src="{{ asset('storage/'.$product->second_image) }}" alt="{{ $product->name }}" width="50" class="img-fluid rounded-3xl">
                                        @endif
                                        @if ($product->third_image)
                                        <img src="{{ asset('storage/'.$product->third_image) }}" alt="{{ $product->name }}" width="50" class="img-fluid rounded-3xl">
                                        @endif
                                    </td>
                                    <td>{{ $product->category->name }}</td>
                                    <td>{{ $product->brand->name }}</td>
                                    <td>
                                        @foreach ($product->colors as $color)
                                            <span class="badge bg-light text-dark">{{ $color->name }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @foreach ($product->sizes as $size)
                                            <span class="badge bg-light text-dark">{{ $size->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        @if ($product->status == 1)
                                            <span class="badge bg-success">In stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of stock</span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{route('admin.products.edit',$product->slug)}}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="deleteItem({{$product->id}})" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <form id="{{$product->id}}" action="{{route('admin.products.destroy',$product->slug)}}" method="post">
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