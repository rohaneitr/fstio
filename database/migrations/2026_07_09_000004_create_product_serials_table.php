<?php

declare(strict_types=1);

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
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->unsigned();
            $table->integer('inventory_source_id')->unsigned();
            $table->string('serial_number')->unique();
            $table->string('status')->default('available'); // available, reserved, sold, returned, defective
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('inventory_source_id')->references('id')->on('inventory_sources')->onDelete('cascade');

            $table->index('product_id');
            $table->index('inventory_source_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};
