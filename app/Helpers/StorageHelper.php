<?php

if (!function_exists('storage_asset')) {
    /**
     * Generate a URL for a storage file (cPanel compatible)
     *
     * @param string $path
     * @return string
     */
    function storage_asset($path)
    {
        // Remove 'storage/' prefix if present
        if (strpos($path, 'storage/') === 0) {
            $path = substr($path, 8);
        }
        
        // Ensure path doesn't start with /
        $path = ltrim($path, '/');
        
        // Return asset URL
        return asset('storage/' . $path);
    }
}



