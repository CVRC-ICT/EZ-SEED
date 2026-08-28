<!DOCTYPE html>
<html>
<head>
    <title>EZ-Seed Farmer Portal</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f5f7f2;
            margin:0;
            padding:40px;
        }

        .container{
            max-width:800px;
            margin:auto;
            background:white;
            padding:30px;
            border-radius:10px;
            box-shadow:0 2px 10px rgba(0,0,0,.1);
        }

        h1{
            color:#2e7d32;
            text-align:center;
        }

        .form-group{
            margin-bottom:15px;
        }

        label{
            display:block;
            margin-bottom:5px;
            font-weight:bold;
        }

        input, select{
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:5px;
            box-sizing:border-box;
        }

        button{
            background:#2e7d32;
            color:white;
            border:none;
            padding:12px 25px;
            border-radius:5px;
            cursor:pointer;
            width:100%;
            font-size:16px;
        }

        button:hover{
            background:#1b5e20;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>EZ-Seed Farmer Profile</h1>

    @if(session('success'))
        <div style="background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin-bottom:20px;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('farmer.store') }}" method="POST">

        @csrf

        <div class="form-group">
            <label>RSBSA Number</label>
            <input type="text" name="rsbsa_number">
        </div>

        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name">
        </div>

        <div class="form-group">
            <label>Middle Name</label>
            <input type="text" name="middle_name">
        </div>

        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name">
        </div>

        <div class="form-group">
            <label>Suffix</label>
            <input type="text" name="suffix">
        </div>

        <div class="form-group">
            <label>Birth Date</label>
            <input type="date" name="birth_date">
        </div>

        <div class="form-group">
            <label>Sex</label>
            <select name="sex">
                <option value="">Select</option>
                <option>Male</option>
                <option>Female</option>
            </select>
        </div>

        <div class="form-group">
            <label>Civil Status</label>
            <select name="civil_status">
                <option value="">Select</option>
                <option>Single</option>
                <option>Married</option>
                <option>Widowed</option>
                <option>Separated</option>
            </select>
        </div>

        <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number">
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email">
        </div>

<div class="form-group">
    <label>Province</label>

    <select name="province_id" id="province" required>

        <option value="">-- Select Province --</option>

        @foreach($provinces as $province)

            <option value="{{ $province->id }}">
                {{ $province->name }}
            </option>

        @endforeach

    </select>
</div>


<div class="form-group">

    <label>Municipality</label>

    <select name="municipality_id" id="municipality" required>

        <option value="">-- Select Municipality --</option>

    </select>

</div>



<div class="form-group">

    <label>Barangay</label>

    <select name="barangay_id" id="barangay" required>

        <option value="">-- Select Barangay --</option>

    </select>

</div>

        <div class="form-group">
            <label>Farm Area (ha)</label>
            <input type="number" step="0.01" name="farm_area">
        </div>

        <div class="form-group">
            <label>Tenurial Status</label>
            <input type="text" name="tenurial_status">
        </div>

        <button type="submit">
            Save Farmer
        </button>

    </form>

</div>

<script>

document.getElementById('province').addEventListener('change', function () {

    let provinceId = this.value;

    let municipality =
        document.getElementById('municipality');

    let barangay =
        document.getElementById('barangay');


    municipality.innerHTML =
        '<option value="">Loading...</option>';

    barangay.innerHTML =
        '<option value="">-- Select Barangay --</option>';



    fetch('/municipalities/' + provinceId)

        .then(response => response.json())

        .then(data => {


            municipality.innerHTML =
                '<option value="">-- Select Municipality --</option>';


            data.forEach(function(item){

                municipality.innerHTML +=
                `
                <option value="${item.id}">
                    ${item.name}
                </option>
                `;

            });


        })

        .catch(error => {

            console.error(error);

            municipality.innerHTML =
            '<option value="">Unable to load municipalities</option>';

        });


});





document.getElementById('municipality').addEventListener('change', function () {


    let municipalityId = this.value;


    let barangay =
        document.getElementById('barangay');



    barangay.innerHTML =
        '<option value="">Loading...</option>';



    fetch('/barangays/' + municipalityId)


        .then(response => response.json())


        .then(data => {


            barangay.innerHTML =
            '<option value="">-- Select Barangay --</option>';



            data.forEach(function(item){


                barangay.innerHTML +=

                `
                <option value="${item.id}">
                    ${item.name}
                </option>
                `;


            });



        })


        .catch(error => {


            console.error(error);


            barangay.innerHTML =
            '<option value="">Unable to load barangays</option>';


        });


});


</script>

</body>
</html>