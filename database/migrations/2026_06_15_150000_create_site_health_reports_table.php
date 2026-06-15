<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_health_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('context', 32);
            $table->unsignedSmallInteger('critical_count')->default(0);
            $table->unsignedSmallInteger('warning_count')->default(0);
            $table->unsignedSmallInteger('passed_count')->default(0);
            $table->json('results');
            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('completed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_health_reports');
    }
};
