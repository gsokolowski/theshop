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
                        Products
                    </h3>
                    <a href="{{route('admin.products.create')}}" class="btn btn-sm btn-primary">
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
                <!--  form for create product -->
                <form action="{{route('admin.products.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror"
                            name="name" 
                            id="name"
                            value="{{old('name')}}"
                            placeholder="Enter category name"
                            autofocus>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="qty">Quantity</label>
                        <input 
                            type="number" 
                            class="form-control @error('qty') is-invalid @enderror"
                            name="qty" 
                            id="qty"
                            value="{{old('qty')}}"
                            placeholder="Enter quantity"
                            min="0">
                        @error('qty')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input 
                            type="number" 
                            class="form-control @error('price') is-invalid @enderror"
                            name="price" 
                            id="price"
                            value="{{old('price')}}"
                            placeholder="Enter price"
                            min="0">
                        @error('price')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea 
                            class="form-control summernote @error('description') is-invalid @enderror"
                            name="description" 
                            id="description"
                            placeholder="Enter description">{{old('description')}}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
                        <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" name="thumbnail" id="thumbnail">
                        @error('thumbnail')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="first_image">First Image</label>
                        <input type="file" class="form-control @error('first_image') is-invalid @enderror" name="first_image" id="first_image">
                        @error('first_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="second_image">Second Image</label>
                        <input type="file" class="form-control @error('second_image') is-invalid @enderror" name="second_image" id="second_image">
                        @error('second_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="third_image">Third Image</label>
                        <input type="file" class="form-control @error('third_image') is-invalid @enderror" name="third_image" id="third_image">
                        @error('third_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" id="status">
                            <option value="" selected disabled>Select status</option>
                            <option value="1">In stock</option>
                            <option value="0">Out of stock</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control @error('category_id') is-invalid @enderror" name="category_id" id="category_id">
                            <option value="" selected disabled>Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="brand_id">Brand</label>
                        <select class="form-control @error('brand_id') is-invalid @enderror" name="brand_id" id="brand_id">
                            <option value="" selected disabled>Select brand</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="color_id">Color</label>
                        <select class="form-control @error('color_id') is-invalid @enderror" name="color_id[]" id="color_id" multiple>
                            <!-- multiple select -->
                            @foreach ($colors as $color)
                                <option value="{{ $color->id }}">{{ $color->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="size_id">Size</label>
                        <select class="form-control @error('size_id') is-invalid @enderror" name="size_id[]" id="size_id" multiple>
                            @foreach ($sizes as $size)
                                <option value="{{ $size->id }}">{{ $size->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                    
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                        Create
                    </button>
                    </div>
                    <br><br><br>
                </form>
            </div>
        </div>
    </div>
@endsection