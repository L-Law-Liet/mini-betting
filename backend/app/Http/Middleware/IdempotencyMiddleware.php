<?php

namespace App\Http\Middleware;

use App\Models\Bet;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyMiddleware
{
    public function handle($request, Closure $next)
    {
        $key = $request->header('Idempotency-Key');
        if(!$key)
            return response()->json(['message'=>'Idempotency-Key required'],400);

        $existing = Bet::query()->where('idempotency_key',$key)->first();
        if($existing)
            return response()->json($existing, 200);

        $request->attributes->set('idempotency_key',$key);
        return $next($request);
    }
}
