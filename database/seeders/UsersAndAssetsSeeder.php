<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Asset;

class UsersAndAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $symbols = ['BTC', 'ETH', 'ADA', 'USDT', 'BNB'];

        $user1 = User::create([
            'name' => 'User One',
            'email' => 'test-one@example.com',
            'password' => Hash::make('password'),
            'balance' => 10000,
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'test-two@example.com',
            'password' => Hash::make('password'),
            'balance' => 10000,
        ]);

        foreach ($symbols as $symbol) {
            Asset::create([
                'user_id' => $user1->id,
                'symbol' => $symbol,
                'amount' => 100,
                'locked_amount' => 0,
            ]);
        }

        foreach ($symbols as $symbol) {
            Asset::create([
                'user_id' => $user2->id,
                'symbol' => $symbol,
                'amount' => 100,
                'locked_amount' => 0,
            ]);
        }
    }
}
