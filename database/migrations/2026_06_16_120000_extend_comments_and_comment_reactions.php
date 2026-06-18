<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('post_comments', function (Blueprint $table): void {
            $table->foreignId('reply_to_id')->nullable()->after('parent_id')
                ->constrained('post_comments')->cascadeOnDelete();
            $table->index(['parent_id', 'status', 'created_at']);
        });

        DB::table('post_comments')->whereNotNull('parent_id')->update([
            'reply_to_id' => DB::raw('parent_id'),
        ]);

        Schema::create('comment_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comment_id')->constrained('post_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->timestamps();

            $table->unique(['comment_id', 'user_id']);
            $table->index(['comment_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');

        Schema::table('post_comments', function (Blueprint $table): void {
            $table->dropForeign(['reply_to_id']);
            $table->dropIndex(['parent_id', 'status', 'created_at']);
            $table->dropColumn('reply_to_id');
        });
    }
};
