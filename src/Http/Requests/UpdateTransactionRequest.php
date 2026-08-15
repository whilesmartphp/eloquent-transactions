<?php

namespace Whilesmart\Transactions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;
use Whilesmart\Transactions\Enums\TransactionStatus;

class UpdateTransactionRequest extends FormRequest
{
    use AuthorizesOwnerRequest;

    public function authorize(): bool
    {
        return $this->authorizeOwnerOfBoundModel('transaction');
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(TransactionStatus::values())],
            'counterparty' => ['nullable', 'string', 'max:200'],
            'occurred_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
