<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl">
DA Personnel Profile
</h2>
</x-slot>


<div class="py-6">

<div class="max-w-3xl mx-auto bg-white p-6 shadow rounded">


<p><strong>Name:</strong>
{{ $da_personnel->name }}</p>


<p><strong>Position:</strong>
{{ $da_personnel->position }}</p>


<p><strong>Office:</strong>
{{ $da_personnel->office }}</p>


<p><strong>Contact:</strong>
{{ $da_personnel->contact_number }}</p>


<p><strong>Province:</strong>
{{ $da_personnel->province }}</p>


</div>

</div>

</x-app-layout>