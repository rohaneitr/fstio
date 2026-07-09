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
        Schema::create('pc_builds', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('user_id')->nullable();
            $table->decimal('total_price', 12, 4)->default(0);
            $table->integer('estimated_wattage')->default(0);
            $table->integer('version')->default(1);
            $table->string('build_hash')->nullable();
            $table->timestamps();

            // Foreign key to customer user if present
            $table->foreign('user_id')
                ->references('id')
                ->on('customers')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });

        Schema::create('pc_build_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('build_id')
                ->constrained('pc_builds')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unsignedInteger('product_id');
            $table->string('component_type');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->unique(['build_id', 'component_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pc_build_items');
        Schema::dropIfExists('pc_builds');
    }
};
