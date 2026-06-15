<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('required_permission_id')
                ->nullable()
                ->after('visibility')
                ->constrained('permissions')
                ->nullOnDelete();
        });

        DB::table('posts')
            ->where('visibility', 'like', 'permission:%')
            ->orderBy('id')
            ->each(function (object $post): void {
                $permissionName = substr((string) $post->visibility, strlen('permission:'));

                if ($permissionName === '') {
                    return;
                }

                $permissionId = Permission::query()
                    ->where('name', $permissionName)
                    ->value('id');

                if ($permissionId === null) {
                    return;
                }

                DB::table('posts')
                    ->where('id', $post->id)
                    ->update([
                        'visibility' => 'permission',
                        'required_permission_id' => $permissionId,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('posts')
            ->where('visibility', 'permission')
            ->whereNotNull('required_permission_id')
            ->orderBy('id')
            ->each(function (object $post): void {
                $permissionName = Permission::query()
                    ->where('id', $post->required_permission_id)
                    ->value('name');

                if ($permissionName === null) {
                    return;
                }

                DB::table('posts')
                    ->where('id', $post->id)
                    ->update([
                        'visibility' => 'permission:'.$permissionName,
                        'required_permission_id' => null,
                    ]);
            });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('required_permission_id');
        });
    }
};
