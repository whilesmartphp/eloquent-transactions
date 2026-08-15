<?php

namespace Whilesmart\Transactions\Enums;

use Whilesmart\Transactions\Enums\TransactionDirection as Direction;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Transfer = 'transfer';
    case Fee = 'fee';
    case Adjustment = 'adjustment';

    public static function values(): array
    {
        return array_map(fn (self $t) => $t->value, self::cases());
    }

    /**
     * The natural direction of a type. Transfer and adjustment can go either
     * way, so they carry no default and must be given a direction explicitly.
     */
    public function defaultDirection(): ?Direction
    {
        return match ($this) {
            self::Deposit => Direction::Credit,
            self::Withdrawal, self::Fee => Direction::Debit,
            self::Transfer, self::Adjustment => null,
        };
    }
}
