<?php

namespace App\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email:rfc,dns|unique:users',
        'password' => 'required|min:6'
    ];

    public function register()
    {
        $this->validate();
        
        Log::info('Starting user registration via Livewire', ['email' => $this->email]);

        // Create user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password)
        ]);

        Log::info('User created via Livewire', ['user_id' => $user->id]);

        // ✅ START TRIAL - INI YANG PERLU DITAMBAHKAN
        $user->startTrial();

        Log::info('Trial started for user via Livewire', [
            'user_id' => $user->id,
            'trial_ends_at' => $user->trial_ends_at,
            'subscription_tier' => $user->subscription_tier
        ]);

        auth()->login($user);

        return redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}