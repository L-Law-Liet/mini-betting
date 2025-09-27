<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Bet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'balance' => 100,
            'password' => bcrypt('secret'),
        ]);

        $this->event = Event::factory()->create([
            'title' => 'Team A vs Team B',
            'outcomes' => json_encode(['A', 'B', 'Draw']),
        ]);
    }

    #[Test]
    public function user_can_place_a_bet_and_balance_is_deducted()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/bets', [
                'event_id' => $this->event->id,
                'outcome' => 'A',
                'amount' => 20,
            ], [
                'Idempotency-Key' => 'key-1',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('bets', [
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'amount' => 20,
        ]);

        $this->assertEquals(80, $this->user->fresh()->balance);
    }

    #[Test]
    public function user_cannot_bet_more_than_balance()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/bets', [
                'event_id' => $this->event->id,
                'outcome' => 'A',
                'amount' => 200,
            ], [
                'Idempotency-Key' => 'key-2',
            ]);

        $response->assertStatus(422);
        $this->assertEquals(100, $this->user->fresh()->balance);
    }

    #[Test]
    public function idempotency_prevents_duplicate_bets()
    {
        $headers = ['Idempotency-Key' => 'same-key'];

        $first = $this->actingAs($this->user)
            ->postJson('/api/bets', [
                'event_id' => $this->event->id,
                'outcome' => 'A',
                'amount' => 10,
            ], $headers);
        $first->assertStatus(201);

        $second = $this->actingAs($this->user)
            ->postJson('/api/bets', [
                'event_id' => $this->event->id,
                'outcome' => 'A',
                'amount' => 10,
            ], $headers);
        $second->assertStatus(200);

        $this->assertEquals(1, Bet::count());
    }

    #[Test]
    public function outcome_must_be_valid_for_event()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/bets', [
                'event_id' => $this->event->id,
                'outcome' => 'INVALID',
                'amount' => 10,
            ], [
                'Idempotency-Key' => 'key-3',
            ]);

        $response->assertStatus(422);
        $this->assertEquals(100, $this->user->fresh()->balance);
    }

    #[Test]
    public function user_cannot_double_spend_with_concurrent_bets()
    {
        $this->assertEquals(100, $this->user->balance);

        $payload = [
            'event_id' => $this->event->id,
            'outcome' => 'A',
            'amount' => 80,
        ];

        $res1 = $this->actingAs($this->user)
            ->postJson('/api/bets', $payload, [
                'Idempotency-Key' => 'key-a',
            ]);

        $res2 = $this->actingAs($this->user)
            ->postJson('/api/bets', $payload, [
                'Idempotency-Key' => 'key-b',
            ]);

        $res1->assertStatus(201);

        $res2->assertStatus(422)
            ->assertJson([
                'message' => 'Insufficient funds',
            ]);

        $this->assertEquals(20, $this->user->fresh()->balance);

        $this->assertEquals(1, Bet::count());
    }
}
