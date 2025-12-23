@extends('admin.layouts.app')
@section('title', 'Colors')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Colors
                    </h3>
                    <a href="{{route('admin.colors.create')}}" class="btn btn-sm btn-primary">
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
                <!--  form for edit color -->
                <form action="{{route('admin.colors.update', $color->id)}}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input 
                        type="text" 
                        class="form-control @error('name') is-invalid @enderror"
                        name ="name"
                        id="name"
                        value="{{$color->name}}"
                        placeholder="Enter color name"
                        autofocus>
                        @error('name')
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