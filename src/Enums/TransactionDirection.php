<?php

namespace Whilesmart\Transactions\Enums;

enum TransactionDirection: string
{
    case Credit = 'credit';
    case Debit = 'debit';

    public static function values(): array
    {
        return array_map(fn (self $d) => $d->value, self::cases());
    }

    /**
     * The signed multiplier this direction applies to an account balance:
     * credit adds, debit subtracts.
     */
    public function sign(): int
    {
        return $this === self::Credit ? 1 : -1;
    }
}
