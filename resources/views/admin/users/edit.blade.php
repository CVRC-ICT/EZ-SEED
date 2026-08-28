<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit User — {{ $user->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" value="Full Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $user->name) }}" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email', $user->email) }}" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="role_id" value="Role" />
                    <select id="role_id" name="role_id" class="mt-1 block w-full rounded-md border-gray-300"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }} required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <p class="text-xs text-gray-500 mt-1">You cannot change your own role.</p>
                    @endif
                    <x-input-error :messages="$errors->get('role_id')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="status" value="Status" />
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }} required>
                        <option value="active" @selected(old('status', $user->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $user->status) === 'inactive')>Inactive</option>
                    </select>
                    @if ($user->id === auth()->id())
                        <input type="hidden" name="status" value="{{ $user->status }}">
                        <p class="text-xs text-gray-500 mt-1">You cannot deactivate your own account.</p>
                    @endif
                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-primary-button>Save Changes</x-primary-button>
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
