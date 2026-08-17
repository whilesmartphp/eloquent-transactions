## [1.1.0] - 2026-08-17
- Optional `party` link so a transaction binds its counterparty to a real record (customer, contact, ...); `counterparty` stays as a denormalized label for one-offs

## [1.0.0] - 2026-08-14
- Account transactions (deposit, withdrawal, transfer, fee, adjustment), scoped per owner via owner-access
- Auto-generated per-owner transaction references, unique per owner
- Direction defaults from the transaction type (deposit credits, withdrawal and fee debit), with a signed amount for balance summing
- Transfers recorded as two matched legs sharing a transfer group, voided together
- Void and posted/pending status so only posted transactions affect a balance
- A TransactionRecorded event for hosts to bridge (e.g. to refresh an account balance)
