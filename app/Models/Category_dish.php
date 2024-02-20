<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category_dish extends Model
{
    use HasFactory;
    protected $fillable = [
        "id",
        "name",
        "code",
    ];

    public function dishes() {
        return $this->hasMany(Dish::class, 'category_id', 'id');
    }
}
