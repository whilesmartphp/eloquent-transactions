<?php

namespace Whilesmart\Transactions\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerController;
use Whilesmart\Transactions\Enums\TransactionDirection;
use Whilesmart\Transactions\Enums\TransactionStatus;
use Whilesmart\Transactions\Enums\TransactionType;
use Whilesmart\Transactions\Http\Requests\StoreTransactionRequest;
use Whilesmart\Transactions\Http\Requests\StoreTransferRequest;
use Whilesmart\Transactions\Http\Requests\UpdateTransactionRequest;
use Whilesmart\Transactions\Http\Resources\TransactionResource;
use Whilesmart\Transactions\Models\Transaction;

class TransactionController extends Controller
{
    use AuthorizesOwnerController;

    public function index(Request $request): JsonResponse
    {
        $query = $this->scopeAccessibleOwners(Transaction::query(), $request->user());

        if ($request->filled('owner_type') && $request->filled('owner_id')) {
            $query->where('owner_type', $request->input('owner_type'))
                ->where('owner_id', $request->input('owner_id'));
        }

        if ($request->filled('account_type') && $request->filled('account_id')) {
            $query->where('account_type', $request->input('account_type'))
                ->where('account_id', $request->input('account_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('q')) {
            $term = '%'.strtolower($request->input('q')).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('lower(description) like ?', [$term])
                    ->orWhereRaw('lower(reference) like ?', [$term])
                    ->orWhereRaw('lower(counterparty) like ?', [$term]);
            });
        }

        $transactions = $query->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => TransactionResource::collection($transactions)->response()->getData(true),
        ]);
    }

    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = Transaction::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    public function show(Transaction $transaction, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($transaction, $request->user());

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $this->authorizeAccessTo($transaction, $request->user());
        $transaction->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction->fresh()),
        ]);
    }

    public function destroy(Transaction $transaction, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($transaction, $request->user());
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaction deleted.',
        ]);
    }

    /**
     * Record a transfer as two posted legs, a debit on the source account and
     * a matching credit on the destination, sharing one transfer group.
     */
    public function transfer(StoreTransferRequest $request): JsonResponse
    {
        $data = $request->validated();
        $group = (string) Str::uuid();

        $shared = [
            'owner_type' => $data['owner_type'],
            'owner_id' => $data['owner_id'],
            'type' => TransactionType::Transfer,
            'status' => TransactionStatus::Posted,
            'amount_cents' => $data['amount_cents'],
            'currency' => $data['currency'] ?? 'USD',
            'transfer_group' => $group,
            'counterparty' => $data['counterparty'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ];

        [$out, $in] = DB::transaction(function () use ($shared, $data) {
            $out = Transaction::create($shared + [
                'account_type' => $data['from_account_type'],
                'account_id' => $data['from_account_id'],
                'direction' => TransactionDirection::Debit,
                'counter_account_type' => $data['to_account_type'],
                'counter_account_id' => $data['to_account_id'],
            ]);

            $in = Transaction::create($shared + [
                'account_type' => $data['to_account_type'],
                'account_id' => $data['to_account_id'],
                'direction' => TransactionDirection::Credit,
                'counter_account_type' => $data['from_account_type'],
                'counter_account_id' => $data['from_account_id'],
            ]);

            return [$out, $in];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'from' => new TransactionResource($out),
                'to' => new TransactionResource($in),
            ],
        ], 201);
    }

    /**
     * Void a transaction so it no longer affects the balance. A transfer voids
     * both of its legs together.
     */
    public function void(Transaction $transaction, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($transaction, $request->user());

        DB::transaction(function () use ($transaction) {
            if ($transaction->transfer_group) {
                Transaction::where('transfer_group', $transaction->transfer_group)
                    ->update(['status' => TransactionStatus::Void->value]);
            } else {
                $transaction->update(['status' => TransactionStatus::Void]);
            }
        });

        return response()->json([
            'success' => true,
            'data' => new TransactionResource($transaction->fresh()),
        ]);
    }
}
