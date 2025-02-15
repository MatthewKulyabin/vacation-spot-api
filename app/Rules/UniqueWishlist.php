<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueWishlist implements ValidationRule
{
    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $wishlistsWithVacId = Wishlist::where('user_id', $this->userId)
            ->where('vacation_spot_id', $value)
            ->exists();

        $isAdmin = User::find($this->userId)->role_id === getAdminRoleId();

        if ($wishlistsWithVacId && !$isAdmin) {
            $fail('validation.uniqueWishlist');
        }
    }
}
