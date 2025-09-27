<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBetRequest;
use App\Models\Bet;
use App\Models\FraudLog;
use App\Services\BettingService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BetController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'bets' => Bet::where('user_id',$request->user()->id)
                ->with('event')
                ->latest()
                ->get(),
            'balance' => $request->user()->balance,
        ]);
    }

    /**
     * @param StoreBetRequest $request
     * @param BettingService  $service
     * @return JsonResponse
     */
    public function store(StoreBetRequest $request, BettingService $service): JsonResponse
    {
        $data = $request->validated();
        try {
            $bet = $service->placeBet(
                $request->user(),
                (int) $data['event_id'],
                $data['outcome'],
                (string) $data['amount'],
                $request->attributes->get('idempotency_key')
            );
            $bet->load('event');
            return response()->json([
                'bet' => $bet,
                'balance' => $request->user()->balance,
            ], 201);
        } catch(\Throwable $e) {
            FraudLog::create([
                'user_id' => $request->user()->id,
                'ip' => $request->ip(),
                'action' => 'bet_rejected',
                'details' => [
                    'reason' => $e->getMessage(),
                    'payload' => $data
                ]
            ]);
            return response()->json([
                'message' => $e->getMessage()
            ],422);
        }
    }
}
