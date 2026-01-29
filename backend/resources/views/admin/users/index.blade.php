@extends('admin.layouts.app')
@section('title', 'Users')
@section('content-dashboard')
    <div class="row mb-5">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
        <div class="row mt-2">
            <div class="col-12">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="mt-2">
                        Users ({{ $users->count() }})
                    </h3>
                </div>
                <hr>
                <!-- Filter tabs -->
                <div class="mb-3">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm {{ !request()->has('filter') || request()->get('filter') !== 'deleted' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Active Users
                    </a>
                    <a href="{{ route('admin.users.index', ['filter' => 'deleted']) }}" class="btn btn-sm {{ request()->get('filter') === 'deleted' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Deleted Users
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>ID</td>
                                    <td>Name</td>
                                    <td>Email</td>
                                    <td>Profile Image</td>
                                    <td>Registered</td>
                                    <td>Actions</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $key => $user)
                                    <tr>
                                        <td>{{ $key += 1 }}</td>
                                        <td>#{{ $user->id }}</td>
                                        <td width="150px">
                                            <strong>{{ $user->name }}</strong>
                                        </td>
                                        <td width="200px">
                                            {{ $user->email }}
                                        </td>
                                        <td width="100px">
                                            @if ($user->profile_image)
                                                <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" width="50" height="50" class="img-fluid rounded-circle" style="object-fit: cover;">
                                            @else
                                                <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" width="50" height="50" class="img-fluid rounded-circle" style="object-fit: cover;">
                                            @endif
                                        </td>
                                        <td width="150px">{{ $user->created_at }}</td>
                                        <td width="100px">
                                            @if (request()->get('filter') === 'deleted')
                                                <!-- Restore button for deleted users -->
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#restoreUserModal{{ $user->id }}">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @else
                                                <!-- Delete button for active users -->
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @if (request()->get('filter') === 'deleted')
                                        <!-- Restore User Modal -->
                                        <div class="modal fade" id="restoreUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="restoreUserModalLabel{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="restoreUserModalLabel{{ $user->id }}">
                                                            Restore User #{{ $user->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('POST')
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to restore this user?</p>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        @if ($user->profile_image)
                                                                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" width="60" height="60" class="img-fluid rounded-circle me-3" style="object-fit: cover;">
                                                                        @else
                                                                            <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" width="60" height="60" class="img-fluid rounded-circle me-3" style="object-fit: cover;">
                                                                        @endif
                                                                        <div>
                                                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                                                            <small class="text-muted">{{ $user->email }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        Registered: {{ $user->created_at }}<br>
                                                                        Deleted: {{ $user->deleted_at }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="alert alert-info mt-3">
                                                                <small><i class="fas fa-info-circle"></i> This will restore the user and they will be able to login again.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-success">Restore User</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Delete User Modal -->
                                        <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteUserModalLabel{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteUserModalLabel{{ $user->id }}">
                                                            Delete User #{{ $user->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this user?</p>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <div class="d-flex align-items-center mb-3">
                                                                        @if ($user->profile_image)
                                                                            <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" width="60" height="60" class="img-fluid rounded-circle me-3" style="object-fit: cover;">
                                                                        @else
                                                                            <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" width="60" height="60" class="img-fluid rounded-circle me-3" style="object-fit: cover;">
                                                                        @endif
                                                                        <div>
                                                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                                                            <small class="text-muted">{{ $user->email }}</small>
                                                                        </div>
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        Registered: {{ $user->created_at }}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            <div class="alert alert-warning mt-3">
                                                                <small><i class="fas fa-exclamation-triangle"></i> This will soft delete the user. Their profile image, orders, and reviews will remain in the database.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Delete User</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif                                                                        

                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
