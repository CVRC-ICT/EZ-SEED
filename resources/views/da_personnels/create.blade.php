<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl">
Add DA Personnel
</h2>
</x-slot>


<div class="py-6">

<div class="max-w-3xl mx-auto bg-white p-6 shadow rounded">


<form method="POST"
action="{{ route('da-personnels.store') }}">

@csrf


<div class="mb-3">
<label>Name</label>

<input type="text"
name="name"
class="border w-full p-2 rounded">
</div>



<div class="mb-3">

<label>Position</label>

<input type="text"
name="position"
class="border w-full p-2 rounded">

</div>



<div class="mb-3">

<label>Office</label>

<input type="text"
name="office"
class="border w-full p-2 rounded">

</div>



<div class="mb-3">

<label>Contact Number</label>

<input type="text"
name="contact_number"
class="border w-full p-2 rounded">

</div>



<div class="mb-3">

<label>Province</label>

<input type="text"
name="province"
class="border w-full p-2 rounded">

</div>



<button class="bg-green-600 text-white px-4 py-2 rounded">
Save
</button>


</form>


</div>

</div>

</x-app-layout>