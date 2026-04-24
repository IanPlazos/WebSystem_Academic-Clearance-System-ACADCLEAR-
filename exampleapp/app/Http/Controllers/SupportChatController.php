<?php

namespace App\Http\Controllers;

use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportChatController extends Controller
{
    public function __construct(private TenantService $tenantService)
    {
    }

    public function index(Request $request)
    {
        $tenantSlug = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());
        $chatPayload = $this->tenantService->getSupportChatMessages($tenantSlug);

        return view('support.chat', [
            'tenantSlug' => $tenantSlug,
            'messages' => $chatPayload['messages'] ?? [],
            'conversation' => $chatPayload['conversation'] ?? null,
        ]);
    }

    public function messages(Request $request): JsonResponse
    {
        $tenantSlug = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());
        $chatPayload = $this->tenantService->getSupportChatMessages($tenantSlug);

        return response()->json([
            'messages' => $chatPayload['messages'] ?? [],
            'conversation' => $chatPayload['conversation'] ?? null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $tenantSlug = (string) ($request->attributes->get('tenant_slug') ?: $this->tenantService->getCurrentTenant());

        $response = $this->tenantService->sendSupportChatMessage(
            $tenantSlug,
            $validated['message'],
            $request->user()?->name,
            $request->user()?->id
        );

        return response()->json($response, 201);
    }
}
