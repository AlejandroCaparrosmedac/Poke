<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Procesar login.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('pokemon')->with('success', 'Bienvenido a la Pokédex');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Credenciales inválidas.');
    }

    /**
     * Mostrar formulario de registro.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Procesar registro.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect('pokemon')->with('success', '¡Registro exitoso! Bienvenido a la Pokédex');
    }

    /**
     * Procesar logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Sesión cerrada correctamente.');
    }
}
