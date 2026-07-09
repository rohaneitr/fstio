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
        Schema::create('inventory_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('product_id');
            $table->unsignedInteger('inventory_source_id');
            $table->unsignedInteger('user_id')->nullable();

            $table->string('action');
            $table->integer('previous_qty');
            $table->integer('new_qty');
            $table->integer('qty_change');

            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('inventory_source_id')
                ->references('id')
                ->on('inventory_sources')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('admins')
                ->onDelete('set null')
                ->onUpdate('cascade');

            // Indexes
            $table->index(['product_id', 'inventory_source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_audit_logs');
    }
};
