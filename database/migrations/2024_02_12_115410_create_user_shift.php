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
        Schema::create('user_shift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('shift')->constrained('shifts')->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('active');
            $table->boolean('force');
            $table->bigInteger('orders');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_shift');
    }
};
