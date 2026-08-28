<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Province;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Support\PlaceName;
use Illuminate\Http\Request;

class FarmerController extends Controller
{

    public function index(Request $request)
    {
        $search = $request->search;

        $farmers = Farmer::with([
                'province',
                'municipality',
                'barangay'
            ])
            ->when($search, function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('rsbsa_number', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->paginate(10);

        return view('farmers.index', compact('farmers'));
    }


    public function create()
    {
        $provinces = Province::orderBy('name')->get();

        // Empty initially.
        // Loaded through AJAX after selecting province/municipality.
        $municipalities = collect();
        $barangays = collect();

        return view('farmers.create', compact(
            'provinces',
            'municipalities',
            'barangays'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([

            'rsbsa_number'    => 'nullable|string|max:50',

            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'suffix'          => 'nullable|string|max:20',

            'birth_date'      => 'nullable|date',

            'sex'             => 'required|in:Male,Female',

            'civil_status'    => 'nullable|string|max:50',

            'contact_number'  => 'nullable|string|max:20',

            'email'           => 'nullable|email|max:255',


            'province_id'     => 'required|exists:provinces,id',

            'municipality_id' => 'required|exists:municipalities,id',

            'barangay_id'     => 'required|exists:barangays,id',


            'farm_area'       => 'nullable|numeric|min:0',

            'tenurial_status' => 'nullable|string|max:100',

        ]);


        Farmer::create($validated);


        return redirect()
            ->route('farmers.index')
            ->with('success', 'Farmer added successfully.');
    }

    public function show(Farmer $farmer)
    {
        $farmer->load([
            'province',
            'municipality',
            'barangay'
        ]);

        return view('farmers.show', compact('farmer'));
    }

    public function edit(Farmer $farmer)
    {
        $provinces = Province::orderBy('name')->get();


        $municipalities = Municipality::where(
            'province_id',
            $farmer->province_id
        )
        ->orderBy('name')
        ->get();


        $barangays = Barangay::where(
            'municipality_id',
            $farmer->municipality_id
        )
        ->orderBy('name')
        ->get();



        return view('farmers.edit', compact(
            'farmer',
            'provinces',
            'municipalities',
            'barangays'
        ));
    }


    public function update(Request $request, Farmer $farmer)
    {

        $validated = $request->validate([

            'rsbsa_number'    => 'nullable|string|max:50',

            'first_name'      => 'required|string|max:255',
            'middle_name'     => 'nullable|string|max:255',
            'last_name'       => 'required|string|max:255',
            'suffix'          => 'nullable|string|max:20',

            'birth_date'      => 'nullable|date',

            'sex'             => 'required|in:Male,Female',

            'civil_status'    => 'nullable|string|max:50',

            'contact_number'  => 'nullable|string|max:20',

            'email'           => 'nullable|email|max:255',


            'province_id'     => 'required|exists:provinces,id',

            'municipality_id' => 'required|exists:municipalities,id',

            'barangay_id'     => 'required|exists:barangays,id',


            'farm_area'       => 'nullable|numeric|min:0',

            'tenurial_status' => 'nullable|string|max:100',

        ]);



        $farmer->update($validated);



        return redirect()
            ->route('farmers.index')
            ->with('success', 'Farmer updated successfully.');

    }


    public function destroy(Farmer $farmer)
    {

        $farmer->delete();


        return redirect()
            ->route('farmers.index')
            ->with('success', 'Farmer deleted successfully.');

    }


    public function getMunicipalities($province_id)
    {

        $municipalities = Municipality::where(
            'province_id',
            $province_id
        )
        ->orderBy('name')
        ->get(['id', 'name'])
        ->map(fn ($municipality) => [
            'id' => $municipality->id,
            'name' => PlaceName::clean($municipality->name),
        ]);



        return response()->json($municipalities);

    }

    public function getBarangays($municipality_id)
    {
        $barangays = Barangay::where('municipality_id', $municipality_id)
            ->select('id', 'name')
            ->groupBy('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($barangay) => [
                'id' => $barangay->id,
                'name' => PlaceName::clean($barangay->name),
            ]);

        return response()->json($barangays);
    }

}