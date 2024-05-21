<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Root extends Model
{
    use HasFactory;
    protected $fillable = [
        "id",
        "canEditUsers",
        "canEditDismissUser",
        "canEditTables",
        "canEditDish",
        "canEditCategories",
        "canEditOrders",
        "canStatusOrders",
        "canResponsibleOrders",
        "canChefOrders",
        "canTableOrders",
        "canNotesOrders",
        "canCreateShift",
        "canEditShift",
        "canWatchChartsCountOrders",
        "canWatchChartsIncome",
        "canWatchChartsUserOrders",
        "canWatchChartsUserHours",
        "canBeResponsibleOrder",
        "canBeChefOrder",
        "canWatchSettingsRolesTab",
        "canCreateUser",
    ];
}
