<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Farmer
    </h2>
</x-slot>


<div class="py-4">

<div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow rounded-lg p-5">



@if ($errors->any())

<div class="mb-3 bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded">

<ul class="list-disc ml-5">

@foreach($errors->all() as $error)

<li>{{ $error }}</li>

@endforeach

</ul>

</div>

@endif





<form action="{{ route('farmers.update',$farmer->id) }}" method="POST">

@csrf

@method('PUT')





<h3 class="text-lg font-bold text-green-700 mb-3">
Personal Information
</h3>




<div class="grid grid-cols-1 md:grid-cols-2 gap-3">





<input class="border rounded p-2"
name="rsbsa_number"
placeholder="RSBSA Number"
value="{{ old('rsbsa_number',$farmer->rsbsa_number) }}">






<input class="border rounded p-2"
name="first_name"
placeholder="First Name"
value="{{ old('first_name',$farmer->first_name) }}"
required>






<input class="border rounded p-2"
name="middle_name"
placeholder="Middle Name"
value="{{ old('middle_name',$farmer->middle_name) }}">






<input class="border rounded p-2"
name="last_name"
placeholder="Last Name"
value="{{ old('last_name',$farmer->last_name) }}"
required>






<input class="border rounded p-2"
name="suffix"
placeholder="Suffix"
value="{{ old('suffix',$farmer->suffix) }}">






<input type="date"
class="border rounded p-2"
name="birth_date"
value="{{ old('birth_date',$farmer->birth_date) }}">







<select name="sex"
class="border rounded p-2"
required>


<option value="">
Select Sex
</option>


<option value="Male"
{{ old('sex',$farmer->sex)=='Male'?'selected':'' }}>
Male
</option>


<option value="Female"
{{ old('sex',$farmer->sex)=='Female'?'selected':'' }}>
Female
</option>


</select>







<select name="civil_status"
class="border rounded p-2">


<option value="">
Civil Status
</option>


<option value="Single"
{{ old('civil_status',$farmer->civil_status)=='Single'?'selected':'' }}>
Single
</option>


<option value="Married"
{{ old('civil_status',$farmer->civil_status)=='Married'?'selected':'' }}>
Married
</option>


<option value="Widowed"
{{ old('civil_status',$farmer->civil_status)=='Widowed'?'selected':'' }}>
Widowed
</option>


<option value="Separated"
{{ old('civil_status',$farmer->civil_status)=='Separated'?'selected':'' }}>
Separated
</option>



</select>






<input class="border rounded p-2"
name="contact_number"
placeholder="Contact Number"
value="{{ old('contact_number',$farmer->contact_number) }}">






<input class="border rounded p-2"
name="email"
placeholder="Email"
value="{{ old('email',$farmer->email) }}">





</div>






<hr class="my-4">






<h3 class="text-lg font-bold text-green-700 mb-3">
Location
</h3>






<div class="grid grid-cols-1 md:grid-cols-3 gap-3">






<select id="province"
name="province_id"
class="border rounded p-2"
required>


<option value="">
Select Province
</option>



@foreach($provinces as $province)


<option value="{{ $province->id }}"
{{ $farmer->province_id == $province->id ? 'selected':'' }}>

{{ $province->name }}

</option>


@endforeach


</select>








<select id="municipality"
name="municipality_id"
class="border rounded p-2"
required>


<option value="">
Select Municipality
</option>



@foreach($municipalities as $municipality)


<option value="{{ $municipality->id }}"
{{ $farmer->municipality_id == $municipality->id ? 'selected':'' }}>

{{ $municipality->name }}

</option>


@endforeach



</select>








<select id="barangay"
name="barangay_id"
class="border rounded p-2"
required>



<option value="">
Select Barangay
</option>




@foreach($barangays as $barangay)


<option value="{{ $barangay->id }}"
{{ $farmer->barangay_id == $barangay->id ? 'selected':'' }}>

{{ $barangay->name }}

</option>



@endforeach




</select>





</div>








<hr class="my-4">






<h3 class="text-lg font-bold text-green-700 mb-3">
Farm Information
</h3>






<div class="grid grid-cols-1 md:grid-cols-2 gap-3">





<input type="number"
step="0.01"
name="farm_area"
class="border rounded p-2"
placeholder="Farm Area (hectares)"
value="{{ old('farm_area',$farmer->farm_area) }}">







<select name="tenurial_status"
class="border rounded p-2">


<option value="">
Tenurial Status
</option>


<option value="Owner"
{{ $farmer->tenurial_status=="Owner"?'selected':'' }}>
Owner
</option>


<option value="Tenant"
{{ $farmer->tenurial_status=="Tenant"?'selected':'' }}>
Tenant
</option>


<option value="Lessee"
{{ $farmer->tenurial_status=="Lessee"?'selected':'' }}>
Lessee
</option>


<option value="Others"
{{ $farmer->tenurial_status=="Others"?'selected':'' }}>
Others
</option>



</select>





</div>



<div class="mt-5 flex flex-wrap justify-end gap-3">



<a href="{{ route('farmers.index') }}"
class="border border-gray-300 text-gray-700 px-5 py-2 rounded hover:bg-gray-50">

Cancel

</a>






<button type="submit"
class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700">

Update Farmer

</button>





</div>





</form>



</div>

</div>

</div>






<script>


document.getElementById('province')
.addEventListener('change',function(){


let provinceId=this.value;

let municipality=document.getElementById('municipality');

let barangay=document.getElementById('barangay');



municipality.innerHTML=
'<option value="">Select Municipality</option>';

barangay.innerHTML=
'<option value="">Select Barangay</option>';



if(provinceId){


fetch('/municipalities/'+provinceId)

.then(response=>response.json())

.then(data=>{


data.forEach(item=>{


municipality.innerHTML +=

`
<option value="${item.id}">
${item.name}
</option>
`;



});


});


}


});








document.getElementById('municipality')
.addEventListener('change',function(){


let municipalityId=this.value;


let barangay=document.getElementById('barangay');



barangay.innerHTML=
'<option value="">Select Barangay</option>';



if(municipalityId){


fetch('/barangays/'+municipalityId)


.then(response=>response.json())


.then(data=>{


data.forEach(item=>{


barangay.innerHTML +=

`
<option value="${item.id}">
${item.name}
</option>
`;



});


});


}


});



</script>




</x-app-layout>