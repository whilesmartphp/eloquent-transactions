<?php

namespace Whilesmart\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;

class StoreTransferRequest extends FormRequest
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
            'from_account_type' => ['required', 'string'],
            'from_account_id' => ['required'],
            'to_account_type' => ['required', 'string'],
            'to_account_id' => ['required'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'currency' => ['nullable', 'string', 'size:3'],
            'counterparty' => ['nullable', 'string', 'max:200'],
            'occurred_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
