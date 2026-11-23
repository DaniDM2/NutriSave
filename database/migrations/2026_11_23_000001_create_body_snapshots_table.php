<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_kg', 6, 2);
            $table->decimal('bmi', 5, 1);
            $table->decimal('body_fat_percent', 4, 1)->nullable();
            $table->decimal('muscle_percent', 4, 1)->nullable();
            $table->decimal('water_percent', 4, 1)->nullable();
            $table->string('origin');
            $table->json('photo_paths')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_snapshots');
    }
};
