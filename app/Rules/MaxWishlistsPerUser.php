<?php

namespace App\Rules;

use Closure;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxWishlistsPerUser implements ValidationRule
{
    protected $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $wishlistsWithUserId = Wishlist::where('user_id', $this->userId)->get();
        $isAdmin = User::find($this->userId)->role_id === getAdminRoleId();

        if ($wishlistsWithUserId->count() >= 3 && !$isAdmin) {
            $fail('validation.maxWishlsitsPerUser');
        }
    }
}
