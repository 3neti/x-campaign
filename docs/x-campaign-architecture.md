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

## Phase 1W Boundary

Phase 1W adds campaign audience import attachment operator workspace composition:

- validate campaign planning keys through the in-memory repository
- validate audience context against the stored campaign plan
- compose existing operator read-model aggregation from supplied mutation summaries
- return overview, effect flags, and repository context metadata
- preserve fail-closed behavior for missing plans or audiences

This is an operator workspace baseline, not an execution, routing, persistence, or delivery surface. It does not trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1X Boundary

Phase 1X adds campaign audience import attachment operator workspace collection aggregation:

- consume existing operator workspace results
- aggregate audience counts by status
- aggregate import counts, attached rows, blocked rows, and recipient deltas
- preserve blocker visibility across audiences
- expose a read-only collection suitable for later operator shell or Cockpit composition

This is a collection read-model baseline, not an execution, routing, persistence, or delivery surface. It does not query persistence, trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1Y Boundary

Phase 1Y adds campaign audience import attachment operator workspace collection workspace composition:

- validate campaign planning keys through the in-memory repository
- compose existing operator workspace collection aggregation from supplied workspace results
- enrich collection metadata with campaign context
- return collection, effect flags, and repository context metadata
- preserve fail-closed behavior for missing plans

This is a read-only workspace baseline, not an execution, routing, persistence, or delivery surface. It does not trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 1Z Boundary

Phase 1Z adds campaign audience import attachment operator workspace collection operator summaries:

- consume an existing operator workspace collection workspace result
- expose aggregate campaign, audience, import, row, recipient delta, and blocker counts
- derive an operator posture from collection status
- preserve blockers for operator review visibility
- expose a read-only summary suitable for later operator shell or Cockpit composition

This is an operator summary read-model baseline, not an execution, routing, persistence, or delivery surface. It does not query persistence, trigger recipient attachment, create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2A Boundary

Phase 2A adds the durable storage boundary plan before any migration exists:

- document storage ownership and non-ownership
- name proposed durable tables
- preserve portable identifiers as public DTO identifiers
- allow JSON only for flexible metadata/context, not lifecycle truth
- sequence later persistence slices

This is a planning slice only. It does not create migrations, use a database, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2B Boundary

Phase 2B adds persistence contracts and DTOs before migrations exist:

- introduce a snapshot repository seam for future durable campaign plans
- introduce a snapshot DTO that preserves the current campaign plan DTO
- introduce explicit persistence effect metadata
- keep the existing campaign plan repository contract intact

This is a contract baseline only. It does not create migrations, bind a database repository, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2C Boundary

Phase 2C adds migration readiness review before migrations exist:

- lock initial durable table names
- lock portable identifier columns
- lock minimum lookup indexes
- constrain JSON columns to metadata/context
- keep lifecycle truth explicit in status and identifier columns

This is a review slice only. It does not create migrations, bind a database repository, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2D Boundary

Phase 2D adds the first durable database migration baseline:

- create campaign planning tables
- preserve portable identifiers as first-class columns
- expose explicit status columns
- allow JSON context columns for metadata, effects, source payloads, and review payloads
- load package migrations through the service provider

This is a storage schema slice only. It does not bind a database repository, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2E Boundary

Phase 2E adds the first Eloquent-backed durable repository baseline:

- add an Eloquent model for campaign plan records
- implement `CampaignPlanSnapshotRepository` using database storage
- bind the snapshot repository contract to the durable implementation
- keep the primary planning repository binding on the in-memory baseline until parity is proven

This is a durable snapshot repository slice only. It does not register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 2F Boundary

Phase 2F adds persistence integration parity:

- implement an Eloquent-backed `CampaignPlanRepository`
- prove repository behavior matches the in-memory baseline for core operations
- keep runtime binding on the in-memory baseline until a future explicit storage-mode decision
- preserve durable repository effects as storage-only side effects

This is a persistence parity slice only. It does not register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3A Boundary

Phase 3A adds the campaign queue boundary plan before any queue contract or job exists:

- document queue ownership and host runtime responsibilities
- define queue handoff non-goals
- preserve the distinction between queue planning, queue dispatch, and campaign execution
- sequence the remaining Phase 3 queue slices

This is a planning slice only. It does not create queue contracts, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3B Boundary

Phase 3B adds queue dispatch contracts and DTOs before real queue dispatch exists:

- define a serializable queue dispatch intent
- define a queue dispatch result envelope
- define a queue dispatch planning contract
- keep default effect metadata planning-only and non-dispatching

This is a contract slice only. It does not bind a dispatcher, register routes, run jobs, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3C Boundary

Phase 3C adds an in-memory queue dispatch planner:

- validate queue dispatch intent completeness
- return a stable planning identifier for identical dispatch intents
- preserve planning-only result metadata
- bind the queue dispatch planning contract to the in-memory planner

This is still not queue dispatch. It does not create job classes, push work to queues, register routes, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3D Boundary

Phase 3D adds queued plan payload data:

- define a serializable queued plan payload
- require planning key and operation before payload creation
- preserve arbitrary operation payload data
- provide stable correlation identifiers when a caller does not supply one

This is a payload slice only. It does not create job classes, push work to queues, register routes, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3E Boundary

Phase 3E adds a queue job wrapper:

- carry a validated queued plan payload
- opt into Laravel queue transport through a package job wrapper
- keep `handle()` as a no-op until execution integration is explicitly authorized
- expose no-side-effect metadata for boundary tests

This is a job wrapper slice only. It does not push work to queues, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 3F Boundary

Phase 3F adds the queue dispatch integration seam:

- bind a queue dispatcher contract
- push the no-op queue job wrapper through Laravel's queue facade
- prove queue handoff with queue fakes
- return queued effect metadata while keeping all execution, delivery, audit, provider, wallet, and money effects false

This is a queued handoff slice only. It does not execute campaign operations, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 4A Boundary

Phase 4A adds the campaign execution handoff boundary plan:

- document handoff ownership
- define execution handoff non-goals
- separate campaign-side handoff state from host-owned issuance and delivery behavior
- sequence the remaining Phase 4 handoff slices

This is a planning slice only. It does not create handoff contracts, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 4B Boundary

Phase 4B adds execution handoff contracts and DTOs:

- define a campaign execution handoff DTO
- define a handoff result envelope
- define a handoff planning contract
- keep default effect metadata handoff-only and non-executing

This is a contract slice only. It does not bind a handoff planner, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 4C Boundary

Phase 4C adds an in-memory execution handoff planner:

- validates that a campaign execution handoff has a planning key
- checks handoff readiness without running campaign execution
- blocks handoff when execution batches are missing
- blocks handoff when the execution state is not planned
- returns stable handoff identifiers and operator-safe handoff metadata

This is still a handoff planning slice only. It does not execute campaigns, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, persist state, or move money.

## Phase 4D Boundary

Phase 4D adds a repository-backed execution handoff workspace:

- reads existing campaign planning state through `CampaignPlanRepository`
- selects an existing campaign execution plan by execution ID
- delegates readiness evaluation to `PlansCampaignExecutionHandoffs`
- fails closed for unknown planning keys or execution IDs
- exposes side-effect metadata for host/Cockpit consumers

This workspace is an integration seam over existing planning data. It does not mutate repository state, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, or move money.

## Phase 4E Boundary

Phase 4E adds a queued payload to execution handoff mapper:

- accepts queued campaign payloads with operation `execution.handoff`
- requires a scalar non-empty `execution_id`
- maps queue metadata into handoff workspace input
- fails closed for unsupported queued operations
- preserves handoff-only side-effect metadata

The mapper does not invoke queue jobs, call the handoff workspace, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, persist state, or move money.

## Phase 4F Boundary

Phase 4F adds an execution handoff read model:

- summarizes handoff result status and readiness
- exposes planning key, handoff ID, execution ID, batch count, recipient count, and blockers
- preserves read-only effect metadata
- documents Phase 4 parity across boundary plan, contracts, planner, workspace, queue mapping, and read model

The execution handoff read model is read-only. It does not invoke queue jobs, call the handoff workspace, execute campaigns, issue Pay Codes, send feedback, write journals, call providers, persist state, or move money.

## Phase 5A Boundary

Phase 5A adds the Pay Code generation gateway boundary plan:

- document campaign-side generation handoff ownership
- preserve external ownership of voucher issuance and execution semantics
- define generation non-goals
- sequence the remaining Phase 5 generation slices

This is a planning slice only. It does not create generation request/result contracts, invoke gateway implementations, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 5B Boundary

Phase 5B adds portable-code generation request/result contracts:

- define a generation request from campaign execution and recipient context
- define a generation result envelope with status, generation ID, optional external reference, blockers, and metadata
- define a planning contract for later generation planning
- keep effect metadata non-generating by default

This is a contract slice only. It does not bind a planner, invoke gateway implementations, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, or move money.

## Phase 5C Boundary

Phase 5C adds a null portable-code generation gateway:

- binds the existing gateway contract to a safe package default
- returns stable generation IDs for execution/recipient pairs
- reports planned, not issued, generation state
- exposes no-side-effect metadata

The null gateway is a safe default and does not call x-change, voucher, providers, wallets, feedback, journal, HTTP clients, or money-moving infrastructure.

## Phase 5D Boundary

Phase 5D adds portable-code generation planning:

- validates generation planning keys
- blocks generation planning when recipient identity is incomplete
- creates deterministic generation IDs
- preserves generation metadata for later host gateway handoff
- keeps gateway invocation explicitly false

This is a planning action only. It does not invoke the gateway, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, persist state, or move money.

## Phase 5E Boundary

Phase 5E adds a repository-backed portable-code generation workspace:

- reads existing campaign planning state through `CampaignPlanRepository`
- selects an existing campaign execution plan by execution ID
- selects recipients from the execution audience plan
- delegates per-recipient planning to `PlansCampaignPortableCodeGenerations`
- fails closed for unknown planning keys or execution IDs
- blocks generation planning for audiences without planned recipients
- exposes workspace-level metadata for future host/Cockpit consumers

This workspace is a composition seam over existing planning data. It does not mutate repository state, invoke the gateway, issue Pay Codes, send feedback, write journals, call providers, mutate wallets, persist new state, or move money.

## Phase 5F Boundary

Phase 5F adds a portable-code generation read model and parity closure:

- summarizes repository-backed portable-code generation workspace results
- exposes planned, blocked, and recipient counts
- preserves blockers for operator-facing review
- carries no-side-effect metadata into a read-only summary DTO
- documents Phase 5 parity across boundary plan, contracts, null gateway, planner, workspace, and read model

The portable-code generation read model is read-only. It does not invoke the workspace, invoke the gateway, issue Pay Codes, send feedback, write journals, call providers, persist state, mutate wallets, or move money.

## Phase 6A Boundary

Phase 6A adds the delivery / feedback handoff boundary plan:

- document campaign-side delivery handoff ownership
- preserve external ownership of notification transport and feedback lifecycle truth
- define delivery handoff non-goals
- sequence the remaining Phase 6 delivery handoff slices

This is a planning slice only. It does not create delivery handoff request/result contracts, call x-feedback, send notifications, call providers, write journals, issue Pay Codes, mutate wallets, or move money.

## Phase 6B Boundary

Phase 6B adds delivery handoff contracts and DTOs:

- define a delivery handoff DTO from execution, recipient, generation, and channel context
- define a delivery handoff result envelope
- define a planning contract for later delivery handoff planning
- keep effect metadata non-delivering by default

This is a contract slice only. It does not bind a planner, call x-feedback, send notifications, call providers, write journals, issue Pay Codes, mutate wallets, or move money.

## Phase 6C Boundary

Phase 6C adds in-memory delivery handoff planning:

- validates delivery handoff planning keys
- requires planned portable-code generation before delivery handoff readiness
- requires a portable-code reference before delivery handoff readiness
- checks recipient contact availability for the selected channel
- creates deterministic delivery handoff IDs
- preserves operator-safe handoff metadata

This is still a handoff planning slice only. It does not call x-feedback, send notifications, call providers, write journals, issue Pay Codes, persist state, mutate wallets, or move money.

## Phase 6D Boundary

Phase 6D adds a repository-backed delivery handoff workspace:

- reads existing campaign planning state through `CampaignPlanRepository`
- selects an existing campaign execution plan by execution ID
- composes portable-code generation workspace planning
- delegates per-recipient delivery handoff checks to `PlansCampaignDeliveryHandoffs`
- fails closed for unknown planning keys or execution IDs
- exposes workspace-level metadata for future host/Cockpit consumers

This workspace is a composition seam over existing planning data. It does not mutate repository state, call x-feedback, send notifications, call providers, write journals, issue Pay Codes, persist new state, mutate wallets, or move money.
