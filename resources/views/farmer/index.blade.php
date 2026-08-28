<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Farmer Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-100 border border-green-400 text-green-700 px-4 py-3">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">

                    <form method="GET" action="{{ route('farmers.index') }}" class="flex gap-2">

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search by Name or RSBSA..."
                            class="border rounded-lg px-4 py-2 w-80">

                        <button
                            type="submit"
                            class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg">

                            Search

                        </button>

                    </form>

                    <a href="{{ route('farmers.create') }}"
                       class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg">

                        + Add Farmer

                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full border border-gray-200">

                        <thead class="bg-green-700 text-white">

                            <tr>

                                <th class="px-4 py-3 text-left">RSBSA</th>

                                <th class="px-4 py-3 text-left">Farmer Name</th>

                                <th class="px-4 py-3 text-left">Province</th>

                                <th class="px-4 py-3 text-left">Municipality</th>

                                <th class="px-4 py-3 text-left">Barangay</th>

                                <th class="px-4 py-3 text-center">Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        @forelse($farmers as $farmer)

                            <tr class="border-b hover:bg-gray-50">

                                <td class="px-4 py-3">
                                    {{ $farmer->rsbsa_number ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $farmer->last_name }},
                                    {{ $farmer->first_name }}
                                    {{ $farmer->middle_name }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $farmer->province->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $farmer->municipality->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $farmer->barangay->name ?? '-' }}
                                </td>

                                <td class="px-4 py-3 text-center">

                                    <a href="{{ route('farmers.show', $farmer) }}"
                                       class="text-blue-600 hover:underline">

                                        View

                                    </a>

                                    |

                                    <a href="{{ route('farmers.edit', $farmer) }}"
                                       class="text-yellow-600 hover:underline">

                                        Edit

                                    </a>

                                    |

                                    <form action="{{ route('farmers.destroy', $farmer) }}"
                                          method="POST"
                                          class="inline">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Delete this farmer?')"
                                            class="text-red-600 hover:underline">

                                            Delete

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center py-6">

                                    No registered farmers found.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                <div class="mt-6">

                    {{ $farmers->links() }}

                </div>

            </div>

        </div>
    </div>

</x-app-layout>