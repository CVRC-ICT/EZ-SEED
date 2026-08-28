<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    protected function settingsFor($user): UserSetting
    {
        return UserSetting::firstOrCreate(['user_id' => $user->id]);
    }

    public function edit()
    {
        $user = Auth::user();

        return view('dashboard.settings', [
            'isAdmin' => $user->isAdminOrSupervisor(),
            'settings' => $this->settingsFor($user),
        ]);
    }

    public function updateAccount(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        Auth::user()->update($request->only('name', 'email'));

        return back()->with('success', 'Account details updated.');
    }

    public function updateNotifications(Request $request)
    {
        $this->settingsFor(Auth::user())->update([
            'email_alerts' => $request->boolean('email_alerts'),
            'push_notifications' => $request->boolean('push_notifications'),
            'portal_submission_alerts' => $request->boolean('portal_submission_alerts'),
            'sync_error_alerts' => $request->boolean('sync_error_alerts'),
        ]);

        return back()->with('success', 'Notification preferences saved.');
    }

    public function updateDataSync(Request $request)
    {
        $request->validate([
            'sync_interval_minutes' => 'required|integer|min:1|max:1440',
            'portal_api_endpoint' => 'nullable|url',
        ]);

        $this->settingsFor(Auth::user())->update([
            'auto_sync_on_reconnect' => $request->boolean('auto_sync_on_reconnect'),
            'sync_interval_minutes' => $request->sync_interval_minutes,
            'portal_api_endpoint' => $request->portal_api_endpoint,
        ]);

        return back()->with('success', 'Data sync settings saved.');
    }

    public function updateExportPreferences(Request $request)
    {
        $request->validate([
            'default_export_format' => 'required|in:PDF,EXCEL,CSV',
        ]);

        $this->settingsFor(Auth::user())->update([
            'default_export_format' => $request->default_export_format,
        ]);

        return back()->with('success', 'Export preference saved.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated.');
    }
}
