<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    /**
     * Show the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validated['profile_photo_path'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password updated successfully.');
    }

    /**
     * Delete the user's profile photo.
     */
    public function deleteProfilePhoto(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            // Delete the file from storage
            \Storage::disk('public')->delete($user->profile_photo_path);
            
            $user->update(['profile_photo_path' => null]);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile photo removed successfully.');
    }

    /**
     * Show user activity log.
     */
    public function activity()
    {
        $user = Auth::user();
        $activities = \App\Models\ActivityLog::where('causer_id', $user->id)
            ->where('causer_type', 'App\Models\User')
            ->latest()
            ->paginate(20);

        return view('profile.activity', compact('activities'));
    }

    /**
     * Show user notifications settings.
     */
    public function notifications()
    {
        $user = Auth::user();
        return view('profile.notifications', compact('user'));
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email_notifications' => ['nullable', 'boolean'],
            'sms_notifications' => ['nullable', 'boolean'],
            'push_notifications' => ['nullable', 'boolean'],
            'bill_reminders' => ['nullable', 'boolean'],
            'maintenance_updates' => ['nullable', 'boolean'],
            'security_alerts' => ['nullable', 'boolean'],
        ]);

        // Convert boolean values
        foreach ($validated as $key => $value) {
            $validated[$key] = (bool) $value;
        }

        $user->update($validated);

        return redirect()->route('profile.notifications')
            ->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Show connected devices.
     */
    public function devices()
    {
        $user = Auth::user();
        // Get user's active sessions/tokens if using Sanctum
        $tokens = $user->tokens ?? collect();

        return view('profile.devices', compact('tokens'));
    }

    /**
     * Revoke a device/token.
     */
    public function revokeDevice(Request $request, $tokenId)
    {
        $user = Auth::user();
        
        if ($user->tokens) {
            $user->tokens()->where('id', $tokenId)->delete();
        }

        return redirect()->route('profile.devices')
            ->with('success', 'Device access revoked successfully.');
    }

    /**
     * Show security settings.
     */
    public function security()
    {
        $user = Auth::user();
        return view('profile.security', compact('user'));
    }

    /**
     * Enable two-factor authentication.
     */
    public function enableTwoFactor(Request $request)
    {
        // Implement 2FA logic here
        // You might want to use laravel/fortify or another package
        
        return redirect()->route('profile.security')
            ->with('success', 'Two-factor authentication enabled.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactor(Request $request)
    {
        // Implement 2FA disable logic
        
        return redirect()->route('profile.security')
            ->with('success', 'Two-factor authentication disabled.');
    }
}