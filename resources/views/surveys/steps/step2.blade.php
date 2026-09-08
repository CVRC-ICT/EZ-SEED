@extends('surveys.wizard', [
    'currentStep' => 2,
    'totalSteps' => 10,
    'stepTitle' => 'Farmer Socio-Demographic Profile',
])

@section('content')
<form id="wizardStepForm" method="POST"
      @isset($farmer)
      action="{{ route('surveys.step.store', ['farmer' => $farmer, 'step' => 2]) }}"
      @endisset
      novalidate>
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- Card Header --}}
        <div class="bg-da-green-50 border-b border-da-green-100 px-6 sm:px-8 py-5">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-11 h-11 rounded-full bg-da-green-600 text-white flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Farmer Socio-Demographic Profile</h2>
                    <p class="text-sm text-gray-600">Personal information of the farmer-respondent.</p>
                </div>
                <div id="syncStatus" class="flex-shrink-0 text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-100 text-gray-500">
                    &nbsp;
                </div>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-7 space-y-8">

            {{-- RSBSA --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Is the farmer an RSBSA member? <span class="text-red-600">*</span>
                </h3>
                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                    <div class="flex gap-3 flex-shrink-0" role="radiogroup">
                        <label class="relative">
                            <input type="radio" name="is_rsbsa_member" value="yes" class="peer sr-only"
                                   id="is_rsbsa_member_yes"
                                   onchange="document.getElementById('rsbsaNumberField').classList.remove('hidden'); document.getElementById('rsbsa_number').setAttribute('required','required');"
                                   {{ old('is_rsbsa_member', data_get($old_data, 'is_rsbsa_member')) == 'yes' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-6 py-2.5 flex items-center justify-center transition">
                                <span class="font-semibold text-gray-800 text-base">Yes</span>
                            </div>
                        </label>
                        <label class="relative">
                            <input type="radio" name="is_rsbsa_member" value="no" class="peer sr-only"
                                   id="is_rsbsa_member_no"
                                   onchange="document.getElementById('rsbsaNumberField').classList.add('hidden'); document.getElementById('rsbsa_number').removeAttribute('required'); document.getElementById('rsbsa_number').value='';"
                                   {{ old('is_rsbsa_member', data_get($old_data, 'is_rsbsa_member')) == 'no' ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-6 py-2.5 flex items-center justify-center transition">
                                <span class="font-semibold text-gray-800 text-base">No</span>
                            </div>
                        </label>
                    </div>

                    <div id="rsbsaNumberField" class="flex-1 min-w-0 {{ old('is_rsbsa_member', data_get($old_data, 'is_rsbsa_member')) == 'yes' ? '' : 'hidden' }}">
                        <input type="text" id="rsbsa_number" name="rsbsa_number"
                               value="{{ old('rsbsa_number', data_get($old_data, 'rsbsa_number')) }}"
                               {{ old('is_rsbsa_member', data_get($old_data, 'is_rsbsa_member')) == 'yes' ? 'required' : '' }}
                               placeholder="RSBSA Number, e.g. 02-1234-56789-000001"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('rsbsa_number') border-red-500 @enderror">
                        @error('rsbsa_number')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @error('is_rsbsa_member')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            <hr class="border-gray-200">

            {{-- Full Name --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Full Name</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
                    <div class="sm:col-span-1">
                        <label for="first_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            First Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="first_name" name="first_name"
                               value="{{ old('first_name', data_get($old_data, 'first_name')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <label for="middle_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Middle Name
                        </label>
                        <input type="text" id="middle_name" name="middle_name"
                               value="{{ old('middle_name', data_get($old_data, 'middle_name')) }}"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('middle_name') border-red-500 @enderror">
                        @error('middle_name')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <label for="last_name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Last Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="last_name" name="last_name"
                               value="{{ old('last_name', data_get($old_data, 'last_name')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-1">
                        <label for="suffix" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Suffix
                        </label>
                        <select id="suffix" name="suffix"
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('suffix') border-red-500 @enderror">
                            <option value="" {{ old('suffix', data_get($old_data, 'suffix')) == '' ? 'selected' : '' }}>None</option>
                            @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV'] as $suffix)
                                <option value="{{ $suffix }}" {{ old('suffix', data_get($old_data, 'suffix')) == $suffix ? 'selected' : '' }}>
                                    {{ $suffix }}
                                </option>
                            @endforeach
                        </select>
                        @error('suffix')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Farmer's Address --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Farmer's Address</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-5">
                    <div>
                        <label for="farmer_province_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Province <span class="text-red-600">*</span>
                        </label>
                        <select id="farmer_province_id" name="farmer_province_id" required
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farmer_province_id') border-red-500 @enderror">
                            <option value="" disabled {{ old('farmer_province_id', data_get($old_data, 'farmer_province_id')) ? '' : 'selected' }}>Select Province</option>
                            @foreach ($provinces as $province)
                                <option value="{{ $province->id }}" {{ old('farmer_province_id', data_get($old_data, 'farmer_province_id')) == $province->id ? 'selected' : '' }}>
                                    {{ \App\Support\PlaceName::clean($province->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('farmer_province_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farmer_municipality_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Municipality <span class="text-red-600">*</span>
                        </label>
                        <select id="farmer_municipality_id" name="farmer_municipality_id" disabled required
                                class="w-full rounded-lg border-gray-300 bg-gray-50 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farmer_municipality_id') border-red-500 @enderror">
                            <option value="">Select province first</option>
                        </select>
                        @error('farmer_municipality_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="farmer_barangay_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Barangay <span class="text-red-600">*</span>
                        </label>
                        <select id="farmer_barangay_id" name="farmer_barangay_id" disabled required
                                class="w-full rounded-lg border-gray-300 bg-gray-50 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farmer_barangay_id') border-red-500 @enderror">
                            <option value="">Select municipality first</option>
                        </select>
                        @error('farmer_barangay_id')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p id="farmerAddressLocationHint" class="mb-3 hidden text-xs text-amber-600">Loading location options&hellip;</p>

                <div>
                    <label for="farmer_address_line" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Street / Sitio / Purok
                    </label>
                    <input type="text" id="farmer_address_line" name="farmer_address_line"
                           value="{{ old('farmer_address_line', data_get($old_data, 'farmer_address_line')) }}"
                           placeholder="e.g. Purok 3, Sitio Mabuhay"
                           class="w-full sm:w-2/3 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('farmer_address_line') border-red-500 @enderror">
                    @error('farmer_address_line')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Personal Details --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Personal Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="birthdate" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Birthdate <span class="text-red-600">*</span>
                        </label>
                        <input type="date" id="birthdate" name="birthdate"
                               value="{{ old('birthdate', data_get($old_data, 'birthdate')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('birthdate') border-red-500 @enderror">
                        @error('birthdate')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="age" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Age <span class="text-red-600">*</span>
                        </label>
                        <input type="number" id="age" name="age" min="18" max="120"
                               value="{{ old('age', data_get($old_data, 'age')) }}" required
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('age') border-red-500 @enderror">
                        @error('age')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- CHANGED: Contact Number is now optional — required
                         attribute removed, red asterisk swapped for an
                         "(optional)" label so it matches the backend rule. --}}
                    <div>
                        <label for="contact_number" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Contact Number <span class="text-xs font-normal text-gray-500">(optional)</span>
                        </label>
                        <input type="tel" id="contact_number" name="contact_number"
                               value="{{ old('contact_number', data_get($old_data, 'contact_number')) }}"
                               placeholder="09XXXXXXXXX"
                               class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('contact_number') border-red-500 @enderror">
                        @error('contact_number')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Sex --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Sex <span class="text-red-600">*</span>
                </h3>
                <div class="flex flex-col sm:flex-row gap-4" role="radiogroup">
                    <label class="flex-1 relative">
                        <input type="radio" name="sex" value="male" class="peer sr-only"
                               {{ old('sex', data_get($old_data, 'sex')) == 'male' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3.5 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Male</span>
                        </div>
                    </label>
                    <label class="flex-1 relative">
                        <input type="radio" name="sex" value="female" class="peer sr-only"
                               {{ old('sex', data_get($old_data, 'sex')) == 'female' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3.5 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Female</span>
                        </div>
                    </label>
                </div>
                @error('sex')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>

            <hr class="border-gray-200">

            {{-- Civil Status, Education --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-4">Background Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div>
                        <label for="civil_status" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Civil Status <span class="text-red-600">*</span>
                        </label>
                        <select id="civil_status" name="civil_status" required
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('civil_status') border-red-500 @enderror">
                            <option value="" disabled {{ old('civil_status', data_get($old_data, 'civil_status')) ? '' : 'selected' }}>Select Status</option>
                            @foreach (['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $status)
                                <option value="{{ $status }}" {{ old('civil_status', data_get($old_data, 'civil_status')) == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('civil_status')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="educational_attainment" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Educational Attainment <span class="text-red-600">*</span>
                        </label>
                        <select id="educational_attainment" name="educational_attainment" required
                                class="w-full rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('educational_attainment') border-red-500 @enderror">
                            <option value="" disabled {{ old('educational_attainment', data_get($old_data, 'educational_attainment')) ? '' : 'selected' }}>Select Attainment</option>
                            @foreach ([
                                'No Formal Education',
                                'Elementary Undergraduate',
                                'Elementary Graduate',
                                'High School Undergraduate',
                                'High School Graduate',
                                'Vocational',
                                'College Undergraduate',
                                'College Graduate',
                                'Post Graduate',
                            ] as $level)
                                <option value="{{ $level }}" {{ old('educational_attainment', data_get($old_data, 'educational_attainment')) == $level ? 'selected' : '' }}>
                                    {{ $level }}
                                </option>
                            @endforeach
                        </select>
                        @error('educational_attainment')
                            <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Ethnicity --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Ethnicity <span class="text-red-600">*</span>
                </h3>
                <div class="flex flex-wrap gap-3" role="radiogroup">
                    @foreach (['Ilokano', 'Tagalog', 'Itawes'] as $ethnic)
                        <label class="relative">
                            <input type="radio" name="ethnicity" value="{{ $ethnic }}" class="peer sr-only"
                                   {{ old('ethnicity', data_get($old_data, 'ethnicity')) == $ethnic ? 'checked' : '' }}>
                            <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 transition">
                                <span class="font-semibold text-gray-800 text-base">{{ $ethnic }}</span>
                            </div>
                        </label>
                    @endforeach
                    <label class="relative">
                        <input type="radio" name="ethnicity" value="others" class="peer sr-only"
                               id="ethnicity_others_radio"
                               onchange="document.getElementById('ethnicityOthersField').classList.toggle('hidden', this.value !== 'others')"
                               {{ old('ethnicity', data_get($old_data, 'ethnicity')) == 'others' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3 transition">
                            <span class="font-semibold text-gray-800 text-base">Others (specify)</span>
                        </div>
                    </label>
                </div>
                @error('ethnicity')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <div id="ethnicityOthersField" class="mt-4 {{ old('ethnicity', data_get($old_data, 'ethnicity')) == 'others' ? '' : 'hidden' }}">
                    <label for="ethnicity_others" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Please specify ethnicity
                    </label>
                    <input type="text" id="ethnicity_others" name="ethnicity_others"
                           value="{{ old('ethnicity_others', data_get($old_data, 'ethnicity_others')) }}"
                           class="w-full sm:w-1/2 rounded-lg border-gray-300 focus:border-da-green-600 focus:ring-da-green-600 text-base py-2.5 px-3.5 @error('ethnicity_others') border-red-500 @enderror">
                    @error('ethnicity_others')
                        <p class="mt-1.5 text-sm text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <hr class="border-gray-200">

            {{-- Crop Type --}}
            <section>
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                    Which crop is this survey covering for this farmer? <span class="text-red-600">*</span>
                </h3>
                <div class="flex flex-col sm:flex-row gap-4" role="radiogroup">
                    <label class="flex-1 relative">
                        <input type="radio" name="crop_type" value="rice" class="peer sr-only"
                               {{ old('crop_type', data_get($old_data, 'crop_type')) == 'rice' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3.5 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Rice</span>
                        </div>
                    </label>
                    <label class="flex-1 relative">
                        <input type="radio" name="crop_type" value="corn" class="peer sr-only"
                               {{ old('crop_type', data_get($old_data, 'crop_type')) == 'corn' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3.5 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Corn</span>
                        </div>
                    </label>
                    <label class="flex-1 relative">
                        <input type="radio" name="crop_type" value="both" class="peer sr-only"
                               {{ old('crop_type', data_get($old_data, 'crop_type')) == 'both' ? 'checked' : '' }}>
                        <div class="cursor-pointer rounded-xl border-2 border-gray-300 peer-checked:border-da-green-600 peer-checked:bg-da-green-50 peer-checked:ring-2 peer-checked:ring-da-green-200 px-5 py-3.5 flex items-center justify-center gap-2 transition">
                            <span class="font-semibold text-gray-800 text-base">Both</span>
                        </div>
                    </label>
                </div>
                @error('crop_type')
                    <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </section>
        </div>

        {{-- Navigation --}}
        <div class="bg-gray-50 border-t border-gray-200 px-6 sm:px-8 py-5 flex flex-col-reverse sm:flex-row items-center justify-between gap-3">
            <a href="{{ isset($farmer)
                    ? route('surveys.step.show', ['farmer' => $farmer, 'step' => 1])
                    : route('surveys.local.step.show', ['uuid' => $uuid, 'step' => 1]) }}"
               class="w-full sm:w-auto text-center px-6 py-3.5 rounded-xl border-2 border-gray-300 text-gray-700 font-semibold text-base hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back
            </a>
            <button type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-da-green-600 text-white font-semibold text-base shadow-sm hover:bg-da-green-700 focus:outline-none focus:ring-2 focus:ring-da-green-500 focus:ring-offset-2 transition flex items-center justify-center gap-2">
                Save &amp; Continue
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    // Auto-calculate age from birthdate
    document.getElementById('birthdate').addEventListener('change', function () {
        const ageField = document.getElementById('age');
        if (!this.value) return;
        const birthDate = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        if (age >= 0) {
            ageField.value = age;
        }
    });

    // Farmer's Address cascading Province -> Municipality -> Barangay.
    (function () {
        const provinceSelect = document.getElementById('farmer_province_id');
        const municipalitySelect = document.getElementById('farmer_municipality_id');
        const barangaySelect = document.getElementById('farmer_barangay_id');
        const hint = document.getElementById('farmerAddressLocationHint');

        function resetSelect(select, placeholder, enable) {
            select.innerHTML = `<option value="">${placeholder}</option>`;
            select.disabled = !enable;
            select.classList.toggle('bg-gray-50', !enable);
        }

        function populateSelect(select, items, selectedId) {
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                if (selectedId && String(item.id) === String(selectedId)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        async function fetchJson(url) {
            hint.classList.remove('hidden');
            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Request failed');
                return await response.json();
            } catch (err) {
                console.error('Location lookup failed:', err);
                return [];
            } finally {
                hint.classList.add('hidden');
            }
        }

        async function loadMunicipalities(provinceId, selectedMunicipalityId) {
            resetSelect(municipalitySelect, 'Loading…', false);
            resetSelect(barangaySelect, 'Select municipality first', false);
            if (!provinceId) {
                resetSelect(municipalitySelect, 'Select province first', false);
                return;
            }
            const municipalities = await fetchJson(`/municipalities/${provinceId}`);
            resetSelect(municipalitySelect, 'Select municipality', true);
            populateSelect(municipalitySelect, municipalities, selectedMunicipalityId);
            if (selectedMunicipalityId) {
                await loadBarangays(selectedMunicipalityId, barangaySelect.dataset.old);
            }
        }

        async function loadBarangays(municipalityId, selectedBarangayId) {
            resetSelect(barangaySelect, 'Loading…', false);
            if (!municipalityId) {
                resetSelect(barangaySelect, 'Select municipality first', false);
                return;
            }
            const barangays = await fetchJson(`/barangays/${municipalityId}`);
            resetSelect(barangaySelect, 'Select barangay', true);
            populateSelect(barangaySelect, barangays, selectedBarangayId);
        }

        provinceSelect.addEventListener('change', (e) => loadMunicipalities(e.target.value));
        municipalitySelect.addEventListener('change', (e) => loadBarangays(e.target.value));

        document.addEventListener('DOMContentLoaded', () => {
            const oldProvince = provinceSelect.dataset.old;
            const oldMunicipality = municipalitySelect.dataset.old;
            if (oldProvince) {
                loadMunicipalities(oldProvince, oldMunicipality);
            }
        });
    })();
</script>

@isset($farmer)
    @include('surveys.partials.offline-sync-init', [
        'step' => 2,
        'nextUrl' => route('surveys.step.show', ['farmer' => $farmer, 'step' => 3]),
        'hasServerData' => !empty($old_data),
    ])
@else
    @include('surveys.partials.local-first-init', [
        'uuid' => $uuid,
        'step' => 2,
    ])
@endisset
@endpush
@endsection
