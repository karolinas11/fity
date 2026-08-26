<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromoService
{
    /** Podrazumevani probni period za sve nove korisnike. */
    public const DEFAULT_TRIAL_DAYS = 7;

    /** Probni period za type = 3 (tester). Postoji od ranije, cuvamo ga. */
    public const TESTER_TRIAL_DAYS = 30;

    public const TESTER_TYPE = 3;

    /**
     * Broj dana probnog perioda koji stare verzije aplikacije same racunaju iz
     * created_at. Potrebno nam je da bismo im mogli servirati pomeren created_at.
     *
     * @see legacyCreatedAt()
     */
    public function baseTrialDays(User $user): int
    {
        return (int) $user->type === self::TESTER_TYPE
            ? self::TESTER_TRIAL_DAYS
            : self::DEFAULT_TRIAL_DAYS;
    }

    /**
     * Datum isteka probnog perioda. Fallback na created_at + osnovni broj dana
     * pokriva redove koji su nastali pre migracije.
     */
    public function trialEndsAt(User $user): ?Carbon
    {
        if ($user->trial_ends_at) {
            return Carbon::parse($user->trial_ends_at);
        }

        return $user->created_at
            ? Carbon::parse($user->created_at)->addDays($this->baseTrialDays($user))
            : null;
    }

    /**
     * Preostali broj dana probnog perioda, racunat po kalendarskim danima.
     */
    public function remainingTrialDays(User $user): int
    {
        $trialEndsAt = $this->trialEndsAt($user);

        if (!$trialEndsAt) {
            return 0;
        }

        // Carbon 3 vraca float; round() a ne cast, da prelazak na zimsko/letnje
        // racunanje vremena (razlika od 6.96 dana za raspon od 7) ne pojede dan.
        $days = (int) round(Carbon::today()->diffInDays($trialEndsAt->copy()->startOfDay(), false));

        return max(0, $days);
    }

    /**
     * Verzije aplikacije pre promo funkcionalnosti racunaju preostali probni period
     * kao `baseTrialDays - (danas - created_at)`. Da bi i one prikazale i postovale
     * produzeni period, u API odgovoru im vracamo created_at pomeren tako da ta
     * formula da tacan rezultat. Za korisnike bez promo koda rezultat je identican
     * originalnom created_at, pa je pomeranje bezbedno primeniti na sve.
     *
     * Vrednost se NE upisuje u bazu - koristi se samo u odgovoru na GET /api/user.
     */
    public function legacyCreatedAt(User $user): ?Carbon
    {
        if (!$user->created_at) {
            return null;
        }

        $createdAt = Carbon::parse($user->created_at);
        $offsetDays = $this->baseTrialDays($user) - $this->remainingTrialDays($user);

        return Carbon::today()
            ->subDays($offsetDays)
            ->setTimeFrom($createdAt);
    }

    /**
     * Iskoriscenje promo koda. Produzava probni period na `trial_days` od danas,
     * pri cemu nikad ne skracuje period koji korisnik vec ima.
     *
     * @return array{ok: bool, reason?: string, trial_ends_at?: string, trial_days_remaining?: int}
     */
    public function redeem(User $user, ?string $code): array
    {
        $promoCode = PromoCode::findByCode($code);

        if (!$promoCode) {
            return ['ok' => false, 'reason' => 'invalid_code'];
        }

        // Ponovno slanje istog koda (npr. korisnik dva puta skenira QR) nije greska.
        if ($user->promo_code === $promoCode->code) {
            return [
                'ok' => true,
                'reason' => 'already_redeemed',
                'trial_ends_at' => optional($this->trialEndsAt($user))->toIso8601String(),
                'trial_days_remaining' => $this->remainingTrialDays($user),
            ];
        }

        if ($user->promo_code !== null) {
            return ['ok' => false, 'reason' => 'other_code_already_redeemed'];
        }

        if ($user->is_subscribed) {
            return ['ok' => false, 'reason' => 'already_subscribed'];
        }

        if ($reason = $promoCode->unavailableReason()) {
            return ['ok' => false, 'reason' => $reason];
        }

        $newTrialEndsAt = Carbon::now()->addDays($promoCode->trial_days);
        $currentTrialEndsAt = $this->trialEndsAt($user);

        // Nikad ne skracujemo probni period koji korisnik vec ima.
        if ($currentTrialEndsAt && $currentTrialEndsAt->gt($newTrialEndsAt)) {
            $newTrialEndsAt = $currentTrialEndsAt;
        }

        try {
            DB::transaction(function () use ($user, $promoCode, $newTrialEndsAt) {
                $user->trial_ends_at = $newTrialEndsAt;
                $user->promo_code = $promoCode->code;
                $user->promo_redeemed_at = now();
                $user->save();

                PromoCode::where('id', $promoCode->id)->increment('redemptions_count');
            });
        } catch (\Throwable $e) {
            Log::error('Promo redeem failed for user ' . $user->id . ': ' . $e->getMessage());

            return ['ok' => false, 'reason' => 'server_error'];
        }

        return [
            'ok' => true,
            'trial_ends_at' => $newTrialEndsAt->toIso8601String(),
            'trial_days_remaining' => $this->remainingTrialDays($user->refresh()),
        ];
    }
}
