@extends('super-admin.layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">404 - Page Not Found</h1>
</div>

<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <h4 class="alert-heading">University Not Found</h4>
    <p>{{ $message ?? 'The university you are looking for has been deleted or does not exist.' }}</p>
    <hr>
    <a href="{{ route('super-admin.tenants.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Universities List
    </a>
</div>
@endsection
