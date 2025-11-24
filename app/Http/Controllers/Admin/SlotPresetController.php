<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduleSlot;
use App\Models\SlotPreset;
use App\Models\SlotPresetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlotPresetController extends Controller
{
    public function index()
    {
        $presets = SlotPreset::withCount('items')->orderByDesc('is_default')->orderBy('name')->get();
        return view('admin.slot-presets.index', compact('presets'));
    }

    public function create()
    {
        $slots = ScheduleSlot::where('is_active', true)->orderBy('sort_order')->orderBy('start_time')->get();
        return view('admin.slot-presets.create', [
            'slots' => $slots,
        ]);
    }

    public function store(Request $request)
    {
        // Debug: Log incoming request data
        Log::info('SlotPreset store request:', $request->all());

        // Handle boolean fields before validation
        $isActive = $request->has('is_active');
        $isDefault = $request->has('is_default');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slots' => 'array',
            'slots.*' => 'exists:schedule_slots,id',
        ]);

        // Add boolean fields after validation
        $validated['is_active'] = $isActive;
        $validated['is_default'] = $isDefault;

        // Debug: Log validated data
        Log::info('SlotPreset validated data:', $validated);

        try {
            return DB::transaction(function () use ($validated) {
                if (!empty($validated['is_default']) && $validated['is_default']) {
                    SlotPreset::query()->update(['is_default' => false]);
                }

                $preset = SlotPreset::create([
                    'name' => $validated['name'],
                    'is_active' => $validated['is_active'],
                    'is_default' => $validated['is_default'],
                ]);

                Log::info('SlotPreset created:', ['id' => $preset->id, 'name' => $preset->name]);

                $sort = 1;
                foreach ($validated['slots'] ?? [] as $slotId) {
                    SlotPresetItem::create([
                        'slot_preset_id' => $preset->id,
                        'schedule_slot_id' => $slotId,
                        'sort_order' => $sort++,
                    ]);
                }

                Log::info('SlotPreset items created:', ['count' => count($validated['slots'] ?? [])]);

                return redirect()->route('admin.slot-presets.index')->with('success', 'Preset created successfully');
            });
        } catch (\Exception $e) {
            Log::error('SlotPreset creation failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors(['error' => 'Failed to create preset: ' . $e->getMessage()]);
        }
    }

    public function edit(SlotPreset $slotPreset)
    {
        // Debug: Log the incoming request
        Log::info('SlotPreset edit method called', [
            'preset_id' => $slotPreset->id,
            'preset_name' => $slotPreset->name,
            'request_url' => request()->url(),
        ]);

        $slots = ScheduleSlot::where('is_active', true)->orderBy('sort_order')->orderBy('start_time')->get();
        $selected = $slotPreset->items()->pluck('schedule_slot_id')->toArray();
        
        // Debug: Log the data being passed to the view
        Log::info('SlotPreset edit data:', [
            'preset_id' => $slotPreset->id,
            'preset_name' => $slotPreset->name,
            'slots_count' => $slots->count(),
            'selected_count' => count($selected),
            'selected_ids' => $selected,
            'all_slot_ids' => $slots->pluck('id')->toArray(),
        ]);
        
        return view('admin.slot-presets.edit', [
            'preset' => $slotPreset,
            'slots' => $slots,
            'selected' => $selected,
        ]);
    }

    public function update(Request $request, SlotPreset $slotPreset)
    {
        // Debug: Log incoming request data
        Log::info('SlotPreset update request:', [
            'preset_id' => $slotPreset->id,
            'preset_name' => $slotPreset->name,
            'request_data' => $request->all(),
            'request_url' => request()->url(),
        ]);

        // Handle boolean fields before validation
        $isActive = $request->has('is_active');
        $isDefault = $request->has('is_default');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slots' => 'array',
            'slots.*' => 'exists:schedule_slots,id',
        ]);

        // Add boolean fields after validation
        $validated['is_active'] = $isActive;
        $validated['is_default'] = $isDefault;

        // Debug: Log validated data
        Log::info('SlotPreset update validated data:', [
            'validated' => $validated,
            'slots_count' => count($validated['slots'] ?? []),
            'slots_array' => $validated['slots'] ?? [],
        ]);

        try {
            return DB::transaction(function () use ($slotPreset, $validated) {
                if (!empty($validated['is_default']) && $validated['is_default']) {
                    SlotPreset::where('id', '!=', $slotPreset->id)->update(['is_default' => false]);
                }

                $slotPreset->update([
                    'name' => $validated['name'],
                    'is_active' => $validated['is_active'],
                    'is_default' => $validated['is_default'],
                ]);

                Log::info('SlotPreset updated:', ['id' => $slotPreset->id, 'name' => $slotPreset->name]);

                // Delete existing items
                $deletedCount = $slotPreset->items()->count();
                $slotPreset->items()->delete();
                Log::info('Deleted existing items:', ['count' => $deletedCount]);

                // Create new items
                $sort = 1;
                $createdItems = [];
                foreach ($validated['slots'] ?? [] as $slotId) {
                    $item = SlotPresetItem::create([
                        'slot_preset_id' => $slotPreset->id,
                        'schedule_slot_id' => $slotId,
                        'sort_order' => $sort++,
                    ]);
                    $createdItems[] = $item->id;
                }

                Log::info('SlotPreset items updated:', [
                    'created_count' => count($createdItems),
                    'created_item_ids' => $createdItems,
                ]);

                return redirect()->route('admin.slot-presets.index')->with('success', 'Preset updated successfully');
            });
        } catch (\Exception $e) {
            Log::error('SlotPreset update failed:', [
                'error' => $e->getMessage(), 
                'trace' => $e->getTraceAsString(),
                'preset_id' => $slotPreset->id,
            ]);
            return back()->withInput()->withErrors(['error' => 'Failed to update preset: ' . $e->getMessage()]);
        }
    }

    public function destroy(SlotPreset $slotPreset)
    {
        try {
            $slotPreset->delete();
            return redirect()->route('admin.slot-presets.index')->with('success', 'Preset deleted successfully');
        } catch (\Exception $e) {
            Log::error('SlotPreset deletion failed:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to delete preset: ' . $e->getMessage()]);
        }
    }

    public function makeDefault(SlotPreset $slotPreset)
    {
        try {
            SlotPreset::query()->update(['is_default' => false]);
            $slotPreset->update(['is_default' => true, 'is_active' => true]);
            return redirect()->route('admin.slot-presets.index')->with('success', 'Preset set as default successfully');
        } catch (\Exception $e) {
            Log::error('SlotPreset make default failed:', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to set preset as default: ' . $e->getMessage()]);
        }
    }
}


