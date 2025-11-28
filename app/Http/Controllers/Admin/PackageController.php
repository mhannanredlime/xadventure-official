<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegularPackageRequest;
use App\Models\Package;
use App\Models\PackageType;
use App\Models\VehicleType;
use App\Services\ImageService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PackageController extends Controller
{
    public function index()
    {
        $data['items'] = Package::with(['packagePrices', 'vehicleTypes.images', 'images'])->orderBy('name')->paginate(10);
        $data['page_title'] = 'Packages';
        $data['page_desc'] = null;
        $data['packageTypes'] = PackageType::whereNull('parent_id')->active()->get();

        return view('admin.add-packege-management', $data);
    }

    public function create()
    {
        $packages = Package::with(['variants.prices', 'vehicleTypes.images', 'images'])->orderBy('name')->get();
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();

        return view('admin.add-packege-management', compact('packages', 'vehicleTypes'));
    }

    public function createAtvUtv()
    {
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        $package = null;

        return view('admin.atvutv-packege-management', compact('vehicleTypes', 'package'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'type' => 'required|in:regular,atv,utv',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1|gte:min_participants',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'vehicle_type_ids' => 'array',
            'vehicle_type_ids.*' => 'exists:vehicle_types,id',
        ]);

        $vehicleTypeIds = $validated['vehicle_type_ids'] ?? [];
        unset($validated['vehicle_type_ids']);

        $package = Package::create($validated);

        // Handle multiple image uploads with extended format support
        if ($request->hasFile('images')) {
            // Validate image formats
            $request->validate([
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120', // 5MB max, support WebP and more formats
            ]);

            $imageService = new ImageService;
            $imageService->uploadMultipleImages($package, $request->file('images'), 'packages');
        }

        if (! empty($vehicleTypeIds)) {
            $package->vehicleTypes()->attach($vehicleTypeIds);
        }

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package created successfully.');
    }

    public function createRegular()
    {
        $data['package'] = null;
        $data['page_title'] = "Add Regular Package";
        $data['packageTypes'] = PackageType::whereNotNull('parent_id')->active()->get();
        $data['days'] = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        return view('admin.regular-packege-management', $data);
    }

    public function storeRegular(StoreRegularPackageRequest $request)
    {
        $validated = $request->validated();
        $activeDays = json_decode($request->input('active_days', '[]'), true) ?: [];
        $dayPrices = json_decode($request->input('day_prices', '[]'), true) ?: [];

        DB::beginTransaction();
        try {
            // ---------- Create Package ----------
            $package = Package::create([
                'name' => $validated['packageName'],
                'subtitle' => $validated['subTitle'] ?? null,
                'package_type_id' => $validated['packageType'],
                'details' => $validated['details'] ?? null,
                'display_starting_price' => $validated['displayStartingPrice'] ?? null,
                'min_participants' => $validated['minParticipant'],
                'max_participants' => $validated['maxParticipant'],
                'is_active' => true,
            ]);

            // ---------- Upload Images ----------
            if ($request->hasFile('images')) {
                $imageService = new ImageService;
                $imageService->uploadMultipleImages($package, $request->file('images'), 'packages');
            }

            if (! empty($activeDays) && ! empty($dayPrices)) {
                $package->syncPackagePrices($activeDays, $dayPrices);
            }
            DB::commit();
            ToastMagic::success('Package created successfully!');

            return redirect()->route('admin.packege.list');

        } catch (Throwable $e) {
            DB::rollBack();
            ToastMagic::error('Something went wrong while creating the package.');

            return back()->withInput();
        }
    }

    public function storeAtvUtv(Request $request)
    {
        // Get available vehicle types for validation
        $vehicleTypes = VehicleType::where('is_active', true)->pluck('name')->toArray();
        $vehicleTypeValidation = 'required|in:'.implode(',', $vehicleTypes);

        $validated = $request->validate([
            'vehicleType' => $vehicleTypeValidation,
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'notes' => 'nullable|string',

            'weekdaySingle' => 'required|numeric|min:0',
            'weekdayDouble' => 'required|numeric|min:0',
            'weekendSingle' => 'required|numeric|min:0',
            'weekendDouble' => 'required|numeric|min:0',
            'selected_weekday' => 'nullable|string|in:sunday,monday,tuesday,wednesday,thursday',
            'selected_weekend' => 'nullable|string|in:friday,saturday',
        ], [
            'vehicleType.required' => 'Vehicle type is required.',
            'packageName.required' => 'Package name is required.',
            'weekdaySingle.required' => 'Weekday single rider price is required.',
            'weekdayDouble.required' => 'Weekday double rider price is required.',
            'weekendSingle.required' => 'Weekend single rider price is required.',
            'weekendDouble.required' => 'Weekend double rider price is required.',
            'weekdaySingle.numeric' => 'Weekday single rider price must be a valid number.',
            'weekdayDouble.numeric' => 'Weekday double rider price must be a valid number.',
            'weekendSingle.numeric' => 'Weekend single rider price must be a valid number.',
            'weekendDouble.numeric' => 'Weekend double rider price must be a valid number.',
            'weekdaySingle.min' => 'Weekday single rider price must be greater than or equal to 0.',
            'weekdayDouble.min' => 'Weekday double rider price must be greater than or equal to 0.',
            'weekendSingle.min' => 'Weekend single rider price must be greater than or equal to 0.',
            'weekendDouble.min' => 'Weekend double rider price must be greater than or equal to 0.',
            'selected_weekday.in' => 'The weekday selection is invalid.',
            'selected_weekend.in' => 'The weekend selection is invalid.',
        ]);

        // Determine package type - map vehicle type to package type
        $packageType = strtolower($validated['vehicleType']);
        // Map vehicle types to package types - you can customize this mapping as needed
        $vehicleTypeMapping = [
            'dirt bike' => 'atv',
            'atv' => 'atv',
            'utv' => 'utv',
            // Add more mappings as needed
        ];

        $packageType = $vehicleTypeMapping[$packageType] ?? $packageType;

        // Get the vehicle type and associate it with the package
        $vehicleType = VehicleType::where('name', $validated['vehicleType'])->first();
        if (! $vehicleType) {
            return back()->withErrors(['vehicleType' => 'Selected vehicle type not found.']);
        }

        // Create package
        $package = Package::create([
            'name' => $validated['packageName'],
            'subtitle' => $validated['subTitle'],
            'type' => $packageType,
            'notes' => $validated['notes'],

            'selected_weekday' => $validated['selected_weekday'] ?? 'monday',
            'selected_weekend' => $validated['selected_weekend'] ?? 'friday',
            'min_participants' => 1,
            'max_participants' => 4,
            'is_active' => true,
        ]);

        // Associate vehicle type with package (this will automatically use vehicle type images)
        $package->vehicleTypes()->attach([$vehicleType->id]);

        // Create variants
        $variants = [
            ['variant_name' => 'Single Rider', 'capacity' => 1],
            ['variant_name' => 'Double Rider', 'capacity' => 2],
        ];

        foreach ($variants as $variantData) {
            $variant = $package->variants()->create([
                'variant_name' => $variantData['variant_name'],
                'capacity' => $variantData['capacity'],
                'is_active' => true,
            ]);

            // Create prices for this variant
            $weekdayPrice = $variantData['variant_name'] === 'Single Rider' ? $validated['weekdaySingle'] : $validated['weekdayDouble'];
            $weekendPrice = $variantData['variant_name'] === 'Single Rider' ? $validated['weekendSingle'] : $validated['weekendDouble'];

            $variant->prices()->createMany([
                ['price_type' => 'weekday', 'amount' => $weekdayPrice],
                ['price_type' => 'weekend', 'amount' => $weekendPrice],
            ]);
        }

        return redirect()->route('admin.add-packege-management')
            ->with('success', 'ATV/UTV package created successfully.');
    }

    public function edit(Package $package)
    {
        $packages = Package::with(['variants.prices', 'vehicleTypes', 'images'])->orderBy('name')->get();
        $vehicleTypes = VehicleType::where('is_active', true)->orderBy('name')->get();
        $package->load('vehicleTypes');

        return view('admin.add-packege-management', compact('packages', 'package', 'vehicleTypes'));
    }

    public function show(Package $package)
    {
        $package->load([
            'vehicleTypes.images',
            'images',
            'packagePrices', // ADD THIS
        ]);

        return view('admin.package-show', compact('package'));
    }

    public function editRegular(Package $package)
    {
        $package->load(['variants.prices', 'images']);
        return view('admin.regular-packege-management', compact('package'));
    }

    public function editAtvUtv(Package $package)
    {
        $vehicleTypes = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        $package->load(['variants.prices', 'images']);

        return view('admin.atvutv-packege-management', compact('package', 'vehicleTypes'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'type' => 'required|in:regular,atv,utv',
            'min_participants' => 'required|integer|min:1',
            'max_participants' => 'required|integer|min:1|gte:min_participants',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'vehicle_type_ids' => 'array',
            'vehicle_type_ids.*' => 'exists:vehicle_types,id',
        ]);

        if ($request->hasFile('image')) {
            if ($package->image_path) {
                Storage::disk('public_storage')->delete($package->image_path);
            }

            $path = $request->file('image')->store('packages', 'public_storage');
            $validated['image_path'] = $path;
        }

        $vehicleTypeIds = $validated['vehicle_type_ids'] ?? [];
        unset($validated['vehicle_type_ids']);

        $package->update($validated);

        $package->vehicleTypes()->sync($vehicleTypeIds);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package updated successfully.');
    }

    public function updateRegular(Request $request, Package $package)
    {
        // Debug: Log the incoming request data
        Log::info('updateRegular request data:', $request->all());

        $validated = $request->validate([
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'packageType' => 'required|in:Single,Bundle,Group',
            'details' => 'nullable|string',
            'displayStartingPrice' => 'nullable|numeric|min:0',
            'minParticipant' => 'required|integer|min:1',
            'maxParticipant' => 'required|integer|min:1|gte:minParticipant',
            'weekdayPrice' => 'required|numeric|min:0',
            'weekendPrice' => 'required|numeric|min:0',
            'selected_weekday' => 'nullable|string|in:sunday,monday,tuesday,wednesday,thursday',
            'selected_weekend' => 'nullable|string|in:friday,saturday',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120',
        ]);

        // Debug: Log the validated data
        Log::info('updateRegular validated data:', $validated);

        // Handle multiple image uploads with extended format support
        if ($request->hasFile('images')) {
            // Validate image formats
            $request->validate([
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg|max:5120', // 5MB max, support WebP and more formats
            ]);

            Log::info('Images received in updateRegular:', [
                'count' => count($request->file('images')),
                'files' => array_map(function ($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $this->getMimeTypeWithFallback($file),
                    ];
                }, $request->file('images')),
            ]);

            $imageService = new ImageService;
            $uploadedImages = $imageService->uploadMultipleImages($package, $request->file('images'), 'packages');

            Log::info('Images uploaded successfully:', [
                'count' => count($uploadedImages),
                'image_ids' => array_map(function ($image) {
                    return $image->id;
                }, $uploadedImages),
            ]);
        } else {
            Log::info('No images received in updateRegular');
        }

        // Update package
        $package->update([
            'name' => $validated['packageName'],
            'subtitle' => $validated['subTitle'],
            'details' => $validated['details'],
            'display_starting_price' => $validated['displayStartingPrice'] ?? null,
            'min_participants' => $validated['minParticipant'],
            'max_participants' => $validated['maxParticipant'],
            'selected_weekday' => $validated['selected_weekday'] ?? 'monday',
            'selected_weekend' => $validated['selected_weekend'] ?? 'friday',
            'image_path' => $validated['image_path'] ?? $package->image_path,
        ]);

        // Update or create variant
        $variantName = $validated['packageType'];
        $capacity = $validated['packageType'] === 'Single' ? 1 : ($validated['packageType'] === 'Bundle' ? 2 : 4);

        $variant = $package->variants()->first();
        if ($variant) {
            $variant->update([
                'variant_name' => $variantName,
                'capacity' => $capacity,
            ]);
        } else {
            $variant = $package->variants()->create([
                'variant_name' => $variantName,
                'capacity' => $capacity,
                'is_active' => true,
            ]);
        }

        // Update prices
        $variant->prices()->delete();
        $variant->prices()->createMany([
            ['price_type' => 'weekday', 'amount' => $validated['weekdayPrice']],
            ['price_type' => 'weekend', 'amount' => $validated['weekendPrice']],
        ]);

        return redirect()->route('admin.regular-packege-management.edit', $package)
            ->with('success', 'Regular package updated successfully.');
    }

    public function updateAtvUtv(Request $request, Package $package)
    {
        // Get available vehicle types for validation
        $vehicleTypes = VehicleType::where('is_active', true)->pluck('name')->toArray();
        $vehicleTypeValidation = 'required|in:'.implode(',', $vehicleTypes);

        $validated = $request->validate([
            'vehicleType' => $vehicleTypeValidation,
            'packageName' => 'required|string|max:255',
            'subTitle' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'weekdaySingle' => 'required|numeric|min:0',
            'weekdayDouble' => 'required|numeric|min:0',
            'weekendSingle' => 'required|numeric|min:0',
            'weekendDouble' => 'required|numeric|min:0',
            'selected_weekday' => 'nullable|string|in:sunday,monday,tuesday,wednesday,thursday',
            'selected_weekend' => 'nullable|string|in:friday,saturday',
        ], [
            'vehicleType.required' => 'Vehicle type is required.',
            'packageName.required' => 'Package name is required.',
            'weekdaySingle.required' => 'Weekday single rider price is required.',
            'weekdayDouble.required' => 'Weekday double rider price is required.',
            'weekendSingle.required' => 'Weekend single rider price is required.',
            'weekendDouble.required' => 'Weekend double rider price is required.',
            'weekdaySingle.numeric' => 'Weekday single rider price must be a valid number.',
            'weekdayDouble.numeric' => 'Weekday double rider price must be a valid number.',
            'weekendSingle.numeric' => 'Weekend single rider price must be a valid number.',
            'weekendDouble.numeric' => 'Weekend double rider price must be a valid number.',
            'weekdaySingle.min' => 'Weekday single rider price must be greater than or equal to 0.',
            'weekdayDouble.min' => 'Weekday double rider price must be greater than or equal to 0.',
            'weekendSingle.min' => 'Weekend single rider price must be greater than or equal to 0.',
            'weekendDouble.min' => 'Weekend double rider price must be greater than or equal to 0.',
            'selected_weekday.in' => 'The weekday selection is invalid.',
            'selected_weekend.in' => 'The weekend selection is invalid.',
        ]);

        // Determine package type - map vehicle type to package type
        $packageType = strtolower($validated['vehicleType']);
        // Map vehicle types to package types - you can customize this mapping as needed
        $vehicleTypeMapping = [
            'dirt bike' => 'atv',
            'atv' => 'atv',
            'utv' => 'utv',
            // Add more mappings as needed
        ];

        $packageType = $vehicleTypeMapping[$packageType] ?? $packageType;

        // Get the vehicle type and associate it with the package
        $vehicleType = VehicleType::where('name', $validated['vehicleType'])->first();
        if (! $vehicleType) {
            return back()->withErrors(['vehicleType' => 'Selected vehicle type not found.']);
        }

        // Update package
        $package->update([
            'name' => $validated['packageName'],
            'subtitle' => $validated['subTitle'],
            'type' => $packageType,
            'notes' => $validated['notes'],

            'selected_weekday' => $validated['selected_weekday'] ?? 'monday',
            'selected_weekend' => $validated['selected_weekend'] ?? 'friday',
        ]);

        // Sync vehicle type relationship (this will automatically use vehicle type images)
        $package->vehicleTypes()->sync([$vehicleType->id]);

        // Update variants and prices
        $variants = $package->variants;
        $variantData = [
            ['variant_name' => 'Single Rider', 'capacity' => 1],
            ['variant_name' => 'Double Rider', 'capacity' => 2],
        ];

        foreach ($variantData as $index => $data) {
            $variant = $variants->get($index);
            if ($variant) {
                $variant->update([
                    'variant_name' => $data['variant_name'],
                    'capacity' => $data['capacity'],
                ]);
            } else {
                $variant = $package->variants()->create([
                    'variant_name' => $data['variant_name'],
                    'capacity' => $data['capacity'],
                    'is_active' => true,
                ]);
            }

            // Update prices for this variant
            $weekdayPrice = $data['variant_name'] === 'Single Rider' ? $validated['weekdaySingle'] : $validated['weekdayDouble'];
            $weekendPrice = $data['variant_name'] === 'Single Rider' ? $validated['weekendSingle'] : $validated['weekendDouble'];

            $variant->prices()->delete();
            $variant->prices()->createMany([
                ['price_type' => 'weekday', 'amount' => $weekdayPrice],
                ['price_type' => 'weekend', 'amount' => $weekendPrice],
            ]);
        }

        return redirect()->route('admin.atvutv-packege-management.edit', $package)
            ->with('success', 'ATV/UTV package updated successfully.');
    }

    public function destroy(Package $package)
    {
        if ($package->image_path) {
            Storage::disk('public_storage')->delete($package->image_path);
        }
        $package->packagePrices()->delete();
        $package->variants()->delete();
        $package->delete();
        ToastMagic::success('Package deleted successfully!');
        return redirect()->route('admin.packages.index');
    }

    /**
     * Get MIME type with fallback for missing fileinfo extension
     */
    private function getMimeTypeWithFallback(UploadedFile $file): string
    {
        try {
            // Try to get MIME type using Laravel's method (requires fileinfo extension)
            return $file->getMimeType();
        } catch (\Exception $e) {
            // Fallback: determine MIME type from file extension
            $extension = strtolower($file->getClientOriginalExtension());

            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'bmp' => 'image/bmp',
                'svg' => 'image/svg+xml',
                'ico' => 'image/x-icon',
            ];

            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
    }
}
