<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'phone_number' => ['nullable', 'string', 'max:255'],
                'password' => ['required', 'string', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' =>  Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $token_result = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $token_result,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User has registered');
        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'message' => "Something went wrong: $exception",
            ], 'Authentication failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required',
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid credentials');
            };

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'User has login');
        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'message' => "Something went wrong: $exception",
            ], 'Authentication Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'Get user success');
    }

    public function updateUser(Request $request)
    {
        $data = $request->all();

        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'phone_number' => ['nullable', 'string', 'max:255'],
            ]);

            $user = Auth::user();
            $user->update($data);

            return ResponseFormatter::success($user, 'User profile has updated');
        } catch (Exception $exception) {
            return ResponseFormatter::error([
                'message' => "Something went wrong: $exception",
            ], 'Update profile failed', 500);
        }
    }
}
