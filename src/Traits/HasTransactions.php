<?php

namespace Whilesmart\Transactions\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Transactions\Models\Transaction;

trait HasTransactions
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'owner');
    }
}
