<?php

namespace App\Services;

use Illuminate\Support\Str;
use Kreait\Firebase\Factory;

class AuthService
{
    protected $firebaseAuth;

    public function __construct() {
        $factory = (new Factory)->withServiceAccount(base_path('fity-8a542-firebase-adminsdk-fbsvc-3845d64334.json'));
        $this->firebaseAuth = $factory->createAuth();
    }

    public function verifyUserAndGetUid($authorizationHeader) {
        if (!$authorizationHeader || !Str::startsWith($authorizationHeader, 'Bearer ')) {
            return response()->json(['error' => 'Authorization header missing or invalid'], 401);
        }
        $idToken = Str::replaceFirst('Bearer ', '', $authorizationHeader);

        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->get('sub');
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            return response()->json(['error' => 'Firebase Auth error: ' . $e->getMessage()], 500);
        }
    }
}
