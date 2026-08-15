<?php

namespace Whilesmart\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;
use Whilesmart\Transactions\Enums\TransactionDirection;
use Whilesmart\Transactions\Enums\TransactionStatus;
use Whilesmart\Transactions\Enums\TransactionType;

class StoreTransactionRequest extends FormRequest
{
    use AuthorizesOwnerRequest;

    public function authorize(): bool
    {
        return $this->authorizeOwnerInRequest();
    }

    public function rules(): array
    {
        return [
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required'],
            'account_type' => ['required', 'string'],
            'account_id' => ['required'],
            'reference' => ['nullable', 'string', 'max:60'],
            'type' => ['required', Rule::in(TransactionType::values()), Rule::notIn([TransactionType::Transfer->value])],
            'direction' => ['nullable', Rule::in(TransactionDirection::values())],
            'status' => ['nullable', Rule::in(TransactionStatus::values())],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'counterparty' => ['nullable', 'string', 'max:200'],
            'occurred_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
