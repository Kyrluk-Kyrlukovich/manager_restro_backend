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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('responsible')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('chef')->nullable();
        });
        //responsible
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
