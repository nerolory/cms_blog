<?php

use App\Enums\SearchDriver;
use App\Support\Search\SearchSettingKey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')
            ->where('key', SearchSettingKey::DRIVER)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('settings')->insert([
            'key' => SearchSettingKey::DRIVER,
            'value' => SearchDriver::Database->value,
        ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('key', SearchSettingKey::DRIVER)
            ->delete();
    }
};
