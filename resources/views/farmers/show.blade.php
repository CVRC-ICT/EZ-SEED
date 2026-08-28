<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Farmer Details
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">

                    <h3 class="text-2xl font-bold text-green-700">
                        {{ $farmer->last_name }}, {{ $farmer->first_name }}
                    </h3>

                    <a href="{{ route('dashboard.farmers') }}"
                       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Back
                    </a>

                </div>

                <!-- Personal Information -->
                <div class="mb-8">

                    <h4 class="text-lg font-semibold text-green-700 border-b pb-2 mb-4">
                        Personal Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <strong>RSBSA Number:</strong><br>
                            {{ $farmer->rsbsa_number ?? '-' }}
                        </div>

                        <div>
                            <strong>First Name:</strong><br>
                            {{ $farmer->first_name }}
                        </div>

                        <div>
                            <strong>Middle Name:</strong><br>
                            {{ $farmer->middle_name ?? '-' }}
                        </div>

                        <div>
                            <strong>Last Name:</strong><br>
                            {{ $farmer->last_name }}
                        </div>

                        <div>
                            <strong>Suffix:</strong><br>
                            {{ $farmer->suffix ?? '-' }}
                        </div>

                        <div>
                            <strong>Birth Date:</strong><br>
                            {{ $farmer->birth_date ?? '-' }}
                        </div>

                        <div>
                            <strong>Sex:</strong><br>
                            {{ $farmer->sex }}
                        </div>

                        <div>
                            <strong>Civil Status:</strong><br>
                            {{ $farmer->civil_status ?? '-' }}
                        </div>

                        <div>
                            <strong>Contact Number:</strong><br>
                            {{ $farmer->contact_number ?? '-' }}
                        </div>

                        <div>
                            <strong>Email:</strong><br>
                            {{ $farmer->email ?? '-' }}
                        </div>

                    </div>

                </div>

                <!-- Location -->
                <div class="mb-8">

                    <h4 class="text-lg font-semibold text-green-700 border-b pb-2 mb-4">
                        Location
                    </h4>

                    {{--
                        NOTE: switched from raw relation access
                        ($farmer->province->name) to the Farmer model's
                        province_name / municipality_name / barangay_name
                        accessors. Those accessors already handle:
                          - relation not eager-loaded (falls back to a query)
                          - relation loaded but null (optional() guards it)
                          - ñ/Ñ cleanup via PlaceName::clean()
                        If these still show "-", the underlying
                        province_id/municipality_id/barangay_id on this
                        farmer record are themselves null — check
                        FarmerController::show() and the DB row directly.
                    --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <div>
                            <strong>Province:</strong><br>
                            {{ $farmer->province_name ?? '-' }}
                        </div>

                        <div>
                            <strong>Municipality:</strong><br>
                            {{ $farmer->municipality_name ?? '-' }}
                        </div>

                        <div>
                            <strong>Barangay:</strong><br>
                            {{ $farmer->barangay_name ?? '-' }}
                        </div>

                    </div>

                </div>

                <!-- Farm Information -->
                <div>

                    <h4 class="text-lg font-semibold text-green-700 border-b pb-2 mb-4">
                        Farm Information
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div>
                            <strong>Farm Area:</strong><br>
                            {{ $farmer->farm_area }} ha
                        </div>

                        <div>
                            <strong>Tenurial Status:</strong><br>
                            {{ $farmer->tenurial_status ?? '-' }}
                        </div>

                    </div>

                </div>

                <div class="mt-8 flex gap-3">

                    <a href="{{ route('farmers.edit', $farmer) }}"
                       class="bg-yellow-500 text-white px-5 py-2 rounded hover:bg-yellow-600">
                        Edit
                    </a>

                    <form action="{{ route('farmers.destroy', $farmer) }}"
                          method="POST"
                          onsubmit="return confirm('Delete this farmer?')">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700">
                            Delete
                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>

</x-app-layout>