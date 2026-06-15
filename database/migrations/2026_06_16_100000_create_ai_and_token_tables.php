<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_token_wallets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('balance')->default(0);
            $table->timestamps();
        });

        Schema::create('token_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('type');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('ai_tool_results', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('tool_code');
            $table->string('status')->default('completed');
            $table->json('payload');
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'tool_code'], 'ai_tool_results_subject_tool_unique');
            $table->index(['tool_code', 'status']);
        });

        Schema::create('token_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('token_amount');
            $table->unsignedBigInteger('price_cents')->default(0);
            $table->string('currency', 3)->default('RUB');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ai_analysis_orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('comment_count')->default(0);
            $table->unsignedBigInteger('tokens_required')->default(0);
            $table->unsignedBigInteger('tokens_charged')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'post_id']);
        });

        Schema::create('site_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('view_prefix')->default('themes.default');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('site_template_themes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_template_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->string('bootstrap_theme')->nullable();
            $table->string('body_class')->nullable();
            $table->string('css_entry')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['site_template_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_template_themes');
        Schema::dropIfExists('site_templates');
        Schema::dropIfExists('ai_analysis_orders');
        Schema::dropIfExists('token_packages');
        Schema::dropIfExists('ai_tool_results');
        Schema::dropIfExists('token_transactions');
        Schema::dropIfExists('user_token_wallets');
    }
};
