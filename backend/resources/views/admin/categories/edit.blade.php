@extends('admin.layouts.app')
@section('title', 'Categories')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Categories
                    </h3>
                    <a href="{{route('admin.categories.create')}}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i>
                    </a>
                </div>
                <hr>
                <!--  form for create category -->
                <form action="{{route('admin.categories.update', $category->slug)}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input 
                        type="text" 
                        class="form-control @error('name') is-invalid @enderror"
                        name ="name"
                        id="name"
                        class="form-control" 
                        required 
                        value="{{$category->name}}"
                        placeholder="Enter category name"
                        autofocus>
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
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