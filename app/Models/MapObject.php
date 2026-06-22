<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapObject extends Model
{
    use HasFactory;
    protected $fillable = [
        "user_id",
        "reviews_url",
        "business_id",
        "business_title",
        'rating',
        'ratings_count',
        'reviews_count'
    ];
}
