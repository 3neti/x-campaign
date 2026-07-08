# Phase 3 Queue Boundary

## Mission

Phase 3 introduces queue architecture for campaign planning and orchestration handoff without executing campaign work.

The goal is to define how campaign work becomes queue-ready while preserving the current package boundary:

- campaign prepares work
- host applications own queue runtime configuration
- future integrations own issuance, delivery, journal, provider, and settlement behavior

## Queue Ownership

`x-campaign` may own queue-safe DTOs, planning contracts, dispatch intent objects, and package job wrappers for campaign orchestration.

Host applications remain responsible for:

- queue connection selection
- worker deployment
- retry and failure infrastructure
- monitoring infrastructure
- production scheduling policy

The queue layer must expose explicit effect metadata so consumers can distinguish:

- planned queue work
- actually queued work
- completed campaign work

## Explicit Non-Goals

Phase 3 does not introduce business execution.

Non-goals:

- No Pay Code generation
- No delivery
- No journal writes
- No provider callbacks
- No reconciliation
- No wallet mutation
- No money movement
- No campaign lifecycle truth beyond queue handoff metadata

## Boundary Rules

Queue payloads must be serializable.

Queue payloads must carry correlation metadata.

Queue dispatch must be explicit and testable with Laravel queue fakes.

Queue jobs must not issue portable contracts, send messages, write audit logs, call providers, mutate wallets, or move money.

## Phase 3 Slice Sequence

### Phase 3A — Campaign Queue Boundary Plan

Document the queue boundary and prove no queue job classes or dispatch behavior exists yet.

### Phase 3B — Queue Dispatch Contract Baseline

Introduce queue dispatch DTOs and a planning contract without real queue dispatch.

### Phase 3C — In-Memory Queue Dispatch Planning Baseline

Add a side-effect-free queue dispatch planner that returns planned dispatch metadata.

### Phase 3D — Queued Plan Payload Baseline

Add serializable queued plan payload DTOs and validation rules.

### Phase 3E — Queue Job Wrapper Baseline

Add a package job wrapper that can carry a queued plan payload without executing issuance, delivery, journal, provider, wallet, or money behavior.

### Phase 3F — Queue Dispatch Integration Parity

Add the queue dispatch integration seam and prove queued handoff behavior is explicit, fakeable, and side-effect constrained.

## Phase 4 Boundary

Phase 4 may begin campaign execution integration only after Phase 3 queue handoff is explicit, tested, and committed.
