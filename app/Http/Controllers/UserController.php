<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct() {
        $this->userService = new UserService();
    }
    public function showAddUser() {
        return view('create-user');
    }

    public function addUser(Request $request) {
        $userData = [
            'goal' => $request->input('goal'),
            'height' => $request->input('height'),
            'weight' => $request->input('weight'),
            'age' => $request->input('age'),
            'gender' => $request->input('gender'),
            'activity' => $request->input('activity'),
            'insulin_resistance' => $request->input('insulin_resistance')
        ];

        $user = $this->userService->addUser($userData);
        return response()->json($user);
    }


}
