<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roots', function (Blueprint $table) {
            $table->boolean('canWatchChartsCountOrders')->default(false);
            $table->boolean('canWatchChartsIncome')->default(false);
            $table->boolean('canWatchChartsUserOrders')->default(false);
            $table->boolean('canWatchChartsUserHours')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
