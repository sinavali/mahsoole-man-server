<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('title');
            $table->string('vendor_uuid');
            $table->integer('active')->default(0);
            $table->string('status')->default('published');
            $table->double('price')->default(0);
            $table->double('off_price')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('sku')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};