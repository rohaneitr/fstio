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
        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->boolean('status')->default(true)->after('website_url');
            $table->text('description')->nullable()->after('status');
            $table->string('meta_title')->nullable()->after('description');
            $table->string('meta_keywords')->nullable()->after('meta_title');
            $table->text('meta_description')->nullable()->after('meta_keywords');
            $table->integer('sort_order')->default(0)->after('meta_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'status',
                'description',
                'meta_title',
                'meta_keywords',
                'meta_description',
                'sort_order',
            ]);
        });
    }
};
