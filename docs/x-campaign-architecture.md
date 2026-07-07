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

## Phase 1F Boundary

Phase 1F adds a campaign planning repository seam:

- store an in-memory campaign plan under a caller-supplied planning key
- retrieve a plan by key
- list in-memory plans
- forget an in-memory plan

This is a repository contract baseline, not persistence. It does not add migrations, database tables, Eloquent queries, routes, jobs, Pay Code generation, notification delivery, journal writes, provider calls, wallet mutation, or money movement.

## Phase 1G Boundary

Phase 1G adds a repository-backed campaign planning workspace:

- create a plan and store it under a caller-supplied key
- retrieve stored planning state
- update, schedule, and archive stored plans through existing planning actions
- produce summaries from stored plans through the existing summary read model

The workspace is a composition seam only. It does not add routes, controllers, migrations, database persistence, queue dispatching, Pay Code generation, notification delivery, journal writes, provider calls, wallet mutation, or money movement.

## Phase 1H Boundary

Phase 1H adds audience import planning:

- describe an intended audience import
- capture source type and source reference metadata
- capture expected recipient count and column names
- return explicit no-side-effect metadata

Audience import planning is not ingestion. It does not read files, parse rows, persist imports, create recipients, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1I Boundary

Phase 1I adds repository-backed audience import workspace integration:

- validate that a planning key exists
- validate that the target audience exists inside the stored plan
- compose the audience import planner with repository-backed campaign/audience context
- return audience import plan metadata with planning key and audience name

This is still not ingestion. It does not read files, parse rows, persist imports, create recipients, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1J Boundary

Phase 1J adds recipient import row planning:

- normalize one raw row array into recipient planning input
- support common recipient column aliases
- return row status and validation diagnostics
- preserve raw row data for later operator review
- expose explicit no-side-effect metadata

This is row planning, not import execution. It does not read files, parse spreadsheets or CSVs, persist imports, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1K Boundary

Phase 1K adds repository-backed recipient import row workspace integration:

- validate that a planning key exists
- validate that the target audience exists inside the stored plan
- compose the recipient import row planner with repository-backed campaign/audience context
- return row planning metadata with planning key, audience name, and existing recipient count

This is still not ingestion and not audience mutation. It does not read files, parse spreadsheets or CSVs, persist imports, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1L Boundary

Phase 1L adds audience import row collection planning:

- accept an already in-memory collection of raw recipient row arrays
- delegate each row to the recipient import row workspace
- preserve row-level diagnostics
- summarize total, valid, and invalid row counts
- expose valid and invalid row numbers for later review

This is collection planning, not file ingestion. It does not read files, parse spreadsheets or CSVs, persist imports, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1M Boundary

Phase 1M adds audience import review summary read models:

- project a row collection plan into review status
- expose total, valid, and invalid row counts
- expose valid and invalid row numbers
- expose row-level validation issues for operator review
- mark all-valid non-empty collections as ready for approval

This is a read-only review projection, not approval or ingestion. It does not read files, parse spreadsheets or CSVs, persist imports, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1N Boundary

Phase 1N adds audience import approval decision planning:

- accept an audience import review summary
- accept an operator decision request
- approve only ready review summaries
- reject as a decision-only outcome
- fail closed for unknown decisions
- return blockers when approval is not allowed

This is a decision baseline, not mutation. It does not persist approval state, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1O Boundary

Phase 1O adds audience import approval workspace integration:

- validate planning keys against the campaign planning repository
- validate audience ids against stored campaign audience plans
- compose in-memory row collection planning
- compose read-only audience import review summaries
- compose decision-only audience import approval results
- return collection, summary, decision, effects, and workspace metadata in one result

This is an approval workspace baseline, not import execution. It does not persist approval state, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1P Boundary

Phase 1P adds approved import recipient attachment planning:

- consume an audience import approval workspace result
- require an approved decision before attachment planning is ready
- require a ready review summary before attachment planning is ready
- convert valid import rows into recipient planning inputs
- expose attachable row numbers, blocked row numbers, blockers, and effect metadata

This is an attachment plan, not recipient mutation. It does not persist approval state, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1Q Boundary

Phase 1Q adds approved import recipient attachment workspace integration:

- compose the audience import approval workspace
- compose the recipient attachment planner
- return approval context and attachment readiness together
- preserve fail-closed planning key and audience validation from the approval workspace
- expose workspace-level effect metadata

This is a workspace baseline, not recipient mutation. It does not persist approval state, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1R Boundary

Phase 1R adds approved import recipient attachment mutation decisions:

- consume recipient attachment workspace results
- accept an operator decision to attach or defer
- allow mutation only for fully ready attachment plans
- block partial, blocked, empty, or unknown-decision cases
- return decision-only metadata for a later mutation slice

This is a mutation decision point, not mutation execution. It does not persist approval state, attach recipients to audiences, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1S Boundary

Phase 1S adds approved import recipient attachment in-memory mutation:

- consume a recipient attachment workspace result
- consume an allowed recipient attachment mutation decision
- load the current campaign plan from the in-memory planning repository
- attach planned recipients to the target audience in a new `CampaignPlanData`
- store the mutated plan back into the in-memory repository
- return before/after recipient counts and no-durable-side-effect metadata

This is the first in-memory audience mutation baseline, not durable ingestion. It does not create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1T Boundary

Phase 1T adds approved import recipient attachment mutation workspace integration:

- compose the recipient attachment workspace
- compose the recipient attachment mutation decision gate
- compose the in-memory attachment mutation action
- return workspace context, decision, mutation result, effect flags, and metadata
- preserve fail-closed planning key and audience validation through the underlying workspace

This is end-to-end in-memory orchestration, not durable ingestion. It may mutate only process-local planning state through the in-memory repository. It does not create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1U Boundary

Phase 1U adds approved import recipient attachment mutation summaries:

- consume an existing recipient attachment mutation workspace result
- expose attachment, decision, mutation, row-count, recipient-count, and blocker summaries
- preserve read-only effect metadata
- expose a contract suitable for later operator or Cockpit read-model composition

This is a read-model baseline, not an execution or mutation surface. It does not trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1V Boundary

Phase 1V adds campaign audience import attachment operator read-model aggregation:

- consume existing recipient attachment mutation summaries
- aggregate import counts by status
- aggregate row counts, attached rows, blocked rows, and recipient deltas
- preserve blocker visibility for operator attention
- expose a read-only overview suitable for later workspace or Cockpit composition

This is an operator read-model baseline, not an execution, routing, persistence, or delivery surface. It does not trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.
