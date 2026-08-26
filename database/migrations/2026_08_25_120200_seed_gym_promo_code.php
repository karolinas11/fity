<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('promo_codes')->updateOrInsert(
            ['code' => 'GYM30'],
            [
                'label' => 'Teretane - 30 dana besplatno',
                'trial_days' => 30,
                'starts_at' => '2026-09-01 00:00:00',
                'ends_at' => null,
                'max_redemptions' => null,
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('promo_codes')->where('code', 'GYM30')->delete();
    }
};
