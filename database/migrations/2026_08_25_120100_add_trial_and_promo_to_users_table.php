<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('promo_code', 32)->nullable();
            $table->timestamp('promo_redeemed_at')->nullable();
            $table->index('promo_code');
        });

        // Do sada je trajanje probnog perioda bilo implicitno: created_at + 7 dana
        // (odnosno + 30 za type = 3 / tester). Prepisujemo to u trial_ends_at tako da
        // postojeci korisnici zadrze tacno isti datum isteka koji su i do sada imali.
        //
        // Kolona `type` je u produkciji dodata mimo migracija, pa je ovde proveravamo
        // umesto da je podrazumevamo - inace migrate:fresh na praznoj bazi puca.
        $hasType = Schema::hasColumn('users', 'type');

        if (DB::connection()->getDriverName() === 'mysql') {
            $days = $hasType ? 'CASE WHEN type = 3 THEN 30 ELSE 7 END' : '7';

            DB::statement("
                UPDATE users
                SET trial_ends_at = DATE_ADD(created_at, INTERVAL {$days} DAY)
                WHERE trial_ends_at IS NULL AND created_at IS NOT NULL
            ");
        } else {
            // Portabilna varijanta za testove (sqlite i sl.) - produkcija je uvek mysql.
            User::whereNull('trial_ends_at')->whereNotNull('created_at')
                ->each(function (User $user) use ($hasType) {
                    $days = ($hasType && (int) $user->type === 3) ? 30 : 7;
                    $user->newQuery()->whereKey($user->getKey())
                        ->update(['trial_ends_at' => $user->created_at->copy()->addDays($days)]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['promo_code']);
            $table->dropColumn(['trial_ends_at', 'promo_code', 'promo_redeemed_at']);
        });
    }
};
