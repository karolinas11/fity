<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\User;
use App\Services\AuthService;
use App\Services\PromoService;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected AuthService $authService;
    protected PromoService $promoService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->promoService = new PromoService();
    }

    /**
     * Provera koda bez prijave - koristi je landing stranica da zna
     * da li da prikaze ponudu i koliko dana nudi.
     */
    public function show(string $code)
    {
        $promoCode = PromoCode::findByCode($code);

        if (!$promoCode) {
            return response()->json(['valid' => false, 'reason' => 'invalid_code'], 404);
        }

        $reason = $promoCode->unavailableReason();

        return response()->json([
            'valid' => $reason === null,
            'reason' => $reason,
            'code' => $promoCode->code,
            'trial_days' => $promoCode->trial_days,
        ]);
    }

    /**
     * Iskoriscenje koda za prijavljenog korisnika.
     */
    public function redeem(Request $request)
    {
        $firebaseUid = $this->authService->verifyUserAndGetUid($request->header('Authorization'));
        if (!$firebaseUid) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('firebase_uid', $firebaseUid)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $result = $this->promoService->redeem($user, $request->input('code'));

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
