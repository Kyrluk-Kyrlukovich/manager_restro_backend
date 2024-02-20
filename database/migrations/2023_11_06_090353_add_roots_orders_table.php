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
            $table->boolean('canStatusOrders')->default(false);
            $table->boolean('canResponsibleOrders')->default(false);
            $table->boolean('canChefOrders')->default(false);
            $table->boolean('canTableOrders')->default(false);
            $table->boolean('canNotesOrders')->default(false);
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
