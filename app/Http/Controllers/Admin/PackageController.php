<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegularPackageStoreUpdateRequest;
use App\Models\Package;
use App\Models\PackagePrice;
use App\Models\PackageType;
use App\Models\VehicleType;
use App\Services\ImageService;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $data['items'] = Package::with(['packagePrices', 'vehicleTypes.images', 'images'])
            ->when($request->has('package_type_id'), function ($query) use ($request) {
                $query->where('package_type_id', $request->package_type_id);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('subtitle', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);
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
        $data['page_title'] = 'Add Regular Package';
        $data['packageTypes'] = PackageType::whereNotNull('parent_id')->active()->get();
        $data['days'] = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        return view('admin.regular-packege-management', $data);
    }

    public function storeRegular(RegularPackageStoreUpdateRequest $request)
    {
        $validated = $request->validated();

        $activeDays = $validated['active_days'] ?? [];
        $dayPrices = $validated['day_prices'] ?? [];

        // Validate array lengths match
        if (count($activeDays) !== count($dayPrices)) {
            ToastMagic::error('Active days and day prices count mismatch.');

            return back()->withInput();
        }

        DB::beginTransaction();
        try {
            // Create Package
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

            // Upload Images
            if ($request->hasFile('images')) {
                $imageService = new ImageService;
                $imageService->uploadMultipleImages($package, $request->file('images'), 'packages');
            }

            // Create Prices
            if (! empty($activeDays) && ! empty($dayPrices)) {
                $this->createPackagePrices($package, $activeDays, $dayPrices);
            }

            DB::commit();
            ToastMagic::success('Package created successfully!');

            return redirect()->route('admin.packege.list');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Package creation failed: '.$e->getMessage());
            ToastMagic::error('Something went wrong while creating the package.');

            return back()->withInput();
        }
    }

    public function updateRegular(RegularPackageStoreUpdateRequest $request, Package $package)
    {
        $validated = $request->validated();

        $activeDays = $validated['active_days'] ?? [];
        $dayPrices = $validated['day_prices'] ?? [];

        // Validate array lengths match
        if (count($activeDays) !== count($dayPrices)) {
            ToastMagic::error('Active days and day prices count mismatch.');

            return back()->withInput();
        }
        // dd($activeDays, $dayPrices, $request->all());
        DB::beginTransaction();
        try {
            // Update Package
            $package->update([
                'name' => $validated['packageName'],
                'subtitle' => $validated['subTitle'] ?? null,
                'package_type_id' => $validated['packageType'],
                'details' => $validated['details'] ?? null,
                'display_starting_price' => $validated['displayStartingPrice'] ?? null,
                'min_participants' => $validated['minParticipant'],
                'max_participants' => $validated['maxParticipant'],
                'is_active' => $validated['is_active'] ?? $package->is_active,
            ]);

            // Handle Images - Upload new ones
            if ($request->hasFile('images')) {
                $imageService = new ImageService;
                $imageService->uploadMultipleImages($package, $request->file('images'), 'packages');
            }

            // Handle deletion of images if requested
            if ($request->has('delete_images')) {
                $imageService = new ImageService;
                $imageService->deleteSpecificImages($package, $request->delete_images);
            }

            // Sync Prices
            $this->syncPackagePrices($package, $activeDays, $dayPrices);

            DB::commit();
            ToastMagic::success('Regular package updated successfully!');

            return redirect()->route('admin.packege.list');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Package update failed: '.$e->getMessage());
            ToastMagic::error('Something went wrong while updating the package.');

            return back()->withInput();
        }
    }

    /**
     * Create package prices - for store operation
     */
    private function createPackagePrices(Package $package, array $activeDays, array $dayPrices): void
    {
        $pricesToCreate = $this->preparePriceData($package->id, $activeDays, $dayPrices);

        if (! empty($pricesToCreate)) {
            PackagePrice::insert($pricesToCreate);
        }
    }

    private function syncPackagePrices(Package $package, array $activeDays, array $dayPrices): void
    {
        $pricesToCreate = $this->preparePriceData($package->id, $activeDays, $dayPrices);
        PackagePrice::where('package_id', $package->id)
            ->whereNotIn('day', $activeDays)
            ->delete();
        foreach ($pricesToCreate as $priceData) {
            PackagePrice::updateOrCreate(
                [
                    'package_id' => $package->id,
                    'day' => $priceData['day'],
                ],
                [
                    'type' => $priceData['type'],
                    'price' => $priceData['price'],
                    'is_active' => $priceData['is_active'],
                ]
            );
        }
    }

    private function preparePriceData(int $packageId, array $activeDays, array $dayPrices): array
    {
        $pricesToCreate = [];

        foreach ($activeDays as $key => $day) {
            $day = strtolower($day);

            $price = $dayPrices[$key] ?? $dayPrices[$day] ?? 0;

            $pricesToCreate[] = [
                'package_id' => $packageId,
                'type' => $this->getDayType($day),
                'day' => $day,
                'price' => $price,
                'is_active' => $price > 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $pricesToCreate;
    }

    /**
     * Determine day type (weekend/weekday)
     */
    private function getDayType(string $day): string
    {
        $weekendDays = ['fri', 'sat', 'sun']; // Consider making this configurable

        return in_array($day, $weekendDays) ? 'weekend' : 'weekday';
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
        $data['page_title'] = 'Packages Update';
        $data['page_desc'] = 'Packages Update';

        // Basic data
        $data['package'] = $package->load(['packagePrices', 'vehicleTypes', 'images']);
        $data['items'] = Package::with(['packagePrices', 'vehicleTypes', 'images'])
            ->orderBy('name')
            ->get();

        // Dropdown items
        $data['vehicleTypes'] = VehicleType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $data['packageTypes'] = PackageType::whereNotNull('parent_id')
            ->active()
            ->get();

        $data['days'] = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

        // Selected days from existing prices
        $selectedDays = $package->packagePrices->pluck('day')->toArray();
        $data['selectedDays'] = $selectedDays;

        // Day-wise price mapping - ensure all prices are properly formatted
        $prices = [];
        foreach ($package->packagePrices as $price) {
            $prices[$price->day] = (float) $price->price;
        }

        // Initialize all days with null if not set
        foreach ($data['days'] as $day) {
            if (! isset($prices[$day])) {
                $prices[$day] = null;
            }
        }

        $data['dayPrices'] = $prices;

        return view('admin.package.edit', $data);
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
