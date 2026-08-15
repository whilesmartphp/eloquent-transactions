<?php

namespace Whilesmart\Transactions\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'account_type' => $this->account_type,
            'account_id' => $this->account_id,
            'reference' => $this->reference,
            'type' => $this->type?->value,
            'direction' => $this->direction?->value,
            'status' => $this->status?->value,
            'amount_cents' => (int) $this->amount_cents,
            'signed_amount_cents' => $this->signedAmountCents(),
            'currency' => $this->currency,
            'counter_account_type' => $this->counter_account_type,
            'counter_account_id' => $this->counter_account_id,
            'transfer_group' => $this->transfer_group,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'counterparty' => $this->counterparty,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'description' => $this->description,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
