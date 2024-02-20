<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish_photo extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "photo",
        "dish_id",
    ];
}
