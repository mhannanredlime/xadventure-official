<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Package;
use App\Models\PriceType;
use App\Models\RiderType;
use App\Models\PackageType;
use App\Models\VehicleType;
use App\Models\PackagePrice;
use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Models\PackageWeekendDay;
use App\Services\XPackageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AtvUtvPackageRequest;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use App\Http\Requests\RegularPackageStoreUpdateRequest;

class PackageController extends Controller
{
    protected XPackageService $packageService;

    public function __construct(XPackageService $packageService)
    {
        $this->packageService = $packageService;
    }

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
            ->orderBy('id', 'desc')
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
        $data['days'] = weekDays();

        return view('admin.package.regular.regular-create', $data);
    }

    public function edit(Package $package)
    {
        $data['package'] = $package;
        $data['page_title'] = 'Edit Regular Package';
        $data['page_desc'] = 'Update Package Details';

        $data['packageTypes'] = PackageType::whereNotNull('parent_id')->active()->get();
        $data['days'] = weekDays();

        // Build array of existing prices
        $existingPrices = [];
        foreach ($package->packagePrices as $price) {
            $existingPrices[$price->day] = $price->price;
        }

        // Ensure all days have at least null values
        foreach ($data['days'] as $day) {
            if (! isset($existingPrices[$day])) {
                $existingPrices[$day] = null;
            }
        }

        $data['dayPrices'] = $existingPrices; // Pass as array

        return view('admin.package.regular.regular-edit', $data);
    }

    public function storeRegular(RegularPackageStoreUpdateRequest $request)
    {
        // dd($request->all());
        try {
            $this->packageService->saveRegularPackage($request->validated());
            ToastMagic::success('Package created successfully!');

            return redirect()->route('admin.packege.list');
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            ToastMagic::error('Something went wrong while creating the package.');

            return back()->withInput();
        }
    }

    /**
     * Create package prices with default null rider_type_id
     */
    private function regularPackagePriceCreate(Package $package, array $dayPrices): void
    {
        // Get price types
        $weekdayPriceType = PriceType::where('slug', 'weekday')->first();
        $weekendPriceType = PriceType::where('slug', 'weekend')->first();

        $pricesToCreate = [];
        $now = now();

        foreach ($dayPrices as $priceData) {
            // Skip invalid data
            if (empty($priceData['day']) || ! isset($priceData['price'])) {
                continue;
            }

            $day = $priceData['day'];
            $price = $priceData['price'];
            // Determine price type (weekday/weekend)
            $isWeekend = in_array($day, ['fri', 'sat']);
            $priceTypeId = $isWeekend ? $weekendPriceType->id : $weekdayPriceType->id;

            $pricesToCreate[] = [
                'package_id' => $package->id,
                'package_type_id' => $package->package_type_id,
                'day' => $day,
                'price' => $price,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($pricesToCreate)) {
            PackagePrice::insert($pricesToCreate);
        }
    }

    public function updateRegular(RegularPackageStoreUpdateRequest $request, Package $package)
    {
        try {
            $this->packageService->saveRegularPackage($request->validated(), $package);
            ToastMagic::success('Regular package updated successfully!');

            return redirect()->route('admin.packege.list');
        } catch (Throwable $e) {
            \Log::error($e->getMessage());
            ToastMagic::error('Something went wrong while updating the package.');

            return back()->withInput();
        }
    }

    /**
     * Determine day type (weekend/weekday)
     */
    private function getDayType(string $day): string
    {
        $weekendDays = ['fri', 'sat', 'sun'];

        return in_array($day, $weekendDays) ? 'weekend' : 'weekday';
    }

    public function createAtvUtv()
    {
        $data['page_title'] = 'Create ATV/UTV Package';
        $data['page_desc'] = 'Create ATV/UTV Package';

        $data['vehicleTypes'] = VehicleType::with('images')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $data['package'] = null;

        $data['packageTypes'] = PackageType::whereNotNull('parent_id')
            ->active()
            ->get();
        $data['days'] = weekDays();
        $data['riderTypes'] = RiderType::get();

        return view('admin.package.atv.create', $data);
    }

    public function editAtvUtv(Package $package)
    {
        $data['page_title'] = 'Edit ATV/UTV Package';
        $data['page_desc'] = 'Edit ATV/UTV Package';

        $data['vehicleTypes'] = VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        $package->load(['packagePrices', 'images']);

        $data['package'] = $package;

        $data['packageTypes'] = PackageType::whereNotNull('parent_id')
            ->active()
            ->get();
        $data['days'] = weekDays();
        $data['riderTypes'] = RiderType::get();

        // Prepare dayPrices for Blade
        $dayPrices = [];
        foreach ($package->packagePrices as $price) {
            $day = $price->day;
            if (! isset($dayPrices[$day])) {
                $dayPrices[$day] = [];
            }
            $dayPrices[$day][] = [
                'rider_type_id' => $price->rider_type_id,
                'price' => $price->price,
            ];
        }
        $data['dayPrices'] = $dayPrices;

        $data['weekendDays'] = PackageWeekendDay::where('package_id', $package->id)->pluck('day')->toArray();
        // dd($data['weekendDays']);

        return view('admin.package.atv.edit', $data);
    }

    public function storeAtvUtv(AtvUtvPackageRequest $request)
    {
        // dd($request->all());
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Step 1: Create Package
            $package = Package::create([
                'name' => $validated['packageName'],
                'type' => 'atv',
                'package_type_id' => 2, // atv type
                'vehicle_type_id' => $validated['vehicleType'],
                'subtitle' => $validated['subTitle'] ?? null,
                'details' => $validated['details'] ?? null,
                'is_active' => true,
            ]);

            // Step 2: Handle package prices
            $dayPrices = collect(json_decode($request->day_prices, true) ?? []);

            if ($dayPrices->isNotEmpty()) {
                foreach ($dayPrices as $dayPrice) {
                    $priceType = PriceType::where('slug', $dayPrice['type'])->first();
                    if (! $priceType) {
                        continue;
                    }
                    PackagePrice::updateOrCreate(
                        [
                            'package_id' => $package->id,
                            'price_type_id' => $priceType->id,
                            'day' => $dayPrice['day'],
                            'rider_type_id' => $dayPrice['rider_type_id'],
                        ],
                        [
                            'price' => $dayPrice['price'],
                            'is_active' => true,
                            'package_type_id' => $package->package_type_id,
                        ]
                    );
                }
            }

            DB::commit();

            ToastMagic::success('ATV/UTV package created successfully!');

            return redirect()->route('admin.packege.list');

        } catch (Throwable $e) {
            DB::rollBack();
            ToastMagic::error('Failed to create package: '.$e->getMessage());

            return back()->withInput();
        }
    }

    public function show(Package $package)
    {
        $package->load([
            'vehicleTypes.images',
            'images',
            'packagePrices.riderType',
            'packagePrices.priceType',
        ]);

        return view('admin.package-show', compact('package'));
    }

    public function editRegular(Package $package)
    {
        $package->load(['variants.prices', 'images']);

        return view('admin.regular-packege-management', compact('package'));
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
