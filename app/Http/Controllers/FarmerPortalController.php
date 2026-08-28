<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farmer;
use App\Models\Province;

class FarmerPortalController extends Controller
{

    public function index()
    {
        $provinces = Province::orderBy('name')->get();

        return view('farmer.register', compact('provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rsbsa_number'      => 'nullable|string|max:50',

            'first_name'        => 'required|string|max:100',
            'middle_name'       => 'nullable|string|max:100',
            'last_name'         => 'required|string|max:100',
            'suffix'            => 'nullable|string|max:20',

            'birth_date'        => 'required|date',

            'sex'               => 'required|in:Male,Female',

            'civil_status'      => 'required|string|max:50',

            'contact_number'    => 'nullable|string|max:20',

            'email'             => 'nullable|email|max:255',

            'province_id'       => 'required|exists:provinces,id',
            'municipality_id'   => 'required|exists:municipalities,id',
            'barangay_id'       => 'required|exists:barangays,id',

            'farm_area'         => 'nullable|numeric|min:0',

            'tenurial_status'   => 'nullable|string|max:100',
        ]);


        $farmer = Farmer::create($validated);


        return redirect()->route('surveys.step.show', [
            'farmer' => $farmer->id,
            'step'   => 1,
        ]);
    }
}