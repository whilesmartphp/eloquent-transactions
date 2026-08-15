# eloquent-transactions

Account transactions for Laravel: deposits, withdrawals, transfers, fees, and adjustments recorded against an account and scoped per owner. It is the primitive for bare money movements that are not invoice payments or categorized expenses.

This is a movement primitive, not an accounting model. How balances are derived and whether the system is single- or double-entry is decided by the accounts/ledger layer that composes it; the same transactions can feed either.

## Install

```bash
composer require whilesmart/eloquent-transactions
```

Migrations register automatically. Publish them to customize:

```bash
php artisan vendor:publish --tag=transactions-migrations
php artisan vendor:publish --tag=transactions-config
```

## Model

A `Transaction` belongs to a polymorphic `owner` and targets a polymorphic `account`. Each carries a `type`, a `direction` (credit adds, debit subtracts), a positive `amount_cents`, and a `status`. Only `posted` transactions should count toward a balance.

- `type`: `deposit`, `withdrawal`, `transfer`, `fee`, `adjustment`
- `direction`: `credit`, `debit` (defaulted from the type when omitted)
- `status`: `pending`, `posted`, `void`
- `signedAmountCents()`: the amount signed by direction

Add `Whilesmart\Transactions\Traits\HasTransactions` to an owner model for a `transactions()` relation.

## Endpoints

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/api/transactions` | List, filterable by owner, account, type, status, date range, and `q` |
| POST | `/api/transactions` | Record a deposit, withdrawal, fee, or adjustment |
| GET | `/api/transactions/{transaction}` | Show |
| PUT | `/api/transactions/{transaction}` | Edit memo, counterparty, date, status, metadata |
| DELETE | `/api/transactions/{transaction}` | Soft delete |
| POST | `/api/transactions/transfer` | Move funds between two accounts as two matched legs |
| POST | `/api/transactions/{transaction}/void` | Void a transaction (a transfer voids both legs) |

## Authorization

Every request is scoped through `whilesmart/eloquent-owner-access`. The host binds an `OwnerAuthorizer`; the package never references an application user model.

## Feeding account balances

When a transaction is written, `TransactionRecorded` fires with the transaction. Bridge it in the host to refresh the affected account (and, for a transfer, the counter account). `whilesmart/eloquent-accounts` sums posted transaction credits and debits into its computed balance.

## Testing

```bash
composer test
composer pint:test
```
