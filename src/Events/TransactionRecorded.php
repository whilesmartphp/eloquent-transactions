<?php

namespace Whilesmart\Transactions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Whilesmart\Transactions\Models\Transaction;

/**
 * Fired after a transaction is written. Hosts bridge this to react
 * asynchronously, for example to refresh the affected account balance.
 */
class TransactionRecorded
{
    use Dispatchable, SerializesModels;

    public function __construct(public Transaction $transaction) {}
}
