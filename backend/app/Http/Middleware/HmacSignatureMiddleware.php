<?php

namespace App\Http\Middleware;

use App\Models\FraudLog;
use Closure;

class HmacSignatureMiddleware
{
    public function handle($request, Closure $next)
    {
        if (app()->runningUnitTests()) {
            return $next($request);
        }

        $signature = $request->header('X-Signature');
        if(!$signature) {
            app(FraudLog::class)::create([
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
                'action' => 'missing_signature',
                'details' => [
                    'path'=>$request->path()
                ]
            ]);
            return response()->json(['message'=>'Missing signature'],400);
        }
        $payload = $request->getContent();
        $calc = base64_encode(hash_hmac('sha256', $payload, config('hmac.secret'),true));
        if(!hash_equals($calc,$signature)) {
            FraudLog::create([
                'user_id' => optional($request->user())->id,
                'ip' => $request->ip(),
                'action' => 'bad_signature',
                'details' => [
                    'expected' => $calc,
                    'got' => $signature
                ]
            ]);
            return response()->json([
                'message' => 'Invalid signature'
            ],401);
        }
        return $next($request);
    }
}
