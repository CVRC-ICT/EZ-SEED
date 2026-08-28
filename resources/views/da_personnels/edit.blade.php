<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit DA Personnel
    </h2>
</x-slot>


<div class="py-6">

<div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">


@if ($errors->any())

<div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">

<ul>

@foreach($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif



<form method="POST" 
action="{{ route('da-personnels.update', $da_personnel->id) }}">

@csrf
@method('PUT')



<div class="mb-4">

<label class="block font-semibold">
Name
</label>

<input 
type="text"
name="name"
value="{{ old('name', $da_personnel->name) }}"
class="w-full border rounded p-2">

</div>




<div class="mb-4">

<label class="block font-semibold">
Position
</label>

<input 
type="text"
name="position"
value="{{ old('position', $da_personnel->position) }}"
class="w-full border rounded p-2">

</div>




<div class="mb-4">

<label class="block font-semibold">
Office
</label>

<input 
type="text"
name="office"
value="{{ old('office', $da_personnel->office) }}"
class="w-full border rounded p-2">

</div>




<div class="mb-4">

<label class="block font-semibold">
Contact Number
</label>

<input 
type="text"
name="contact_number"
value="{{ old('contact_number', $da_personnel->contact_number) }}"
class="w-full border rounded p-2">

</div>




<div class="mb-4">

<label class="block font-semibold">
Province
</label>

<input 
type="text"
name="province"
value="{{ old('province', $da_personnel->province) }}"
class="w-full border rounded p-2">

</div>




<div class="flex gap-3">

<button 
type="submit"
class="bg-green-600 text-white px-5 py-2 rounded">

Update

</button>



<a href="{{ route('da-personnels.index') }}"
class="bg-gray-500 text-white px-5 py-2 rounded">

Cancel

</a>


</div>


</form>


</div>

</div>

</div>


</x-app-layout>