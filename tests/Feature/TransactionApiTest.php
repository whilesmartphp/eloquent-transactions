<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Transactions\Models\Transaction;

class TransactionApiTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    private const ACCOUNT = 'App\\Models\\Account';

    private function deposit(array $overrides = []): array
    {
        return array_merge([
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'account_type' => self::ACCOUNT,
            'account_id' => 1,
            'type' => 'deposit',
            'amount_cents' => 50000,
            'currency' => 'USD',
        ], $overrides);
    }

    #[Test]
    public function it_records_a_deposit_and_defaults_direction_and_reference(): void
    {
        $this->postJson('/api/transactions', $this->deposit(['description' => 'Opening float']))
            ->assertCreated()
            ->assertJsonPath('data.reference', 'TXN-00001')
            ->assertJsonPath('data.type', 'deposit')
            ->assertJsonPath('data.direction', 'credit')
            ->assertJsonPath('data.status', 'posted')
            ->assertJsonPath('data.signed_amount_cents', 50000);

        $this->assertDatabaseHas('transactions', [
            'owner_id' => 1,
            'reference' => 'TXN-00001',
            'type' => 'deposit',
            'direction' => 'credit',
        ]);
    }

    #[Test]
    public function it_defaults_a_withdrawal_to_a_debit(): void
    {
        $this->postJson('/api/transactions', $this->deposit(['type' => 'withdrawal', 'amount_cents' => 12000]))
            ->assertCreated()
            ->assertJsonPath('data.direction', 'debit')
            ->assertJsonPath('data.signed_amount_cents', -12000);
    }

    #[Test]
    public function it_rejects_a_transfer_through_the_plain_store_endpoint(): void
    {
        $this->postJson('/api/transactions', $this->deposit(['type' => 'transfer']))
            ->assertStatus(422);
    }

    #[Test]
    public function it_records_a_transfer_as_two_matched_legs(): void
    {
        $response = $this->postJson('/api/transactions/transfer', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'from_account_type' => self::ACCOUNT,
            'from_account_id' => 1,
            'to_account_type' => self::ACCOUNT,
            'to_account_id' => 2,
            'amount_cents' => 25000,
        ])->assertCreated()
            ->assertJsonPath('data.from.direction', 'debit')
            ->assertJsonPath('data.from.account_id', 1)
            ->assertJsonPath('data.from.counter_account_id', 2)
            ->assertJsonPath('data.to.direction', 'credit')
            ->assertJsonPath('data.to.account_id', 2);

        $group = $response->json('data.from.transfer_group');
        $this->assertNotEmpty($group);
        $this->assertSame($group, $response->json('data.to.transfer_group'));
        $this->assertSame(2, Transaction::where('transfer_group', $group)->count());
    }

    #[Test]
    public function it_voids_both_legs_of_a_transfer(): void
    {
        $group = $this->postJson('/api/transactions/transfer', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'from_account_type' => self::ACCOUNT,
            'from_account_id' => 1,
            'to_account_type' => self::ACCOUNT,
            'to_account_id' => 2,
            'amount_cents' => 25000,
        ])->json('data.from.transfer_group');

        $leg = Transaction::where('transfer_group', $group)->first();

        $this->postJson("/api/transactions/{$leg->id}/void")
            ->assertOk()
            ->assertJsonPath('data.status', 'void');

        $this->assertSame(2, Transaction::where('transfer_group', $group)->where('status', 'void')->count());
    }

    #[Test]
    public function it_binds_a_party_to_a_recorded_transaction(): void
    {
        $this->postJson('/api/transactions', $this->deposit([
            'party_type' => 'customer',
            'party_id' => 42,
            'counterparty' => 'Globex Trading',
        ]))->assertCreated()
            ->assertJsonPath('data.party_type', 'customer')
            ->assertJsonPath('data.party_id', 42)
            ->assertJsonPath('data.counterparty', 'Globex Trading');
    }

    #[Test]
    public function it_links_a_transaction_to_its_source_and_finds_it(): void
    {
        $source = Transaction::create($this->deposit());
        $posted = Transaction::create($this->deposit([
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
        ]));

        $this->assertSame(1, Transaction::forSource($source)->count());
        $this->assertTrue(Transaction::forSource($source)->whereKey($posted->id)->exists());
    }

    #[Test]
    public function it_filters_by_account_and_type(): void
    {
        Transaction::create($this->deposit(['account_id' => 1]));
        Transaction::create($this->deposit(['account_id' => 2, 'type' => 'withdrawal', 'direction' => 'debit']));

        $this->getJson('/api/transactions?account_type='.urlencode(self::ACCOUNT).'&account_id=2')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.account_id', 2);
    }
}
