<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isPremiumUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plan = Auth::user()->activePlan();
		$plan_type = 'regular';
		if ( $plan != null ) {
			$plan_type = strtolower($plan->plan_type);
		}
        if ($plan_type !== 'regular'){
            return $next($request);
        } else{
            return redirect()->route('index');
        }
    }
}
