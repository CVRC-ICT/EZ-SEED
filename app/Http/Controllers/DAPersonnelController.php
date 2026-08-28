<?php

namespace App\Http\Controllers;

use App\Models\DAPersonnel;
use Illuminate\Http\Request;

class DAPersonnelController extends Controller
{

    public function index()
    {
        $personnels = DAPersonnel::latest()->get();

        return view('da_personnels.index', compact('personnels'));
    }

    public function create()
    {
        return view('da_personnels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'office' => 'required',
            'contact_number' => 'nullable',
            'province' => 'nullable'
        ]);


        DAPersonnel::create([
            'name' => $request->name,
            'position' => $request->position,
            'office' => $request->office,
            'contact_number' => $request->contact_number,
            'province' => $request->province,
        ]);


        return redirect()
            ->route('da-personnels.index')
            ->with('success','DA Personnel added successfully.');
    }


    public function show(DAPersonnel $da_personnel)
    {
        return view('da_personnels.show', compact('da_personnel'));
    }

    public function edit(DAPersonnel $da_personnel)
    {
        return view('da_personnels.edit', compact('da_personnel'));
    }

    public function update(Request $request, DAPersonnel $da_personnel)
    {

        $request->validate([
            'name' => 'required',
            'position' => 'required',
            'office' => 'required',
            'contact_number' => 'nullable',
            'province' => 'nullable'
        ]);


        $da_personnel->update([
            'name' => $request->name,
            'position' => $request->position,
            'office' => $request->office,
            'contact_number' => $request->contact_number,
            'province' => $request->province,
        ]);


        return redirect()
            ->route('da-personnels.index')
            ->with('success','DA Personnel updated successfully.');
    }

    public function destroy(DAPersonnel $da_personnel)
    {
        $da_personnel->delete();


        return redirect()
            ->route('da-personnels.index')
            ->with('success','DA Personnel deleted successfully.');
    }

}