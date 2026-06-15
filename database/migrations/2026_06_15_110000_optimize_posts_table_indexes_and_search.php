<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->index(['status', 'published_at'], 'posts_status_published_at_index');
            $table->index('visibility', 'posts_visibility_index');
        });

        DB::table('posts')
            ->where('status', 'published')
            ->update(['is_published' => true]);

        DB::table('posts')
            ->where('status', '!=', 'published')
            ->update(['is_published' => false]);

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement("
            ALTER TABLE posts
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                to_tsvector('simple', coalesce(title, '') || ' ' || coalesce(excerpt, ''))
            ) STORED
        ");

        DB::statement('CREATE INDEX posts_search_vector_gin_index ON posts USING GIN (search_vector)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS posts_search_vector_gin_index');
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropColumn('search_vector');
            });
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_status_published_at_index');
            $table->dropIndex('posts_visibility_index');
        });
    }
};
