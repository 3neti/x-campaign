# Phase 5 Pay Code Generation Gateway Boundary

## Purpose

Phase 5 introduces the campaign-side boundary for Pay Code generation.

The campaign package may plan and request portable-code generation for campaign recipients, but it must not own voucher issuance semantics, x-change product behavior, wallet movement, provider calls, delivery, or audit truth.

## Ownership

`x-campaign` owns:

- campaign execution-to-recipient planning context
- recipient grouping for generation requests
- request/result envelopes for host gateway handoff
- read-only generation summaries for operators

External packages own:

- actual Pay Code / voucher issuance
- voucher templates and execution semantics
- wallet and settlement behavior
- provider calls
- delivery and feedback
- journal/audit persistence

## Explicit Non-Goals

- No direct voucher package dependency
- No x-change concrete dependency
- No wallet mutation
- No provider calls
- No notification delivery
- No journal writes
- No controller or route surface
- No money movement

## Phase 5 Slice Sequence

- Phase 5A — Pay Code Generation Gateway Boundary Plan
- Phase 5B — Portable Code Generation Request/Result Contract Baseline
- Phase 5C — Null Portable Code Generation Gateway Baseline
- Phase 5D — Portable Code Generation Planning Baseline
- Phase 5E — Repository-Backed Portable Code Generation Workspace Baseline
- Phase 5F — Portable Code Generation Read Model and Parity

## Boundary Rule

Phase 5 may describe and prepare generation handoff.

Phase 5 must not perform real issuance.
