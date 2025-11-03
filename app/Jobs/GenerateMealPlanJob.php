<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Recipe;
use App\Models\UserRecipe;
use App\Models\UserAllergy;
use App\Models\UserRecipeFoodstuff;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Uverite se da ste importovali potrebne servise ako se koriste unutar klase
use App\Services\RecipeFoodstuffService;
// NAPOMENA: UserService vam ne treba jer se getMacrosForUser2 poziva u kontroleru

class GenerateMealPlanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $uniqueFor = 600;

    protected $user;
    protected $target;
    protected $allergyIds;

    // Injektujemo servise u Job (ili ih prosledimo kroz konstruktor ako su već instancirani)
    protected $recipefoodstuffService;

    /**
     * Kreira novu instancu posla.
     * @param User $user Korisnik (Laravel će automatski serijalizovati model)
     * @param array $target Makro ciljevi
     * @param array $allergyIds ID-evi alergena
     * @param RecipeFoodstuffService $recipefoodstuffService
     */
    public function __construct(User $user, array $target, array $allergyIds)
    {
        $this->user = $user;
        $this->target = $target;
        $this->allergyIds = $allergyIds;

        // Ručno injektovanje servisa (ili koristite "Service Container Resolution" u handle metodi)
        $this->recipefoodstuffService = app(RecipeFoodstuffService::class);
    }

    /**
     * Izvršava posao.
     * Ovo je mesto gde ide vaša dugačka logika.
     */
    public function handle()
    {
        $user = $this->user;
        $target = $this->target;
        $allergyIds = $this->allergyIds;
        $userId = $user->id;

        try {
            Log::info('Job started for user: ' . $userId);

            // 1. HTTP Poziv ka Algoritmu
            $response = Http::timeout(10000)
                ->withoutVerifying()
                ->post('https://algo.getfity.app/meal-plan', [
                    'target_calories' => $target['calories'],
                    'target_protein' => $target['proteins'],
                    'target_fat' => $target['fats'],
                    'meals_num' => $user->meals_num,
                    'tolerance_calories' => $user->tolerance_calories,
                    'tolerance_proteins' => $user->tolerance_proteins,
                    'tolerance_fats' => $user->tolerance_fats,
                    'days' => $user->days,
                    'allergy_holder_ids' => $allergyIds
                ]);

            if ($response->failed()) {
                Log::error('Algo API failed for user ' . $userId, ['response' => $response->body()]);
                // Opciono: Dodati logiku za ponovni pokušaj ili obaveštenje korisnika o grešci
                return;
            }

            $data = $response->json();
            shuffle($data['daily_plans']);

            // 2. Logika za upis u bazu
            $i = 0;
            for($k = 0; $k < 5; $k++) {
                foreach ($data['daily_plans'] as $day) {
                    if(!$day['exists']) continue;
                    $date = date('Y-m-d', strtotime('+' . $i . ' days'));
                    $i++;
                    $lunch = false;

                    foreach ($day['meals'] as $meal) {
                        $r = Recipe::find($meal['same_meal_id']);
                        if (!$r) {
                            Log::warning("Recipe ID {$meal['same_meal_id']} not found.");
                            continue;
                        }

                        $userRecipe = UserRecipe::create([
                            'user_id' => $userId,
                            'recipe_id' => $meal['same_meal_id'],
                            'status' => 'active',
                            'date' => $date,
                            'type' => $lunch && $r->type == 2 ? 4 : $r->type
                        ]);

                        if($r->type == 2) {
                            $lunch = true;
                        }

                        // Logika za namirnice
                        $foodstuffs = $this->recipefoodstuffService->getRecipeFoodstuffs($meal['same_meal_id']);

                        foreach ($foodstuffs as $foodstuff) {
                            if($foodstuff->proteins_holder == 0 && $foodstuff->fats_holder == 0 && $foodstuff->carbohydrates_holder == 0) {
                                UserRecipeFoodstuff::create([
                                    'user_recipe_id' => $userRecipe->id,
                                    'foodstuff_id' => $foodstuff->foodstuff_id,
                                    'amount' => $foodstuff->amount,
                                    'purchased' => 0
                                ]);
                            }
                        }

                        foreach ($meal['holder_quantities'] as $key => $holder) {
                            UserRecipeFoodstuff::create([
                                'user_recipe_id' => $userRecipe->id,
                                'foodstuff_id' => $key,
                                'amount' => $holder,
                                'purchased' => 0
                            ]);
                        }
                    }
                }
            }

            // 3. Ažuriranje korisnika (poslednji korak)
            $user->plan_generated = now();
            $user->save();

            Log::info('Job completed successfully for user: ' . $userId);
        } catch (\Exception $e) {
            Log::error('Job failed for user: ' . $userId, ['exception' => $e]);
        }

    }
}
