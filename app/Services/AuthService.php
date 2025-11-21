<?php

namespace App\Services;

use Illuminate\Support\Str;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AuthService
{
    protected $firebaseAuth;
    protected $messaging;

    public function __construct() {
        $factory = (new Factory)->withServiceAccount(base_path('fity-8a542-firebase-adminsdk-fbsvc-3845d64334.json'));

        $this->firebaseAuth = $factory->createAuth();
        $this->messaging = $factory->createMessaging();
    }

    public function verifyUserAndGetUid($authorizationHeader) {
        if (!$authorizationHeader || !Str::startsWith($authorizationHeader, 'Bearer ')) {
            return false;
        }
        $idToken = Str::replaceFirst('Bearer ', '', $authorizationHeader);
        try {
            $verifiedIdToken = $this->firebaseAuth->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->get('sub');
        } catch (\Kreait\Firebase\Exception\AuthException $e) {
            return false;
        }
    }

    public function sendNotification($deviceToken, $title, $body) {
        try {
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification(Notification::create($title, $body));

            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
