<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VacationSpot extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'wishlists')->withTimestamps();
    }
}
