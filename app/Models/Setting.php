<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Setting extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'key', 'value', 'group', 'type', 'options', 'label', 'description', 'order'
    ];
    
    protected $casts = [
        'options' => 'array',
    ];
    
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        // During development, always clear the cache for this key
        if (config('app.env') !== 'production') {
            Cache::forget('setting_' . $key);
        }
        
        // Try to get from cache first
        if (Cache::has('setting_' . $key)) {
            $value = Cache::get('setting_' . $key);
            Log::info("Retrieved from cache: {$key} = {$value}");
            return $value;
        }
        
        // If not in cache, get from database
        $setting = self::where('key', $key)->first();
        
        if ($setting) {
            // Store in cache for future requests
            Cache::put('setting_' . $key, $setting->value, now()->addDay());
            Log::info("Retrieved from database: {$key} = {$setting->value}");
            return $setting->value;
        }
        
        Log::info("Using default value: {$key} = {$default}");
        return $default;
    }
    
    /**
     * Set a setting value
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public static function set($key, $value)
    {
        // First, clear the cache for this key
        Cache::forget('setting_' . $key);
        
        // Log the setting being updated
        Log::info('Setting updated:', [
            'key' => $key,
            'value' => $value
        ]);
        
        $setting = self::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->save();
        
        // Update cache with new value
        Cache::put('setting_' . $key, $value, now()->addDay());
        
        return $setting;
    }
}
