<?php

namespace Whilesmart\Transactions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Whilesmart\Transactions\Enums\TransactionDirection;
use Whilesmart\Transactions\Enums\TransactionStatus;
use Whilesmart\Transactions\Enums\TransactionType;
use Whilesmart\Transactions\Models\Transaction;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'account_type' => 'App\\Models\\Account',
            'account_id' => 1,
            'type' => TransactionType::Deposit->value,
            'direction' => TransactionDirection::Credit->value,
            'status' => TransactionStatus::Posted->value,
            'amount_cents' => $this->faker->numberBetween(1000, 500000),
            'currency' => 'USD',
            'occurred_at' => now(),
        ];
    }

    public function withdrawal(): static
    {
        return $this->state(fn () => [
            'type' => TransactionType::Withdrawal->value,
            'direction' => TransactionDirection::Debit->value,
        ]);
    }
}
