@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">App Update</h1>
</div>

@if(session('update_success'))
    <div class="alert alert-success" role="alert">
        {{ session('update_success') }}
    </div>
@endif

@if(session('update_error'))
    <div class="alert alert-danger" role="alert">
        {{ session('update_error') }}
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Install New Version</h6>
    </div>
    <div class="card-body">
        @if($hasUpdate)
            <div class="alert alert-warning" role="alert">
                New version available: <strong>{{ $latestVersion }}</strong>. Please install/update to the latest version.
            </div>
        @elseif($isUpToDate === true)
            <div class="alert alert-success" role="alert">
                Updated: You are already using the latest version.
            </div>
        @elseif($updateError)
            <div class="alert alert-info" role="alert">
                {{ $updateError }}
            </div>
        @endif

        @if(!empty($localChanges))
            <div class="alert alert-danger" role="alert">
                <div class="font-weight-bold mb-2">Update blocked: uncommitted local changes were found.</div>
                <div class="mb-2">Commit or stash these files before installing a new version:</div>
                <pre class="bg-white border rounded p-2 mb-0" style="white-space: pre-wrap;">{{ implode("\n", $localChanges) }}</pre>
            </div>
        @endif

        <p class="mb-2 text-gray-700">
            Use this button after the programmer publishes a new version. It pulls the latest code from GitHub, installs dependencies, builds assets, and runs Laravel update tasks.
        </p>
        <p class="mb-4"><strong>Current Version:</strong> {{ $currentVersion }}</p>
        <p class="mb-4"><strong>Latest Version:</strong> {{ $latestVersion ?? 'Unavailable' }}</p>

        <div class="mb-3">
            <a href="{{ route('admin.update.index', ['refresh' => 1]) }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Latest Version
            </a>
        </div>

        <form method="POST" action="{{ route('admin.update.install') }}" onsubmit="return confirm('Install the new version now?');">
            @csrf
            @if($isUpToDate === true)
                <button type="button" class="btn btn-success" disabled>
                    <i class="fas fa-check-circle mr-1"></i> Already Updated
                </button>
            @elseif(!empty($localChanges))
                <button type="button" class="btn btn-secondary" disabled>
                    <i class="fas fa-exclamation-triangle mr-1"></i> Resolve Local Changes First
                </button>
            @else
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-download mr-1"></i> Install New Version
                </button>
            @endif
        </form>
    </div>
</div>

@if(session('update_logs'))
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Update Logs</h6>
        </div>
        <div class="card-body">
            @foreach(session('update_logs') as $log)
                <div class="mb-3">
                    <div><strong>{{ $log['label'] }}</strong> ({{ $log['command'] }}) - Exit Code: {{ $log['exit_code'] }}</div>
                    @if(!empty($log['output']))
                        <pre class="bg-light p-2 rounded mt-2 mb-0" style="white-space: pre-wrap;">{{ $log['output'] }}</pre>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
