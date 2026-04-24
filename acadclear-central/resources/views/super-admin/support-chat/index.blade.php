@extends('super-admin.layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tenant Support Chat</h1>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Conversations</h6>
            </div>
            <div class="list-group list-group-flush" style="max-height: 70vh; overflow-y: auto;">
                @forelse($conversations as $conversation)
                    <a href="{{ route('super-admin.support-chat.show', $conversation) }}"
                       class="list-group-item list-group-item-action {{ optional($selectedConversation)->id === $conversation->id ? 'active' : '' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <strong>{{ $conversation->tenant_name ?: $conversation->tenant_slug }}</strong>
                            @if($conversation->unread_count > 0)
                                <span class="badge badge-danger">{{ $conversation->unread_count }}</span>
                            @endif
                        </div>
                        <small>{{ $conversation->tenant_slug }}</small>
                    </a>
                @empty
                    <div class="list-group-item text-muted">No support conversations yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    {{ $selectedConversation ? ('Chat with ' . ($selectedConversation->tenant_name ?: $selectedConversation->tenant_slug)) : 'Select a conversation' }}
                </h6>
            </div>
            <div class="card-body d-flex flex-column">
                @if($selectedConversation)
                    <div id="chatMessages" class="border rounded p-3 mb-3" style="height: 50vh; overflow-y: auto; background: #f8fafc;">
                        @foreach($messages as $message)
                            <div class="mb-3 {{ $message->sender_type === 'super_admin' ? 'text-right' : '' }}">
                                <div class="small text-muted mb-1">{{ $message->sender_name ?: ucfirst(str_replace('_', ' ', $message->sender_type)) }} • {{ $message->created_at?->diffForHumans() }}</div>
                                <div class="d-inline-block px-3 py-2 rounded {{ $message->sender_type === 'super_admin' ? 'bg-primary text-white' : 'bg-white border' }}" style="max-width: 85%;">
                                    {{ $message->message }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form id="chatForm" class="d-flex" autocomplete="off">
                        @csrf
                        <input type="text" class="form-control mr-2" id="chatInput" maxlength="2000" placeholder="Type your reply..." required>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </form>
                @else
                    <div class="text-muted">No conversation selected yet.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($selectedConversation)
@push('scripts')
<script>
(function () {
    var conversationId = {{ $selectedConversation->id }};
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
        var signature = messages.map(function (m) { return m.id + ':' + m.created_at; }).join('|');
        if (signature === lastSignature) {
            return;
        }

        lastSignature = signature;
        messagesEl.innerHTML = messages.map(function (m) {
            var mine = m.sender_type === 'super_admin';
            return '<div class="mb-3 ' + (mine ? 'text-right' : '') + '">' +
                '<div class="small text-muted mb-1">' + escapeHtml(m.sender_name || m.sender_type.replace('_', ' ')) + '</div>' +
                '<div class="d-inline-block px-3 py-2 rounded ' + (mine ? 'bg-primary text-white' : 'bg-white border') + '" style="max-width:85%;">' +
                escapeHtml(m.message) +
                '</div></div>';
        }).join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function loadMessages() {
        fetch('{{ route('super-admin.support-chat.messages', $selectedConversation) }}')
            .then(function (res) { return res.json(); })
            .then(function (data) {
                render(data.messages || []);
            })
            .catch(function () {});
    }

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();

        var payload = new URLSearchParams();
        payload.append('_token', '{{ csrf_token() }}');
        payload.append('message', inputEl.value.trim());

        if (!inputEl.value.trim()) {
            return;
        }

        fetch('{{ route('super-admin.support-chat.store', $selectedConversation) }}', {
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
@endif
@endsection
