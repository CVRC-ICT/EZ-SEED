<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">User Management</h2>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-700 text-white text-sm font-medium rounded-md hover:bg-green-800">
                + Create User
            </a>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('temp_password'))
            <div class="p-4 bg-amber-50 border border-amber-200 text-amber-900 rounded-md text-sm">
                Temporary password generated: <span class="font-mono font-semibold">{{ session('temp_password') }}</span>
                — share this with the user securely. It will not be shown again, and they must change it on next login.
            </div>
        @endif

        {{-- Search / Filter --}}
        <form method="GET" class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name or email..."
                       class="w-full rounded-md border-gray-300 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Role</label>
                <select name="role" class="rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm font-medium">
                Filter
            </button>
        </form>

        {{-- Desktop table --}}
        <div class="hidden md:block bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase">
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $user->role?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if ($user->status === 'active')
                                    <span class="inline-flex items-center gap-1 text-green-700">● Active</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-gray-500">● Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600 hover:underline">View</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-700 hover:underline">Edit</a>

                                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Reset this user\'s password?');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-amber-700 hover:underline">Reset Password</button>
                                </form>

                                @if ($user->status === 'active')
                                    <form action="{{ route('admin.users.deactivate', $user) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Deactivate {{ $user->name }}? They will no longer be able to access the DA Dashboard.');">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-red-700 hover:underline">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.reactivate', $user) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-green-700 hover:underline">Reactivate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden space-y-3">
            @foreach ($users as $user)
                <div class="bg-white rounded-lg shadow-sm p-4 space-y-1">
                    <div class="font-semibold text-gray-900">{{ $user->name }}</div>
                    <div class="text-sm text-gray-600">Email: {{ $user->email }}</div>
                    <div class="text-sm text-gray-600">Role: {{ $user->role?->name ?? '—' }}</div>
                    <div class="text-sm">
                        Status:
                        @if ($user->status === 'active')
                            <span class="text-green-700">● Active</span>
                        @else
                            <span class="text-gray-500">● Inactive</span>
                        @endif
                    </div>
                    <div class="pt-2 flex gap-3 text-sm">
                        <a href="{{ route('admin.users.show', $user) }}" class="text-gray-600">View</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-700">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
