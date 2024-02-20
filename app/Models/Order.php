<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "status",
        "responsible",
        "chef",
        "table_id",
        "notes",
        "created_at",
    ];

    public function responsible() {
        return $this->belongsTo(User::class, 'responsible', 'id');
    }

    public function table() {
        return $this->belongsTo(Table::class, 'table_id', 'id');
    }

    public function dishes() {
        return $this->belongsToMany(Dish::class,'order_dishes', 'order_id', 'dish_id');
    }

    public function orderDishes() {
        return $this->hasMany(Order_dish::class, 'order_id', 'id');
    }
}
