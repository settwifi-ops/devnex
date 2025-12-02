<?php
// app/Http/Middleware/CheckSingleSession.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        // ⚠️ TAMBAHKAN INI - JANGAN blokir request broadcasting
        if ($request->is('broadcasting/auth')) {
            return $next($request);
        }

        $user = $request->user();
        
        if ($user) {
            $token = $request->session()->get('login_token');
            
            if (!$token) {
                // Generate new token
                $token = Str::random(60);
                $request->session()->put('login_token', $token);
                $user->update(['login_token' => $token]);
            }
            
            // Check if token matches
            if ($user->login_token !== $token) {
                auth()->logout();
                $request->session()->invalidate();
                return redirect('/login')
                    ->with('error', 'Your account was accessed from another device.');
            }
        }

        return $next($request);
    }
}