<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dish extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "description",
        "cost",
        "category_id",
    ];

    public function category() {
        return $this->belongsTo(Category_dish::class, 'category_id', 'id');
    }
}

