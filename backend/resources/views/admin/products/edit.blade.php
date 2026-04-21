@extends('admin.layouts.app')
@section('title', 'Edit Product')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Edit Product
                    </h3>
                    <a href="{{route('admin.products.index')}}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <hr>
                <!-- Display validation errors at top of form -->
                @if (session('success'))
                    <div class="alert alert-success mb-3">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- Form for editing product -->
                <form action="{{route('admin.products.update', $product->slug)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input 
                            type="text" 
                            class="form-control @error('name') is-invalid @enderror"
                            name="name" 
                            id="name"
                            value="{{old('name', $product->name)}}"
                            placeholder="Enter product name"
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
                            value="{{old('qty', $product->qty)}}"
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
                            value="{{old('price', $product->price)}}"
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
                            placeholder="Enter description">{{old('description', $product->description)}}</textarea>
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
                        @if($product->thumbnail)
                            <small class="text-muted">Current: <a href="{{asset('storage/'.$product->thumbnail)}}" target="_blank">View current thumbnail</a></small>
                        @endif
                    </div>
                    <!-- Preview for thumbnail image -->
                    <div class="form-group">
                        @if($product->thumbnail)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <img id="thumbnail_preview" src="{{asset('storage/'.$product->thumbnail)}}" alt="Current Thumbnail" width="100" class="img-fluid rounded-3xl d-block flex-shrink-0">
                                <button type="submit" form="delete-product-image-thumbnail" class="btn btn-outline-danger btn-sm shadow-sm" title="Delete thumbnail" onclick="return confirm('Remove this image?');">Delete</button>
                            </div>
                        @else
                            <img id="thumbnail_preview" src="" alt="Thumbnail Preview" width="100" class="img-fluid rounded-3xl" style="display: none;">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="first_image">First Image</label>
                        <input type="file" class="form-control @error('first_image') is-invalid @enderror" name="first_image" id="first_image">
                        @error('first_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        @if($product->first_image)
                            <small class="text-muted">Current: <a href="{{asset('storage/'.$product->first_image)}}" target="_blank">View current image</a></small>
                        @endif
                    </div>
                    <!-- Preview for first image -->
                    <div class="form-group">
                        @if($product->first_image)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <img id="first_image_preview" src="{{asset('storage/'.$product->first_image)}}" alt="Current First Image" width="100" class="img-fluid rounded-3xl d-block flex-shrink-0">
                                <button type="submit" form="delete-product-image-first" class="btn btn-outline-danger btn-sm shadow-sm" title="Delete first image" onclick="return confirm('Remove this image?');">Delete</button>
                            </div>
                        @else
                            <img id="first_image_preview" src="" alt="First Image Preview" width="100" class="img-fluid rounded-3xl" style="display: none;">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="second_image">Second Image</label>
                        <input type="file" class="form-control @error('second_image') is-invalid @enderror" name="second_image" id="second_image">
                        @error('second_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        @if($product->second_image)
                            <small class="text-muted">Current: <a href="{{asset('storage/'.$product->second_image)}}" target="_blank">View current image</a></small>
                        @endif
                    </div>
                    <!-- Preview for second image -->
                    <div class="form-group">
                        @if($product->second_image)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <img id="second_image_preview" src="{{asset('storage/'.$product->second_image)}}" alt="Current Second Image" width="100" class="img-fluid rounded-3xl d-block flex-shrink-0">
                                <button type="submit" form="delete-product-image-second" class="btn btn-outline-danger btn-sm shadow-sm" title="Delete second image" onclick="return confirm('Remove this image?');">Delete</button>
                            </div>
                        @else
                            <img id="second_image_preview" src="" alt="Second Image Preview" width="100" class="img-fluid rounded-3xl" style="display: none;">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="third_image">Third Image</label>
                        <input type="file" class="form-control @error('third_image') is-invalid @enderror" name="third_image" id="third_image">
                        @error('third_image')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        @if($product->third_image)
                            <small class="text-muted">Current: <a href="{{asset('storage/'.$product->third_image)}}" target="_blank">View current image</a></small>
                        @endif
                    </div>
                    <!-- Preview for third image -->
                    <div class="form-group">
                        @if($product->third_image)
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <img id="third_image_preview" src="{{asset('storage/'.$product->third_image)}}" alt="Current Third Image" width="100" class="img-fluid rounded-3xl d-block flex-shrink-0">
                                <button type="submit" form="delete-product-image-third" class="btn btn-outline-danger btn-sm shadow-sm" title="Delete third image" onclick="return confirm('Remove this image?');">Delete</button>
                            </div>
                        @else
                            <img id="third_image_preview" src="" alt="Third Image Preview" width="100" class="img-fluid rounded-3xl" style="display: none;">
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" id="status">
                            <option value="" disabled>Select status</option>
                            <option value="1" {{ old('status', $product->status) == 1 ? 'selected' : '' }}>In stock</option>
                            <option value="0" {{ old('status', $product->status) == 0 ? 'selected' : '' }}>Out of stock</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="category_id">Category</label>
                        <select class="form-control @error('category_id') is-invalid @enderror" name="category_id" id="category_id">
                            <option value="" disabled>Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="brand_id">Brand</label>
                        <select class="form-control @error('brand_id') is-invalid @enderror" name="brand_id" id="brand_id">
                            <option value="" disabled>Select brand</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="color_id">Color</label>
                        <select class="form-control @error('color_id') is-invalid @enderror" name="color_id[]" id="color_id" multiple>
                            @foreach ($colors as $color)
                                <option value="{{ $color->id }}" {{ in_array($color->id, old('color_id', $product->colors->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $color->name }}</option>
                            @endforeach
                        </select>
                        @error('color_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="size_id">Size</label>
                        <select class="form-control @error('size_id') is-invalid @enderror" name="size_id[]" id="size_id" multiple>
                            @foreach ($sizes as $size)
                                <option value="{{ $size->id }}" {{ in_array($size->id, old('size_id', $product->sizes->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $size->name }}</option>
                            @endforeach
                        </select>
                        @error('size_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="form-group mb-6 pb-4">                    
                        <button type="submit" class="btn btn-sm btn-primary mt-4 mb-4">
                            <i class="fas fa-edit btn-icon"></i>
                            Update
                        </button>
                    </div>                    
                </form>

                {{-- Separate forms: nested <form> inside the update form is invalid HTML and can submit the wrong route (e.g. delete product). --}}
                @if($product->thumbnail)
                <form id="delete-product-image-thumbnail" action="{{ route('admin.products.image.destroy', $product) }}" method="post" class="d-none">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="field" value="thumbnail">
                </form>
                @endif
                @if($product->first_image)
                <form id="delete-product-image-first" action="{{ route('admin.products.image.destroy', $product) }}" method="post" class="d-none">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="field" value="first_image">
                </form>
                @endif
                @if($product->second_image)
                <form id="delete-product-image-second" action="{{ route('admin.products.image.destroy', $product) }}" method="post" class="d-none">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="field" value="second_image">
                </form>
                @endif
                @if($product->third_image)
                <form id="delete-product-image-third" action="{{ route('admin.products.image.destroy', $product) }}" method="post" class="d-none">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="field" value="third_image">
                </form>
                @endif
            </div>
        </div>
    </div>
@endsection
