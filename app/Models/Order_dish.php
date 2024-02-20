<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order_dish extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "count",
        "sum",
        "dish_id",
        "order_id",
        "updated_at",
        "created_at",
    ];

    public function dish() {
        return $this->belongsTo(Dish::class, 'dish_id', 'id');
    }
}
