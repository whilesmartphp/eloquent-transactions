<?php

namespace Whilesmart\Transactions\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Posted = 'posted';
    case Void = 'void';

    public static function values(): array
    {
        return array_map(fn (self $s) => $s->value, self::cases());
    }
}
