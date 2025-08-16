<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterCompanyIdFromPayload
{
    /**
     * Handle an incoming request.
     *
     * Remove company_id from request payload to prevent manipulation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Remover company_id do payload se existir
        $request->request->remove('company_id');
        
        // Também remover de query parameters
        $request->query->remove('company_id');
        
        return $next($request);
    }
}
