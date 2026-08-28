<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        DA Personnel Management
    </h2>
</x-slot>


<div class="py-6">

<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-6">


@if(session('success'))
<div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
    {{ session('success') }}
</div>
@endif


<div class="flex justify-between mb-4">

<h3 class="text-lg font-semibold">
    DA Personnel List
</h3>


<a href="{{ route('da-personnels.create') }}"
class="bg-green-600 text-white px-4 py-2 rounded">
    Add Personnel
</a>

</div>



<table class="w-full border">

<thead class="bg-gray-100">

<tr>
<th class="border p-2">Name</th>
<th class="border p-2">Position</th>
<th class="border p-2">Office</th>
<th class="border p-2">Contact</th>
<th class="border p-2">Action</th>
</tr>

</thead>


<tbody>

@foreach($personnels as $personnel)

<tr>

<td class="border p-2">
{{ $personnel->name }}
</td>

<td class="border p-2">
{{ $personnel->position }}
</td>

<td class="border p-2">
{{ $personnel->office }}
</td>

<td class="border p-2">
{{ $personnel->contact_number }}
</td>


<td class="border p-2">

<a href="{{ route('da-personnels.show',$personnel->id) }}"
class="text-blue-600">
View
</a>


<a href="{{ route('da-personnels.edit',$personnel->id) }}"
class="text-yellow-600 ml-2">
Edit
</a>


<form action="{{ route('da-personnels.destroy',$personnel->id) }}"
method="POST"
class="inline">

@csrf
@method('DELETE')

<button class="text-red-600 ml-2">
Delete
</button>

</form>


</td>


</tr>

@endforeach


</tbody>

</table>


</div>
</div>

</div>

</x-app-layout>