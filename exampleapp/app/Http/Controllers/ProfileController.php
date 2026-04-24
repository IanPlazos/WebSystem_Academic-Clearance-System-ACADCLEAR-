<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $tenantLogoChanged = false;
        $tenantThemeChanged = false;

        unset($validated['profile_photo']);

        $request->user()->fill($validated);

        if ($request->hasFile('profile_photo')) {
            if ($request->user()->profile_photo_path) {
                Storage::disk('public')->delete($request->user()->profile_photo_path);
            }

            $request->user()->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        if ($request->user()->role === 'school_admin') {
            $request->validate([
                'tenant_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
                'remove_tenant_logo' => ['nullable', 'boolean'],
                'tenant_color_scheme' => ['nullable', 'string', 'in:ocean,forest,sunset'],
            ]);

            $tenantSlug = (string) $request->attributes->get('tenant_slug', data_get($request->attributes->get('tenant_details'), 'slug', ''));

            if ($tenantSlug !== '') {
                $branding = $this->loadTenantBranding($tenantSlug);
                $shouldRemove = $request->boolean('remove_tenant_logo');

                if ($shouldRemove || $request->hasFile('tenant_logo')) {
                    $this->deleteTenantLogoFiles($tenantSlug);
                    $tenantLogoChanged = true;
                    $branding['logo'] = null;
                }

                if ($request->hasFile('tenant_logo')) {
                    $extension = $request->file('tenant_logo')->getClientOriginalExtension() ?: 'png';
                    $logoPath = $request->file('tenant_logo')->storeAs('tenant-branding', $tenantSlug . '.' . strtolower($extension), 'public');
                    $branding['logo'] = $logoPath;
                    $tenantLogoChanged = true;
                }

                if ($request->filled('tenant_color_scheme')) {
                    $branding['color_scheme'] = $request->string('tenant_color_scheme')->toString();
                    $tenantThemeChanged = true;
                }

                $this->saveTenantBranding($tenantSlug, $branding);
            }
        }

        $request->user()->save();

        $redirect = Redirect::route('profile.edit')->with('status', 'profile-updated');

        if ($tenantLogoChanged) {
            $redirect->with('tenant_logo_status', 'tenant-logo-updated');
        }

        if ($tenantThemeChanged) {
            $redirect->with('tenant_theme_status', 'tenant-theme-updated');
        }

        return $redirect;
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    private function deleteTenantLogoFiles(string $tenantSlug): void
    {
        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $extension) {
            Storage::disk('public')->delete("tenant-branding/{$tenantSlug}.{$extension}");
        }
    }

    private function loadTenantBranding(string $tenantSlug): array
    {
        $path = "tenant-branding/{$tenantSlug}.json";

        if (!Storage::disk('public')->exists($path)) {
            return [];
        }

        $decoded = json_decode(Storage::disk('public')->get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function saveTenantBranding(string $tenantSlug, array $branding): void
    {
        Storage::disk('public')->put(
            "tenant-branding/{$tenantSlug}.json",
            json_encode($branding, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
