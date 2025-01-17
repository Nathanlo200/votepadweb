<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Access\AuthorizationException;

class JuryTokenIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');
        
        if (empty($authorizationHeader) || !preg_match('/^Bearer\s+(.+)$/', $authorizationHeader, $matches)) {
            $token = $request->cookie('juryToken');
            
            if (empty($token)) {
                return redirect()->route('form-authenticate');
            }
        } else {
            $token = $matches[1];
        }

        $validToken = DB::table('juries')
            ->where('token', $token)
            ->exists();

        if (!$validToken) {
            return redirect()->route('form-authenticate');
        }
        
        return $next($request);
    }
}
