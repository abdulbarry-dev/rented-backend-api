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
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->timestamps();
            
            $table->index(['owner_id', 'status']);
        });

        // Product descriptions table
        Schema::create('product_descriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->json('product_images'); // Array of image paths for LFS
            $table->json('categories'); // Array of category names
            $table->timestamps();
            
            $table->index('product_id');
        });

        // Reviews table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('review_rate')->unsigned()->between(1, 5);
            $table->text('comment')->nullable();
            $table->timestamp('reviewed_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['product_id', 'review_rate']);
            $table->unique(['user_id', 'product_id']); // One review per user per product
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('product_descriptions');
        Schema::dropIfExists('products');
    }
};
