@extends('admin.layouts.app')
@section('title', 'Update Password')
@section('content-dashboard')
    <div class="row">
        @include('admin.layouts.sidebar')
        <div class="col-md-9">
            <div class="d-flex justify-content-center align-items-start py-5 px-3">
                <div class="card shadow-sm border w-100" style="max-width: 420px;">
                    <div class="card-header bg-white text-center border-bottom py-3">
                        <h4 class="mb-0">
                            <i class="fas fa-key me-2"></i>
                            Update Password
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('admin.password.update') }}" method="post">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Current Password</label>
                                <div class="position-relative">
                                    <input
                                        type="password"
                                        class="form-control pe-5 @error('old_password') is-invalid @enderror"
                                        name="old_password"
                                        id="old_password"
                                        placeholder="Enter current password"
                                        autofocus>
                                    <button
                                        type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-secondary"
                                        style="border: none; background: none; z-index: 10;"
                                        onclick="togglePasswordVisibility('old_password', this)"
                                        aria-label="Show or hide current password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('old_password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="position-relative">
                                    <input
                                        type="password"
                                        class="form-control pe-5 @error('new_password') is-invalid @enderror"
                                        name="new_password"
                                        id="new_password"
                                        placeholder="Enter new password">
                                    <button
                                        type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-secondary"
                                        style="border: none; background: none; z-index: 10;"
                                        onclick="togglePasswordVisibility('new_password', this)"
                                        aria-label="Show or hide new password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('new_password')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="position-relative">
                                    <input
                                        type="password"
                                        class="form-control pe-5"
                                        name="new_password_confirmation"
                                        id="new_password_confirmation"
                                        placeholder="Confirm new password">
                                    <button
                                        type="button"
                                        class="btn btn-link position-absolute end-0 top-50 translate-middle-y pe-3 text-secondary"
                                        style="border: none; background: none; z-index: 10;"
                                        onclick="togglePasswordVisibility('new_password_confirmation', this)"
                                        aria-label="Show or hide confirm password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark">
                                    <i class="fas fa-key me-1"></i>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // ✅ ADDED: same show/hide pattern as storefront Register.vue (bi-eye / bi-eye-slash)
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
@endsection
