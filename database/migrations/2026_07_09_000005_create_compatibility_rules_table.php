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
        Schema::create('compatibility_rules', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_product_id')->unsigned();
            $table->integer('child_product_id')->unsigned();
            $table->string('rule_type');
            $table->timestamps();

            $table->foreign('parent_product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('child_product_id')->references('id')->on('products')->onDelete('cascade');

            $table->index('parent_product_id');
            $table->index('child_product_id');
            $table->unique(['parent_product_id', 'child_product_id', 'rule_type'], 'compat_rule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compatibility_rules');
    }
};
