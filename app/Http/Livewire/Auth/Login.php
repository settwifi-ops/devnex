<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Login extends Component
{
    public $email = '';
    public $password = '';
    public $remember_me = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function mount()
    {
        // Jika sudah login, redirect langsung berdasarkan status
        if (Auth::check()) {
            $this->redirectBasedOnUserStatus();
        }
    }

    public function login()
    {
        $this->validate();

        Log::info('Login attempt', [
            'email' => $this->email,
            'remember_me' => $this->remember_me
        ]);

        if (Auth::attempt([
            'email' => $this->email,
            'password' => $this->password
        ], $this->remember_me)) {
            
            $user = Auth::user();
            
            Log::info('Login successful - User status', [
                'user_id' => $user->id,
                'email' => $user->email,
                'subscription_tier' => $user->subscription_tier,
                'access_status' => $user->getAccessStatus(),
                'should_redirect_to_pricing' => $user->shouldRedirectToPricing()
            ]);

            session()->regenerate();

            // ✅ GUNAKAN LIVEWIRE REDIRECT, BUKAN LARAVEL REDIRECT
            return $this->redirectBasedOnUserStatus();

        } else {
            Log::warning('Login failed', [
                'email' => $this->email,
                'reason' => 'Invalid credentials'
            ]);
            
            $this->addError('email', 'These credentials do not match our records.');
        }
    }

    /**
     * Redirect user berdasarkan status subscription
     * ✅ GUNAKAN LIVEWIRE REDIRECT METHOD
     */
    private function redirectBasedOnUserStatus()
    {
        $user = Auth::user();

        if ($user->shouldRedirectToPricing()) {
            Log::info('Redirecting to pricing page', ['user_id' => $user->id]);
            return $this->redirect(route('subscription'));
        } else {
            Log::info('Redirecting to dashboard', ['user_id' => $user->id]);
            return $this->redirect('/dashboard');
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}