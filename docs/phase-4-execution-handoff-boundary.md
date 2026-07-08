# Phase 4 Execution Handoff Boundary

## Mission

Phase 4 makes campaign execution plans handoff-ready without performing issuance, delivery, audit recording, provider calls, wallet mutation, or money movement.

The package should be able to describe:

- which campaign execution is ready for handoff
- which audience and batches are in scope
- which queued payload or operator action requested the handoff
- which host-owned capability must later perform the actual work

## Handoff Ownership

`x-campaign` owns the campaign-side handoff grammar:

- handoff DTOs
- handoff result envelopes
- in-memory handoff planning
- repository-backed handoff workspace composition
- queued payload mapping into handoff requests
- read-only handoff summaries

Host packages and future integrations own:

- Pay Code generation
- voucher issuance
- delivery transport
- journal writes
- provider integrations
- wallet mutation
- settlement and money movement

## Explicit Non-Goals

Phase 4 does not execute campaign work.

Non-goals:

- No Pay Code generation
- No voucher issuance
- No delivery
- No journal writes
- No provider calls
- No wallet mutation
- No money movement
- No durable execution lifecycle truth beyond existing campaign planning state

## Boundary Rules

Execution handoff must be explicit and inspectable.

Handoff outputs must preserve correlation metadata.

Handoff status must be derived from existing campaign plans and execution plans.

Unknown or incomplete handoff requests must fail closed before any queued work, external call, issuance, delivery, audit write, or money effect.

## Phase 4 Slice Sequence

### Phase 4A — Campaign Execution Handoff Boundary Plan

Document the execution handoff boundary before handoff contracts are introduced.

### Phase 4B — Execution Handoff Contract Baseline

Introduce execution handoff DTOs and a planning contract without implementation side effects.

### Phase 4C — In-Memory Execution Handoff Planning Baseline

Add a side-effect-free planner that turns an existing campaign execution plan into a handoff result.

### Phase 4D — Repository-Backed Execution Handoff Workspace Baseline

Compose handoff planning with stored campaign plans while preserving fail-closed behavior for missing campaign, audience, or execution state.

### Phase 4E — Queued Payload to Execution Handoff Mapping Baseline

Map queued campaign payloads into handoff requests without executing the job's target operation.

### Phase 4F — Execution Handoff Read Model and Parity

Expose read-only handoff summaries and prove Phase 4 remains an execution handoff layer, not issuance or delivery.

## Phase 5 Boundary

Phase 5 may begin Pay Code generation gateway planning only after Phase 4 handoff behavior is explicit, tested, and committed.
