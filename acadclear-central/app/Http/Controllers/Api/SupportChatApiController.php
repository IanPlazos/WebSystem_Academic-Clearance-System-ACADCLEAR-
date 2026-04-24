<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatApiController extends Controller
{
    public function summary(Request $request, string $tenantSlug): JsonResponse
    {
        $this->authorizeTokenIfConfigured($request);

        $conversation = $this->findOrCreateConversation($tenantSlug);

        $unreadCount = $conversation->messages()
            ->where('sender_type', 'super_admin')
            ->where('is_read', false)
            ->count();

        $recentMessages = $conversation->messages()
            ->where('sender_type', 'super_admin')
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'sender_type', 'sender_name', 'message', 'created_at'])
            ->reverse()
            ->values();

        return response()->json([
            'unread_count' => $unreadCount,
            'recent_messages' => $recentMessages,
        ]);
    }

    public function messages(Request $request, string $tenantSlug): JsonResponse
    {
        $this->authorizeTokenIfConfigured($request);

        $conversation = $this->findOrCreateConversation($tenantSlug);

        // Tenant has opened chat; mark super admin messages as read for this conversation.
        $conversation->messages()
            ->where('sender_type', 'super_admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->limit(200)
            ->get(['id', 'sender_type', 'sender_name', 'message', 'created_at']);

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'tenant_slug' => $conversation->tenant_slug,
                'tenant_name' => $conversation->tenant_name,
                'status' => $conversation->status,
                'last_message_at' => optional($conversation->last_message_at)->toISOString(),
            ],
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, string $tenantSlug): JsonResponse
    {
        $this->authorizeTokenIfConfigured($request);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'sender_user_id' => ['nullable', 'integer'],
        ]);

        $conversation = $this->findOrCreateConversation($tenantSlug);

        $message = $conversation->messages()->create([
            'sender_type' => 'tenant',
            'sender_name' => $validated['sender_name'] ?? $conversation->tenant_name ?? $tenantSlug,
            'sender_user_id' => $validated['sender_user_id'] ?? null,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender_name,
                'message' => $message->message,
                'created_at' => $message->created_at?->toISOString(),
            ],
        ], 201);
    }

    private function findOrCreateConversation(string $tenantSlug): SupportConversation
    {
        $tenant = Tenant::query()
            ->where('slug', $tenantSlug)
            ->orWhere('domain', $tenantSlug)
            ->first();

        return SupportConversation::firstOrCreate(
            ['tenant_slug' => $tenantSlug],
            [
                'tenant_name' => $tenant?->name,
                'tenant_domain' => $tenant?->domain,
                'status' => 'open',
                'last_message_at' => now(),
            ]
        );
    }

    private function authorizeTokenIfConfigured(Request $request): void
    {
        $expected = (string) config('services.support_chat.token', '');
        if ($expected === '') {
            return;
        }

        $provided = (string) $request->header('X-Support-Token', '');
        abort_unless(hash_equals($expected, $provided), 403, 'Invalid support chat token.');
    }
}
