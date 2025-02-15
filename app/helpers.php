<?php

use App\Models\Role;
use Illuminate\Support\Facades\Cache;
if (!function_exists('getUserRoleId')) {
    function getUserRoleId()
    {
        return Cache::remember('role_user_id', now()->addMinutes(60), function () {
            return Role::where('name', 'user')->firstOrFail()->id;
        });
    }
}

if (!function_exists('getAdminRoleId')) {
    function getAdminRoleId()
    {
        return Cache::remember('admin_user_id', now()->addMinutes(60), function () {
            return Role::where('name', 'admin')->firstOrFail()->id;
        });
    }
}
?>