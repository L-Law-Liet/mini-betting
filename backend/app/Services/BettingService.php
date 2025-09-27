<?php

namespace App\Services;

use App\Models\Bet;
use App\Models\Event;
use App\Models\LedgerTransaction;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class BettingService
{
    /**
     * @param User        $user
     * @param int         $eventId
     * @param string      $outcome
     * @param string      $amount
     * @param string|null $idempotencyKey
     * @return Bet
     * @throws \Throwable
     * @throws DomainException
     */
    public function placeBet(User $user, int $eventId, string $outcome, string $amount, ?string $idempotencyKey): Bet
    {
        try {
            DB::beginTransaction();
            $user = User::whereKey($user->id)->lockForUpdate()->first();

            if ($idempotencyKey && Bet::where('idempotency_key', $idempotencyKey)->exists()) {
                throw new DomainException('Duplicate request');
            }

            if(bccomp($user->balance, $amount,2) < 0) {
                throw new DomainException('Insufficient funds');
            }

            $event = Event::findOrFail($eventId);

            $bet = Bet::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'outcome' => $outcome,
                'amount' => $amount,
                'status' => 'placed',
                'idempotency_key' => $idempotencyKey,
            ]);

            $user->balance = bcsub($user->balance, $amount, 2);
            $user->save();

            LedgerTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $amount,
                'bet_id' => $bet->id,
                'meta' => [
                    'reason' => 'bet_place',
                    'idempotency_key' => $idempotencyKey,
                ]
            ]);

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return $bet;
    }
}
