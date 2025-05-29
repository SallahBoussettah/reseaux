<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Direct database query to check current values
        $dbSettings = DB::table('settings')->get();
        Log::info('Current settings in database:', $dbSettings->toArray());
        
        // Get settings grouped by their group
        $settings = Setting::orderBy('group')
            ->orderBy('order')
            ->get()
            ->groupBy('group');
            
        return view('dashboard.settings', compact('settings'));
    }
    
    /**
     * Update settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // Debug: Log all request data
        Log::info('Settings update request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            Log::error('Settings validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $settings = $request->input('settings', []);
        
        // Debug: Log settings array
        Log::info('Settings to update:', $settings);
        
        foreach ($settings as $setting) {
            if (isset($setting['key'])) {
                // Debug: Log each setting being updated
                Log::info('Updating setting:', [
                    'key' => $setting['key'],
                    'value' => $setting['value'] ?? 'null'
                ]);
                
                Setting::set($setting['key'], $setting['value'] ?? '');
                
                // Debug: Verify the setting was saved correctly
                $savedValue = Setting::get($setting['key']);
                Log::info('Setting after save:', [
                    'key' => $setting['key'],
                    'saved_value' => $savedValue
                ]);
                
                // Direct database check
                $dbValue = DB::table('settings')->where('key', $setting['key'])->value('value');
                Log::info('Database value after save:', [
                    'key' => $setting['key'],
                    'db_value' => $dbValue
                ]);
            }
        }
        
        // Clear all settings cache
        Cache::flush();
        
        // Verify all settings after cache flush
        $allSettings = Setting::all();
        Log::info('All settings after update:', $allSettings->toArray());
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully');
    }
}
