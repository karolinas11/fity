<?php

namespace App\Repositories;

use App\Models\UserWater;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserWaterRepository
{
    public function updateUserWater($userId, $water)
    {
        try {
            $today = Carbon::today()->format('Y-m-d');

            // SLUČAJ A: Frontend šalje samo zapreminu čaše (npr. +250)
            // Ako je tako, samo kreiramo novi red!
            return UserWater::create([
                'user_id' => $userId,
                'water'   => $water,
                'date'    => $today
            ]);

            /* SLUČAJ B (VAŽNO!):
            Ako tvoj frontend prethodno sabere vodu i pošalje ti UKUPNU cifru
            (npr. bilo je 500, on popije 250, a front ti pošalje "750"),
            moramo da izvučemo razliku pre upisa.
            U tom slučaju ZAMENI gornji return ovim kodom ispod:

            $currentTotal = UserWater::where('user_id', $userId)->where('date', $today)->sum('water');
            $increment = $water - $currentTotal;

            if ($increment > 0) {
                return UserWater::create(['user_id' => $userId, 'water' => $increment, 'date' => $today]);
            }
            return null;
            */

        } catch (QueryException $e) {
            Log::error('Can\'t update water: ' . $e->getMessage());
        }
    }
}
