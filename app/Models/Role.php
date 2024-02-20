<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "code",
        "role",
        "root_id",
    ];

    public function root() {
        return $this->belongsTo(Root::class, 'root_id', 'id');
    }
}
