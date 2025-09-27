<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'password' => Hash::make('password'),
            'balance' => 500
        ]);
        User::factory()->create([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'password' => Hash::make('password'),
            'balance'=>100
        ]);
        Event::insert([
            [
                'title' => 'Team A vs Team B',
                'outcomes' => json_encode(['A','B','Draw']),
                'starts_at' => now()->addDay(),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Reds vs Blues',
                'outcomes' => json_encode(['Reds','Blues']),
                'starts_at' => now()->addDays(2),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
