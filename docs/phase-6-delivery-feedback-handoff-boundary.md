# Phase 6 Delivery / Feedback Handoff Boundary

## Purpose

Phase 6 introduces the campaign-side boundary for delivery and feedback handoff.

The campaign package may describe which generated portable-code plans should be communicated to recipients, but it must not own notification transport, provider delivery, lifecycle truth, audit truth, Pay Code issuance, wallet mutation, or money movement.

## Ownership

`x-campaign` owns:

- campaign execution and audience context
- recipient delivery planning context
- delivery handoff request/result envelopes
- readiness checks for campaign-side delivery planning
- read-only delivery handoff summaries for operators

External packages own:

- actual notification delivery
- channel drivers and provider adapters
- template rendering and provider credentials
- feedback receipts and callbacks
- immutable audit storage
- Pay Code generation and voucher issuance
- settlement and money movement

## Explicit Non-Goals

- No direct x-feedback package dependency
- No notification delivery
- No provider calls
- No journal writes
- No Pay Code issuance
- No voucher package dependency
- No x-change concrete dependency
- No wallet mutation
- No controller or route surface
- No money movement

## Boundary Rules

Delivery handoff must be explicit, inspectable, and side-effect free.

Handoff outputs must preserve planning keys, execution identifiers, recipient identifiers, generated portable-code references when present, channel intent, and correlation metadata.

Unknown or incomplete handoff requests must fail closed before queued work, notification delivery, provider calls, audit writes, issuance, wallet mutation, or money effects.

## Phase 6 Slice Sequence

### Phase 6A — Delivery / Feedback Handoff Boundary Plan

Document the delivery handoff boundary before delivery handoff contracts are introduced.

### Phase 6B — Delivery Handoff Request/Result Contract Baseline

Introduce delivery handoff DTOs and a planning contract without implementation side effects.

### Phase 6C — In-Memory Delivery Handoff Planning Baseline

Add a side-effect-free planner that turns portable-code generation summaries and recipient context into delivery handoff results.

### Phase 6D — Repository-Backed Delivery Handoff Workspace Baseline

Compose delivery handoff planning with stored campaign plans and portable-code generation planning while preserving fail-closed behavior for missing campaign, execution, or recipient state.

### Phase 6E — Queued Payload to Delivery Handoff Mapping Baseline

Map queued campaign payloads into delivery handoff requests without executing delivery.

### Phase 6F — Delivery Handoff Read Model and Parity

Expose read-only delivery handoff summaries and prove Phase 6 remains a handoff layer, not a notification transport layer.

## Phase 7 Boundary

Phase 7 may begin engagement, claim, or analytics visibility only after Phase 6 delivery handoff behavior is explicit, tested, and committed.

