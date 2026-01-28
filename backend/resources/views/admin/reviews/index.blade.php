@extends('admin.layouts.app')
@section('title', 'Reviews')
@section('content-dashboard')
    <div class="row mb-5">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Reviews ({{ $reviews->count() }})
                    </h3>
                </div>
                <hr>
                <!-- Filter buttons -->
                <div class="mb-3">
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm {{ !request()->has('filter') ? 'btn-primary' : 'btn-outline-primary' }}">
                        All
                    </a>
                    <a href="{{ route('admin.reviews.index', ['filter' => 'approved']) }}" class="btn btn-sm {{ request()->get('filter') === 'approved' ? 'btn-success' : 'btn-outline-success' }}">
                        Approved
                    </a>
                    <a href="{{ route('admin.reviews.index', ['filter' => 'unapproved']) }}" class="btn btn-sm {{ request()->get('filter') === 'unapproved' ? 'btn-warning' : 'btn-outline-warning' }}">
                        Unapproved
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>ID</td>
                                    <td>User</td>
                                    <td>Product</td>
                                    <td>Title</td>
                                    <td>Body</td>
                                    <td>Status</td>
                                    <td>Date</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reviews as $key => $review)
                                    <tr>
                                        <td>{{ $key += 1 }}</td>
                                        <td>#{{ $review->id }}</td>
                                        <td width="150px">
                                            <div>
                                                <strong>{{ $review->user->name }}</strong><br>
                                                <small class="text-muted">{{ $review->user->email }}</small>
                                            </div>
                                        </td>
                                        <td width="150px">
                                            <div class="d-flex align-items-center">
                                                @if ($review->product->thumbnail)
                                                    <img src="{{ asset('storage/'.$review->product->thumbnail) }}" alt="{{ $review->product->name }}" width="30" height="30" class="img-fluid rounded me-2" style="object-fit: cover;">
                                                @endif
                                                <span class="badge bg-light text-dark">{{ Str::limit($review->product->name, 15) }}</span>
                                            </div>
                                        </td>
                                        <td width="120px">
                                            <div style="max-width: 120px; word-wrap: break-word; white-space: normal;">
                                                {{ Str::limit($review->title, 20) }}
                                            </div>
                                        </td>
                                        <td width="150px">
                                            <div style="max-width: 150px; word-wrap: break-word; white-space: normal;">
                                                {{ Str::limit($review->body, 50) }}
                                            </div>
                                        </td>
                                        <td width="100px" class="text-center">
                                            @if ($review->approved)
                                                <span class="badge bg-success">Approved</span>
                                            @else
                                                <span class="badge bg-warning">Unapproved</span>
                                            @endif
                                        </td>
                                        <td width="120px">{{ $review->created_at }}</td>
                                        <td width="100px">
                                            <button type="button" class="btn btn-sm {{ $review->approved ? 'btn-warning' : 'btn-success' }}" data-bs-toggle="modal" data-bs-target="#approveReviewModal{{ $review->id }}">
                                                <i class="fas {{ $review->approved ? 'fa-times' : 'fa-check' }}"></i>
                                            </button>
                                            <a href="#" onclick="deleteItem({{ $review->id }})" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <form id="{{ $review->id }}" action="{{ route('admin.reviews.destroy', $review->id) }}" method="post">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                </tr>
                                
                                <!-- Approve/Unapprove Review Modal -->
                                <div class="modal fade" id="approveReviewModal{{ $review->id }}" tabindex="-1" aria-labelledby="approveReviewModalLabel{{ $review->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="approveReviewModalLabel{{ $review->id }}">
                                                    {{ $review->approved ? 'Unapprove' : 'Approve' }} Review #{{ $review->id }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.reviews.update', $review->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <p>Are you sure you want to {{ $review->approved ? 'unapprove' : 'approve' }} this review?</p>
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h6>{{ $review->title }}</h6>
                                                            <p class="mb-2">{{ $review->body }}</p>
                                                            <div class="d-flex align-items-center mb-2">
                                                                @for ($i = 1; $i <= 5; $i++)
                                                                    @if ($i <= $review->rating)
                                                                        <i class="fas fa-star text-warning"></i>
                                                                    @elseif ($i - 0.5 <= $review->rating)
                                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                                    @else
                                                                        <i class="far fa-star text-muted"></i>
                                                                    @endif
                                                                @endfor
                                                                <span class="ms-2">({{ $review->rating }})</span>
                                                            </div>
                                                            <small class="text-muted">
                                                                By: {{ $review->user->name }} | 
                                                                Product: {{ $review->product->name }} | 
                                                                Date: {{ $review->created_at }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn {{ $review->approved ? 'btn-warning' : 'btn-success' }}">
                                                        {{ $review->approved ? 'Unapprove' : 'Approve' }} Review
                                                    </button>
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
    </div>
@endsection
