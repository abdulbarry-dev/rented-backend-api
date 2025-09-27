<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['national_id'])->default('national_id');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->json('image_paths'); // Array of image file paths
            $table->text('notes')->nullable(); // Admin notes for rejection/approval
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['verification_status', 'submitted_at']);
            $table->index('reviewed_by');
        });

        // Product verifications table
        Schema::create('product_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable(); // Admin notes for rejection/approval
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['verification_status', 'submitted_at']);
            $table->index(['product_id', 'verification_status']);
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_verifications');
        Schema::dropIfExists('user_verifications');
    }
};
