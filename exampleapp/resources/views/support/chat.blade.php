@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Support Chat</h1>
</div>

<div class="card shadow-sm">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Live chat with Super Admin</h6>
        <span class="small text-muted">Tenant: {{ $tenantSlug }}</span>
    </div>
    <div class="card-body d-flex flex-column">
        <div id="chatMessages" class="border rounded p-3 mb-3" style="height: 52vh; overflow-y: auto; background: #f8fafc;">
            @forelse($messages as $message)
                <div class="mb-3 {{ ($message['sender_type'] ?? null) === 'tenant' ? 'text-right' : '' }}">
                    <div class="small text-muted mb-1">
                        {{ $message['sender_name'] ?? ucfirst(str_replace('_', ' ', $message['sender_type'] ?? 'unknown')) }}
                    </div>
                    <div class="d-inline-block px-3 py-2 rounded {{ ($message['sender_type'] ?? null) === 'tenant' ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 85%;">
                        {{ $message['message'] ?? '' }}
                    </div>
                </div>
            @empty
                <div class="text-muted">No messages yet. Start the conversation with support.</div>
            @endforelse
        </div>

        <form id="chatForm" class="d-flex" autocomplete="off">
            @csrf
            <input id="chatInput" type="text" class="form-control mr-2" maxlength="2000" placeholder="Type your message..." required>
            <button type="submit" class="btn btn-primary">Send</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var messagesEl = document.getElementById('chatMessages');
    var formEl = document.getElementById('chatForm');
    var inputEl = document.getElementById('chatInput');
    var lastSignature = '';

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function render(messages) {
        var signature = messages.map(function (m) { return (m.id || '') + ':' + (m.created_at || ''); }).join('|');
        if (signature === lastSignature) {
            return;
        }

        lastSignature = signature;

        if (!messages.length) {
            messagesEl.innerHTML = '<div class="text-muted">No messages yet. Start the conversation with support.</div>';
            return;
        }

        messagesEl.innerHTML = messages.map(function (m) {
            var mine = m.sender_type === 'tenant';
            return '<div class="mb-3 ' + (mine ? 'text-right' : '') + '">' +
                '<div class="small text-muted mb-1">' + escapeHtml(m.sender_name || (m.sender_type || '').replace('_', ' ')) + '</div>' +
                '<div class="d-inline-block px-3 py-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="max-width:85%;">' +
                escapeHtml(m.message) + '</div></div>';
        }).join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function loadMessages() {
        fetch('{{ route('support.chat.messages') }}')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                render(data.messages || []);
            })
            .catch(function () {});
    }

    formEl.addEventListener('submit', function (event) {
        event.preventDefault();

        var message = inputEl.value.trim();
        if (!message) {
            return;
        }

        var payload = new URLSearchParams();
        payload.append('_token', '{{ csrf_token() }}');
        payload.append('message', message);

        fetch('{{ route('support.chat.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload.toString(),
        })
            .then(function (res) { return res.json(); })
            .then(function () {
                inputEl.value = '';
                loadMessages();
            })
            .catch(function () {});
    });

    loadMessages();
    setInterval(loadMessages, 4000);
})();
</script>
@endpush
@endsection
