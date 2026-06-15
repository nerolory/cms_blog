<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->string('featured_image_path')->nullable()->after('body');
            $table->string('background_image_path')->nullable()->after('featured_image_path');
            $table->string('theme_primary_color', 7)->nullable()->after('background_image_path');
            $table->string('theme_accent_color', 7)->nullable()->after('theme_primary_color');
            $table->unsignedTinyInteger('content_opacity')->default(100)->after('theme_accent_color');
            $table->string('editor_mode', 16)->default('simple')->after('content_opacity');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn([
                'featured_image_path',
                'background_image_path',
                'theme_primary_color',
                'theme_accent_color',
                'content_opacity',
                'editor_mode',
            ]);
        });
    }
};
