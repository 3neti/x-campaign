# x-campaign Architecture

## Mission

`x-campaign` is the beneficiary distribution platform for the x-change Settlement Operating System.

It owns:

- campaigns
- audiences
- recipients
- distribution orchestration
- delivery visibility
- engagement tracking
- attribution tracking
- campaign analytics
- recipient intelligence

It does not own:

- voucher execution
- Pay Code generation semantics
- claim lifecycle
- redemption
- withdrawal
- disbursement
- settlement
- wallet mutation
- notification transport

## Ecosystem Position

`x-change` remains the execution platform. `x-campaign` may request Pay Code generation through a gateway contract, but it does not implement or duplicate voucher templates, execution, pricing, claims, disbursement, or settlement.

`x-feedback` remains the communication transport layer. `x-campaign` may plan or track campaign deliveries, but provider delivery belongs outside campaign core.

`x-journal` remains the audit trail. `x-campaign` may become journal-ready, but it does not own immutable audit storage.

## Phase 0 Boundary

Phase 0 establishes shape only:

- package metadata
- service provider
- contracts
- DTOs
- model-name scaffolds
- boundary documentation
- architecture tests

No migrations, persistence, routes, jobs, provider clients, Pay Code issuance, notification delivery, campaign execution, or analytics calculations are implemented in Phase 0.

