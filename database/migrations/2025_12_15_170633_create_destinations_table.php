<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();

            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();

            $table->string('name');
            $table->string('location');
            $table->string('owner');
            $table->integer('maxOfGuest');
            $table->double('rating')->default(0);
            $table->json('ratings')->nullable();
            $table->double('price');
            $table->string('thumbnailUrl');
            $table->json('facilities')->nullable();
            $table->json('imageUrls')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
