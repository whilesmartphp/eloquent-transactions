<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('transactions.transactions_table', 'transactions'), function (Blueprint $table) {
            $table->id();
            $table->morphs('owner');
            $table->morphs('account');
            $table->string('reference');

            $table->string('type')->default('deposit');
            $table->string('direction')->default('credit');
            $table->string('status')->default('posted');

            $table->bigInteger('amount_cents');
            $table->string('currency', 3)->default('USD');

            // For a transfer, the paired leg on the other account and the id
            // shared by both legs so they can be found and reversed together.
            $table->nullableMorphs('counter_account');
            $table->string('transfer_group')->nullable();

            // The document that generated this entry (a payment or expense),
            // so posting stays idempotent and unposted sources are findable.
            $table->nullableMorphs('source');

            // The other side of the movement as a real record (a customer,
            // vendor, contact, ...). counterparty stays as a denormalized label
            // for display and for genuine one-offs with no record to bind to.
            $table->nullableMorphs('party');

            $table->string('counterparty')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['owner_type', 'owner_id', 'reference']);
            $table->index(['owner_type', 'owner_id', 'status']);
            $table->index(['account_type', 'account_id', 'status']);
            $table->index(['owner_type', 'owner_id', 'type']);
            $table->index(['owner_type', 'owner_id', 'occurred_at']);
            $table->index('transfer_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('transactions.transactions_table', 'transactions'));
    }
};
