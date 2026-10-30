<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learnup_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'order_index'], 'lup_cat_active_order_idx');
        });

        // Default fallback category.
        if (!DB::table('learnup_categories')->where('name', 'General')->exists()) {
            DB::table('learnup_categories')->insert([
                'name'        => 'General',
                'image_path'  => null,
                'is_active'   => true,
                'order_index' => 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learnup_categories');
    }
};
