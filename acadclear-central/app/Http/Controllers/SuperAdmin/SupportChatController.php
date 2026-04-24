<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function index()
    {
        $conversations = SupportConversation::query()
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('sender_type', 'tenant')->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $selectedConversation = $conversations->first();

        $messages = collect();
        if ($selectedConversation) {
            $messages = $selectedConversation->messages()
                ->orderBy('created_at')
                ->limit(200)
                ->get();

            $selectedConversation->messages()
                ->where('sender_type', 'tenant')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        return view('super-admin.support-chat.index', compact('conversations', 'selectedConversation', 'messages'));
    }

    public function show(SupportConversation $conversation)
    {
        $conversations = SupportConversation::query()
            ->withCount(['messages as unread_count' => function ($query) {
                $query->where('sender_type', 'tenant')->where('is_read', false);
            }])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->limit(200)
            ->get();

        $conversation->messages()
            ->where('sender_type', 'tenant')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $selectedConversation = $conversation;

        return view('super-admin.support-chat.index', compact('conversations', 'selectedConversation', 'messages'));
    }

    public function messages(SupportConversation $conversation): JsonResponse
    {
        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->limit(200)
            ->get(['id', 'sender_type', 'sender_name', 'message', 'created_at']);

        $conversation->messages()
            ->where('sender_type', 'tenant')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, SupportConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_type' => 'super_admin',
            'sender_name' => $request->user()?->name ?? 'Super Admin',
            'sender_user_id' => $request->user()?->id,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();

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
}
