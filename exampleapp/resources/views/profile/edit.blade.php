@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success">Profile updated successfully.</div>
    @endif
    @if (session('status') === 'password-updated')
        <div class="alert alert-success">Password updated successfully.</div>
    @endif

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Overview</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            @if($user->profile_photo_url)
                                <img src="{{ $user->profile_photo_url }}" alt="Profile Photo" class="rounded-circle" style="width:64px; height:64px; object-fit:cover;">
                            @else
                                <i class="fas fa-user-circle fa-3x text-gray-300"></i>
                            @endif
                        </div>
                        <div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $user->name }}</div>
                            <div class="text-sm text-gray-600">{{ $user->email }}</div>
                            <div class="mt-1">
                                <span class="badge badge-info text-uppercase">{{ $user->role ?? 'user' }}</span>
                            </div>
                        </div>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-5 text-gray-600">Joined</dt>
                        <dd class="col-7 text-gray-800 mb-2">{{ optional($user->created_at)->format('M d, Y') }}</dd>

                        <dt class="col-5 text-gray-600">Last update</dt>
                        <dd class="col-7 text-gray-800 mb-0">{{ optional($user->updated_at)->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Profile</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div class="form-group">
                            <label for="profile_photo">Profile Picture</label>
                            <input
                                id="profile_photo"
                                name="profile_photo"
                                type="file"
                                class="form-control-file @error('profile_photo') is-invalid @enderror"
                                accept="image/png,image/jpeg,image/webp"
                            />
                            <small class="form-text text-muted">Allowed: JPG, PNG, WEBP (max 2MB)</small>
                            @error('profile_photo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                                autocomplete="name"
                            />
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                                autocomplete="username"
                            />
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Change Password</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        @method('put')

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input
                                id="current_password"
                                name="current_password"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}"
                                autocomplete="current-password"
                            />
                            @if ($errors->updatePassword->has('current_password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                            @if ($errors->updatePassword->has('password'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}"
                                autocomplete="new-password"
                            />
                            @if ($errors->updatePassword->has('password_confirmation'))
                                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
