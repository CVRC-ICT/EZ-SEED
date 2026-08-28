<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}</h2>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm p-6 space-y-3">
            <div><span class="text-gray-500 text-sm">Name:</span> <div class="font-medium">{{ $user->name }}</div></div>
            <div><span class="text-gray-500 text-sm">Email:</span> <div class="font-medium">{{ $user->email }}</div></div>
            <div><span class="text-gray-500 text-sm">Role:</span> <div class="font-medium">{{ $user->role?->name ?? '—' }}</div></div>
            <div>
                <span class="text-gray-500 text-sm">Status:</span>
                <div class="font-medium">
                    @if ($user->status === 'active')
                        <span class="text-green-700">● Active</span>
                    @else
                        <span class="text-gray-500">● Inactive</span>
                    @endif
                </div>
            </div>
            @if ($user->daPersonnel)
                <div><span class="text-gray-500 text-sm">Linked DA Personnel:</span> <div class="font-medium">{{ $user->daPersonnel->name }} — {{ $user->daPersonnel->position }}</div></div>
            @endif
            <div><span class="text-gray-500 text-sm">Created:</span> <div class="font-medium">{{ $user->created_at->format('F j, Y') }}</div></div>

            <div class="pt-4">
                <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-700 hover:underline text-sm">Edit</a>
                <a href="{{ route('admin.users.index') }}" class="ml-4 text-gray-600 hover:underline text-sm">Back to User Management</a>
            </div>
        </div>
    </div>
</x-app-layout>
