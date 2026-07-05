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

## Phase 1A Boundary

Phase 1A adds campaign core state grammar only:

- campaign status normalization
- audience status normalization
- execution status normalization
- side-effect-free transition checks

The grammar is descriptive infrastructure. It does not execute campaigns, issue Pay Codes, send feedback, write journal records, run jobs, call providers, mutate wallets, or persist state.

## Phase 1B Boundary

Phase 1B adds campaign planning action contracts and in-memory implementations:

- create campaign plan
- update campaign plan
- schedule campaign plan
- archive campaign plan

These actions return immutable planning DTOs and explicit side-effect metadata. They do not persist plans, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, delete records, or move money.

## Phase 1C Boundary

Phase 1C adds audience and recipient planning contracts with in-memory implementations:

- add audience to a campaign plan
- add recipient to an audience plan
- remove recipient from an audience plan

These actions update planning DTOs only. They do not import files, parse CSVs, persist audiences or recipients, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

Recipient field normalization is limited to planning presentation:

- trim names and references
- remove spaces from mobile values
- lowercase email values

It is not KYC, notification routing, identity verification, or execution authorization.

## Phase 1D Boundary

Phase 1D adds campaign execution planning contracts with in-memory implementations:

- plan a campaign execution for an existing audience plan
- partition a planned execution into in-memory batches

Execution planning is still not execution. It does not queue jobs, persist execution records, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

Execution batches are planning partitions only. They exist to describe future scale behavior before introducing queues or persistence.

## Phase 1E Boundary

Phase 1E adds campaign summary read-model infrastructure:

- build a campaign summary from an existing in-memory plan
- expose audience summary rows without recipient detail records
- expose execution summary rows with batch and planned-recipient counts

Read models are derived views over existing DTOs. They do not query databases, register routes, persist state, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.
