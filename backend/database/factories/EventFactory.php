<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title'    => $this->faker->sentence(3),
            'outcomes' => json_encode(['A', 'B', 'Draw']),
            'starts_at'=> now()->addDays(1),
        ];
    }
}
