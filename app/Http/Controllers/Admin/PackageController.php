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
use App\Services\XPackageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Services\AtvUtvPackageService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\PackageStoreRequest;
use App\Http\Requests\AtvUtvPackageRequest;
use App\Http\Requests\PackageUpdateRequest;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use App\Http\Requests\RegularPackageStoreUpdateRequest;

class PackageController extends Controller
{
    protected XPackageService $packageService;

    protected AtvUtvPackageService $atvUtvPackageService;

    public function __construct(XPackageService $packageService, AtvUtvPackageService $atvUtvPackageService)
    {
        $this->packageService = $packageService;
        $this->atvUtvPackageService = $atvUtvPackageService;
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
        
        $data['packageTypes'] = Cache::remember('parent_package_types', 120, function () {
            return PackageType::whereNull('parent_id')->active()->get();
        });

        return view('admin.add-packege-management', $data);
    }

    public function create()
    {
        $packages = Package::with(['vehicleTypes.images', 'images'])->orderBy('name')->get();
        
        $vehicleTypes = Cache::remember('active_vehicle_types', 120, function () {
            return VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        });

        return view('admin.add-packege-management', compact('packages', 'vehicleTypes'));
    }

    public function store(PackageStoreRequest $request)
    {
        $validated = $request->validated();

        $vehicleTypeIds = $validated['vehicle_type_ids'] ?? [];
        unset($validated['vehicle_type_ids']);

        $package = Package::create($validated);

        if ($request->hasFile('images')) {
            $request->validate([
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg ,webp,bmp,svg|max:5120',
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
        
        $data['packageTypes'] = \Illuminate\Support\Facades\Cache::remember('parent_package_types', 3600, function () {
            return PackageType::whereNotNull('parent_id')->active()->get();
        });
        
        $data['days'] = weekDays();

        return view('admin.package.regular.regular-create', $data);
    }

    public function edit(Package $package)
    {
        $data['package'] = $package;
        $data['page_title'] = 'Edit Regular Package';
        $data['page_desc'] = 'Update Package Details';

        $data['packageTypes'] = \Illuminate\Support\Facades\Cache::remember('parent_package_types', 3600, function () {
            return PackageType::whereNotNull('parent_id')->active()->get();
        });
        
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

            return redirect()->route('admin.packages.index');
        } catch (\Throwable $e) {
            dd($e->getMessage());
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

            return redirect()->route('admin.packages.index');
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

        $data['vehicleTypes'] = Cache::remember('active_vehicle_types', 3600, function () {
            return VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        });

        $data['package'] = null;
        $data['packageTypes'] = Cache::remember('parent_package_types_atv', 3600, function () {
            return PackageType::whereNotNull('parent_id')->active()->get();
        });
        $data['days'] = weekDays();
        $data['riderTypes'] = RiderType::get();

        // Add empty dayPrices for create
        $data['dayPrices'] = [];
        $data['weekendDays'] = ['fri', 'sat'];

        return view('admin.package.atv.create', $data);
    }

    public function editAtvUtv(Package $package)
    {
        $data['page_title'] = 'Edit ATV/UTV Package';
        $data['page_desc'] = 'Edit ATV/UTV Package';

        $data['vehicleTypes'] = Cache::remember('active_vehicle_types', 3600, function () {
            return VehicleType::with('images')->where('is_active', true)->orderBy('name')->get();
        });

        // ✅ Correct relationship name (based on your model)
        $package->load(['packagePrices', 'images']);

        $data['package'] = $package;
        $data['packageTypes'] = PackageType::whereNotNull('parent_id')->active()->get();
        $data['days'] = weekDays(); // ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat']
        $data['riderTypes'] = RiderType::get();

        $data['dayPrices'] = $package->packagePrices->map(function ($price) {
            return [
                'day' => $price->day,
                'rider_type_id' => $price->rider_type_id,
                'price' => $price->price,
            ];
        })->toArray();

        $data['weekendDays'] = ['fri', 'sat'];

        return view('admin.package.atv.edit', $data);
    }

    public function storeAtvUtv(AtvUtvPackageRequest $request)
    {
        $validated = $request->validated();
        // dd($validated);
        DB::beginTransaction();
        try {
            $package = Package::create([
                'name' => $validated['packageName'],
                'type' => 'atv',
                'package_type_id' => 2, // ATV/UTV package type
                'vehicle_type_id' => $validated['vehicleType'],
                'subtitle' => $validated['subTitle'] ?? null,
                'details' => $validated['details'] ?? null,
                'is_active' => true,
            ]);

            // ✅ Call service method
            $this->atvUtvPackageService->savePackagePrices($package, $validated['day_prices']);

            DB::commit();
            ToastMagic::success('ATV/UTV package created successfully!');

            return redirect()->route('admin.packages.index');

        } catch (Throwable $e) {
            DB::rollBack();
            ToastMagic::error($e->getMessage());

            return back()->withInput();
        }
    }

    public function updateAtvUtv(AtvUtvPackageRequest $request, Package $package)
    {
        $validated = $request->validated();
        // dd($validated);

        DB::beginTransaction();
        try {
            $package->update([
                'name' => $validated['packageName'],
                'vehicle_type_id' => $validated['vehicleType'],
                'subtitle' => $validated['subTitle'] ?? null,
                'details' => $validated['details'] ?? null,
            ]);

            // ✅ Call service method
            $this->atvUtvPackageService->savePackagePrices($package, $validated['day_prices']);

            DB::commit();
            ToastMagic::success('ATV/UTV package updated successfully!');

            return redirect()->route('admin.packages.index');

        } catch (Throwable $e) {

            \Log::info('Day Prices from form: Error', [
                'raw' => $e->getMessage(),
                'decoded' => json_decode($e->getMessage(), true),
            ]);

            DB::rollBack();
            ToastMagic::error($e->getMessage());

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
        $data['package'] = $package;
        $data['page_title'] = 'Edit Regular Package';
        $data['page_desc'] = 'Update Package Details';

        $data['packageTypes'] = \Illuminate\Support\Facades\Cache::remember('parent_package_types', 3600, function () {
            return PackageType::whereNotNull('parent_id')->active()->get();
        });
        
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

    public function update(PackageUpdateRequest $request, Package $package)
    {
        $validated = $request->validated();

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

    public function destroy(Package $package)
    {
        if ($package->image_path) {
            Storage::disk('public_storage')->delete($package->image_path);
        }
        $package->packagePrices()->delete();
        // $package->variants()->delete();
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
