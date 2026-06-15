<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('account_status', 32)
                ->default(AccountStatus::Pending->value)
                ->after('email_verified_at');
        });

        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->update(['account_status' => AccountStatus::Active->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('account_status');
        });
    }
};
