<?php

namespace App\Console\Commands;

use App\Models\PromoCode;
use Illuminate\Console\Command;

/**
 * Kreiranje/pregled/ukidanje promo kodova bez potrebe za admin panelom.
 *
 * Primeri:
 *   php artisan promo:manage list
 *   php artisan promo:manage create GYM30 --days=30 --label="Teretane"
 *   php artisan promo:manage create SUMMER --days=14 --starts=2026-09-01 --ends=2026-09-30 --max=500
 *   php artisan promo:manage disable GYM30
 *   php artisan promo:manage enable GYM30
 */
class ManagePromoCode extends Command
{
    protected $signature = 'promo:manage
                            {action : list|create|enable|disable}
                            {code? : Kod, npr. GYM30}
                            {--days=30 : Trajanje probnog perioda u danima}
                            {--label= : Opisna etiketa (npr. naziv teretane)}
                            {--starts= : Pocetak vazenja, YYYY-MM-DD (opciono)}
                            {--ends= : Kraj vazenja, YYYY-MM-DD (opciono)}
                            {--max= : Maksimalan broj iskoriscenja (opciono)}';

    protected $description = 'Upravljanje promo kodovima za probni period';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'list' => $this->list(),
            'create' => $this->create(),
            'enable' => $this->setActive(true),
            'disable' => $this->setActive(false),
            default => $this->fail("Nepoznata akcija. Koristi: list, create, enable, disable."),
        };
    }

    private function list(): int
    {
        $rows = PromoCode::orderByDesc('created_at')->get();

        if ($rows->isEmpty()) {
            $this->info('Nema promo kodova.');
            return self::SUCCESS;
        }

        $this->table(
            ['Kod', 'Etiketa', 'Dana', 'Iskorišćeno', 'Max', 'Aktivan', 'Počinje', 'Ističe'],
            $rows->map(fn (PromoCode $p) => [
                $p->code,
                $p->label,
                $p->trial_days,
                $p->redemptions_count,
                $p->max_redemptions ?? '∞',
                $p->is_active ? 'da' : 'ne',
                optional($p->starts_at)->format('d.m.Y') ?? '-',
                optional($p->ends_at)->format('d.m.Y') ?? '-',
            ])
        );

        return self::SUCCESS;
    }

    private function create(): int
    {
        $code = strtoupper((string) $this->argument('code'));

        if ($code === '') {
            $this->error('Kod je obavezan: php artisan promo:manage create GYM30');
            return self::FAILURE;
        }

        if (PromoCode::where('code', $code)->exists()) {
            $this->error("Kod {$code} već postoji.");
            return self::FAILURE;
        }

        PromoCode::create([
            'code' => $code,
            'label' => $this->option('label'),
            'trial_days' => (int) $this->option('days'),
            'starts_at' => $this->option('starts'),
            'ends_at' => $this->option('ends'),
            'max_redemptions' => $this->option('max') !== null ? (int) $this->option('max') : null,
            'is_active' => true,
        ]);

        $this->info("Kod {$code} kreiran ({$this->option('days')} dana). Landing: /gym/{$code}");
        return self::SUCCESS;
    }

    private function setActive(bool $active): int
    {
        $code = strtoupper((string) $this->argument('code'));
        $promo = PromoCode::where('code', $code)->first();

        if (!$promo) {
            $this->error("Kod {$code} ne postoji.");
            return self::FAILURE;
        }

        $promo->update(['is_active' => $active]);
        $this->info("Kod {$code} je sada " . ($active ? 'aktivan' : 'neaktivan') . '.');
        return self::SUCCESS;
    }
}
