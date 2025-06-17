<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Реєстрація нового користувача
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        // Створення API токену
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Вхід користувача
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'message' => 'Невірні облікові дані.'
        ], 401);
    }

    /**
     * Вихід користувача
     */
    public function logout(Request $request)
    {
        Log::info('Logout request received.');

        try {
            Auth::guard('web')->logout(); // Вийти з guard 'web'

            $request->session()->invalidate(); // Інвалідувати сесію
            $request->session()->regenerateToken(); // Згенерувати новий CSRF токен

            Log::info('User logged out successfully.');

            return response()->json(['message' => 'Ви успішно вийшли.'], 200);
        } catch (\Exception $e) {
            Log::error('Error during logout: ' . $e->getMessage());
            return response()->json(['message' => 'Помилка при виході.'], 500);
        }
    }

    /**
     * Перевірка автентифікації
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
