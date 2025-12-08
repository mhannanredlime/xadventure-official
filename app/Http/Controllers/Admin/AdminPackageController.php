<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Http\Requests\StorePackageRequest;
use App\Models\VehicleType;
use Illuminate\Http\Request;

class AdminPackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('vehicleTypes')->get();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        return view('admin.packages.create', compact('vehicleTypes'));
    }

    public function store(StorePackageRequest $request)
    {
        $package = Package::create($request->validated());
        
        if ($request->has('vehicle_type_ids')) {
            $package->vehicleTypes()->sync($request->vehicle_type_ids);
        }

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        $vehicleTypes = VehicleType::where('is_active', true)->get();
        return view('admin.packages.edit', compact('package', 'vehicleTypes'));
    }

    public function update(StorePackageRequest $request, Package $package)
    {
        $package->update($request->validated());
        
        if ($request->has('vehicle_type_ids')) {
            $package->vehicleTypes()->sync($request->vehicle_type_ids);
        }

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully.');
    }
}
