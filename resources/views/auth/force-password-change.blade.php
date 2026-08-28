<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Welcome to EZ-Seed. For security, please change your temporary password before continuing.
    </div>

    @if (session('error'))
        <div class="mb-4 text-sm text-red-600">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('password.force.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Current (Temporary) Password" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" required autofocus />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="New Password" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm New Password" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>Change Password</x-primary-button>
        </div>
    </form>
</x-guest-layout>
