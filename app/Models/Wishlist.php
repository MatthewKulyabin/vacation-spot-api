<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wishlist extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'vacation_spot_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vacationSpot()
    {
        return $this->belongsTo(VacationSpot::class, 'vacation_spot_id');
    }
}
