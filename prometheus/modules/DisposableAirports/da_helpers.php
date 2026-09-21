<?php
use Illuminate\Support\Facades\DB;

// Check Disposable Module Setting
// Return mixed, either boolean or the value itself as string
// If setting is not found, return either false or provided default
if (!function_exists('DA_Setting')) {
    function DA_Setting($key, $default_value = null)
    {
        $setting = DB::table('disposable_settings')->select('key', 'value')->where('key', $key)->first();

        if (!$setting && !$default_value) {
            $result = false;
        } elseif (!$setting && $default_value) {
            $result = $default_value;
        } elseif (!$setting->value) {
            $result = $default_value;
        } elseif ($setting->value === 'false') {
            $result = false;
        } elseif ($setting->value === 'true') {
            $result = true;
        } else {
            $result = $setting->value;
        }

        return $result;
    }
}
