<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTrial
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // IZINKAN SEMUA BROADCASTING & AJAX REQUESTS
        if ($request->is('broadcasting/*') || $request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // Skip untuk Vite dev server dan static assets
        if ($this->shouldSkipCheck($request)) {
            return $next($request);
        }

        // Current route name
        $currentRoute = $request->route()?->getName();

        // Routes yang dibolehkan tanpa trial check
        $allowedRoutes = [
            'premium.pricing',
            'subscription',    // ✅ TAMBAHKAN INI
            'subscription.*',  // ✅ TAMBAHKAN INI  
            'payment.*', 
            'logout',
            'profile',
            'billing',
            'login',
            'register',
            'forgot-password',
            'reset-password'
        ];

        // Check jika current route ada di allowed routes
        if (in_array($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        // Check jika route pattern match
        if ($this->isAllowedRoutePattern($currentRoute, $allowedRoutes)) {
            return $next($request);
        }

        // Cek trial status
        if (!$user->canAccessPremium()) {
            \Log::warning('Trial expired - redirecting to pricing', [
                'user_id' => $user->id,
                'current_route' => $currentRoute,
                'user_status' => $user->getAccessStatus()
            ]);
            
            return redirect()->route('subscription')
                ->with('error', 'Masa trial Anda telah habis. Silakan berlangganan untuk mengakses fitur premium.');
        }

        return $next($request);
    }

    private function shouldSkipCheck(Request $request)
    {
        // Skip untuk Vite dev server dan static assets
        return $request->is('__vite_ping') ||
               $request->is('@vite*') ||
               $request->is('build/*') ||
               $request->is('assets/*') || 
               $request->is('css/*') || 
               $request->is('js/*') ||
               $request->is('images/*') ||
               $request->is('vendor/*') ||
               $request->is('storage/*') ||
               $request->is('favicon.ico');
    }

    private function isAllowedRoutePattern($currentRoute, $allowedRoutes)
    {
        if (!$currentRoute) return false;
        
        foreach ($allowedRoutes as $allowedRoute) {
            if (str_contains($allowedRoute, '*')) {
                $pattern = str_replace('*', '.*', $allowedRoute);
                if (preg_match('#^' . $pattern . '$#', $currentRoute)) {
                    return true;
                }
            }
        }
        return false;
    }
}