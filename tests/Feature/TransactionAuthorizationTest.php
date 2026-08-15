<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\OwnerAccess\Contracts\OwnerAuthorizer;
use Whilesmart\Transactions\Models\Transaction;

class TransactionAuthorizationTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    private const ACCOUNT = 'App\\Models\\Account';

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(OwnerAuthorizer::class, new class implements OwnerAuthorizer
        {
            public function authorize(?Authenticatable $user, string $ownerType, mixed $ownerId): bool
            {
                return false;
            }

            public function scope(Builder $query, ?Authenticatable $user, string $ownerTypeColumn = 'owner_type', string $ownerIdColumn = 'owner_id'): Builder
            {
                return $query->whereRaw('0 = 1');
            }
        });
    }

    private function transaction(): Transaction
    {
        return Transaction::create([
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'account_type' => self::ACCOUNT,
            'account_id' => 1,
            'type' => 'deposit',
            'amount_cents' => 50000,
        ]);
    }

    #[Test]
    public function store_and_transfer_are_forbidden_when_authorizer_denies(): void
    {
        $this->postJson('/api/transactions', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'account_type' => self::ACCOUNT,
            'account_id' => 1,
            'type' => 'deposit',
            'amount_cents' => 9999,
        ])->assertForbidden();

        $this->postJson('/api/transactions/transfer', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'from_account_type' => self::ACCOUNT,
            'from_account_id' => 1,
            'to_account_type' => self::ACCOUNT,
            'to_account_id' => 2,
            'amount_cents' => 9999,
        ])->assertForbidden();

        $this->assertDatabaseCount('transactions', 0);
    }

    #[Test]
    public function show_update_destroy_and_void_are_forbidden_when_authorizer_denies(): void
    {
        $transaction = $this->transaction();

        $this->getJson("/api/transactions/{$transaction->id}")->assertForbidden();
        $this->putJson("/api/transactions/{$transaction->id}", ['description' => 'Hijacked'])->assertForbidden();
        $this->postJson("/api/transactions/{$transaction->id}/void")->assertForbidden();
        $this->deleteJson("/api/transactions/{$transaction->id}")->assertForbidden();

        $this->assertSame('posted', $transaction->fresh()->status->value);
    }

    #[Test]
    public function index_returns_nothing_when_scope_denies(): void
    {
        $this->transaction();

        $this->getJson('/api/transactions')
            ->assertOk()
            ->assertJsonPath('data.meta.total', 0);
    }
}
