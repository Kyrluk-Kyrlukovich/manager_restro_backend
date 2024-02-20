<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "name",
        "date_start",
        "date_end",
        "hours",
        "created_at",
        "updated_at",
        "count_staff",
    ];

    public function users() {
        return $this->belongsToMany(User::class, 'user_shift', 'shift', 'user');
    }

    public function userShifts() {
        return $this->hasMany(UserShift::class, 'shift');
    }
}
