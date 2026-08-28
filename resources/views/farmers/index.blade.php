<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Farmer Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">

                <div class="flex justify-between items-center mb-4">

                    <form method="GET" action="{{ route('farmers.index') }}" class="flex">
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search farmer..."
                            class="border rounded-l px-4 py-2"
                        >

                        <button
                            type="submit"
                            class="bg-green-600 text-white px-4 rounded-r hover:bg-green-700">
                            Search
                        </button>
                    </form>

                    <a href="{{ route('farmers.create') }}"
                       class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        + Add Farmer
                    </a>

                </div>

                <table class="min-w-full border">

                    <thead class="bg-green-600 text-white">

                    <tr>
                        <th class="p-2">RSBSA No.</th>
                        <th class="p-2">Farmer Name</th>
                        <th class="p-2">Province</th>
                        <th class="p-2">Municipality</th>
                        <th class="p-2">Barangay</th>
                        <th class="p-2">Actions</th>
                    </tr>

                    </thead>

                    <tbody>

                    @forelse($farmers as $farmer)

                        <tr class="border-b">

                            <td class="p-2">
                                {{ $farmer->rsbsa_number }}
                            </td>

                            <td class="p-2">
                                {{ $farmer->last_name }},
                                {{ $farmer->first_name }}
                            </td>

                            <td class="p-2">
                                {{ $farmer->province->name ?? '-' }}
                            </td>

                            <td class="p-2">
                                {{ $farmer->municipality->name ?? '-' }}
                            </td>

                            <td class="p-2">
                                {{ $farmer->barangay->name ?? '-' }}
                            </td>

                            <td class="p-2">

                                <a href="{{ route('farmers.show', $farmer) }}"
                                   class="text-blue-600">
                                    View
                                </a>

                                |

                                <a href="{{ route('farmers.edit', $farmer) }}"
                                   class="text-yellow-600">
                                    Edit
                                </a>

                                |

                                <form
                                    action="{{ route('farmers.destroy', $farmer) }}"
                                    method="POST"
                                    class="inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        onclick="return confirm('Delete this farmer?')"
                                        class="text-red-600">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center p-4">

                                No farmers found.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

                <div class="mt-4">
                    {{ $farmers->links() }}
                </div>

            </div>

        </div>
    </div>

</x-app-layout>