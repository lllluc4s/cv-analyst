<?php

if (!function_exists('app_url')) {
    /**
     * Get the application URL dynamically
     * 
     * @param string $path
     * @return string
     */
    function app_url(string $path = ''): string
    {
        $url = rtrim(config('app.url'), '/');
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}

if (!function_exists('frontend_url')) {
    /**
     * Get the frontend URL dynamically
     * 
     * @param string $path
     * @return string
     */
    function frontend_url(string $path = ''): string
    {
        $url = rtrim(config('app.frontend_url'), '/');
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}

if (!function_exists('api_url')) {
    /**
     * Get the API URL dynamically
     * 
     * @param string $path
     * @return string
     */
    function api_url(string $path = ''): string
    {
        $url = rtrim(config('app.url'), '/') . '/api';
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}

if (!function_exists('storage_url')) {
    /**
     * Get the storage URL dynamically
     * 
     * @param string $path
     * @return string
     */
    function storage_url(string $path = ''): string
    {
        $url = rtrim(config('app.url'), '/') . '/storage';
        return $path ? $url . '/' . ltrim($path, '/') : $url;
    }
}
