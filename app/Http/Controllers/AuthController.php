<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Kreait\Firebase\Factory;

class AuthController extends Controller
{
    protected $firebaseAuth;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(base_path('fity-8a542-firebase-adminsdk-fbsvc-3845d64334.json'));
        $this->firebaseAuth = $factory->createAuth();
    }

    public function firebaseLogin(Request $request) {
        $token = request()->bearerToken();

//        $request->validate([
//            'firebase_token' => 'required'
//        ]);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($token);
            $request->attributes->set('firebase_uid', $verifiedIdToken->claims()->get('sub'));
            return response()->json(['firebase_uid' => $verifiedIdToken->claims()->get('sub')], 200);

            // Verify Firebase ID Token
//            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($request->firebase_token);
//            $uid = $verifiedIdToken->claims()->get('sub');
//
//            // Get Firebase User
//            $firebaseUser = $this->firebaseAuth->getUser($uid);
//            $email = $firebaseUser->email ?? null;
//
//            if (!$email) {
//                return response()->json(['message' => 'No email found'], 400);
//            }
//
//            // Find or Create User in Laravel
//            $user = User::firstOrCreate(
//                ['email' => $email],
//                ['name' => $firebaseUser->displayName, 'password' => Hash::make(uniqid())]
//            );
//
//            // Generate Laravel API Token
//            $token = $user->createToken('auth_token')->plainTextToken;
//
//            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid Firebase Token'], 401);
        }
    }
}
