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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_uuid');
            $table->string('type')->default('unit'); // or percent
            $table->string('code')->unique();
            $table->string('from')->nullable();
            $table->string('until')->nullable();
            $table->double('amount')->default(0);
            $table->integer('include_shipping')->default(1);
            $table->string('desc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
