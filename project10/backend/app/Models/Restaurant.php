<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'category',
        'rating',
        'album_number',
    ];
}
