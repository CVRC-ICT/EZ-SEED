<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Settings</h2>
        <p class="text-xs text-gray-500 mt-0.5">Manage your account, notifications, and system preferences</p>
    </x-slot>

    <div class="flex">
        @include('dashboard._sidebar')

        <div class="flex-1 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto space-y-6">

                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ACCOUNT --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🛡️ Account</h3>
                    <form method="POST" action="{{ route('settings.account.update') }}" class="space-y-4">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-sm text-gray-600">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Role</label>
                            <input type="text" disabled value="{{ auth()->user()->role->name ?? '—' }}"
                                   class="mt-1 w-full rounded-full border-gray-200 bg-gray-50 text-sm text-gray-500">
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800">💾 Save</button>
                        </div>
                    </form>
                </div>

                {{-- NOTIFICATIONS --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🔔 Notifications</h3>
                    <form method="POST" action="{{ route('settings.notifications.update') }}" class="space-y-4">
                        @csrf @method('PATCH')
                        @foreach ([
                            'email_alerts' => ['Email Alerts', 'Receive alerts via email'],
                            'push_notifications' => ['Push Notifications', 'Browser push notifications'],
                            'portal_submission_alerts' => ['Portal Submission Alerts', 'New farmer portal submissions'],
                            'sync_error_alerts' => ['Sync Error Alerts', 'Notify when sync fails'],
                        ] as $field => [$label, $desc])
                            <label class="flex items-center justify-between">
                                <span>
                                    <span class="block text-sm font-medium text-gray-700">{{ $label }}</span>
                                    <span class="block text-xs text-gray-400">{{ $desc }}</span>
                                </span>
                                <input type="checkbox" name="{{ $field }}" value="1" @checked($settings->$field)
                                       class="h-5 w-9 rounded-full text-green-600 focus:ring-green-600">
                            </label>
                        @endforeach
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800">💾 Save</button>
                        </div>
                    </form>
                </div>

                {{-- DATA SYNC --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🔄 Data Sync</h3>
                    <form method="POST" action="{{ route('settings.data-sync.update') }}" class="space-y-4">
                        @csrf @method('PATCH')
                        <label class="flex items-center justify-between">
                            <span>
                                <span class="block text-sm font-medium text-gray-700">Auto-sync on Reconnect</span>
                                <span class="block text-xs text-gray-400">Automatically upload offline data when internet is restored</span>
                            </span>
                            <input type="checkbox" name="auto_sync_on_reconnect" value="1" @checked($settings->auto_sync_on_reconnect)
                                   class="h-5 w-9 rounded-full text-green-600 focus:ring-green-600">
                        </label>
                        <div>
                            <label class="text-sm text-gray-600">Sync Interval (minutes)</label>
                            <select name="sync_interval_minutes" class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                                @foreach ([5, 10, 15, 30, 60] as $mins)
                                    <option value="{{ $mins }}" @selected($settings->sync_interval_minutes == $mins)>Every {{ $mins }} minutes</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Farmer Portal API Endpoint</label>
                            <input type="url" name="portal_api_endpoint" value="{{ old('portal_api_endpoint', $settings->portal_api_endpoint) }}"
                                   placeholder="https://api.example.gov.ph/v1"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm font-mono focus:ring-green-600 focus:border-green-600">
                            <p class="text-xs text-gray-400 mt-1">Changes require a restart to take effect.</p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800">💾 Save</button>
                        </div>
                    </form>
                </div>

                {{-- EXPORT PREFERENCES --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">📄 Export Preferences</h3>
                    <form method="POST" action="{{ route('settings.export-preferences.update') }}">
                        @csrf @method('PATCH')
                        <label class="text-sm text-gray-600">Default Export Format</label>
                        <div class="grid grid-cols-3 gap-3 mt-2 mb-4">
                            @foreach (['PDF', 'EXCEL', 'CSV'] as $format)
                                <label class="flex items-center justify-center gap-2 border rounded-full py-2 text-sm cursor-pointer
                                              {{ $settings->default_export_format === $format ? 'border-green-600 text-green-700 font-medium' : 'border-gray-200 text-gray-500' }}">
                                    <input type="radio" name="default_export_format" value="{{ $format }}"
                                           @checked($settings->default_export_format === $format) class="hidden">
                                    {{ $format }}
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800">💾 Save</button>
                        </div>
                    </form>
                </div>

                {{-- PASSWORD --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">🔒 Security — Change Password</h3>
                    <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-4">
                        @csrf @method('PATCH')
                        <div>
                            <label class="text-sm text-gray-600">Current Password</label>
                            <input type="password" name="current_password"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                            @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">New Password</label>
                            <input type="password" name="password"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                   class="mt-1 w-full rounded-full border-gray-300 text-sm focus:ring-green-600 focus:border-green-600">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-5 py-2 bg-green-700 text-white text-sm font-medium rounded-full hover:bg-green-800">Update Password</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
