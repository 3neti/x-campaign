# Phase 7 Engagement / Claim Visibility Boundary

## Purpose

Phase 7 introduces campaign-side engagement and claim visibility.

The campaign package may describe whether recipients have a visible claim/engagement state for campaign operations, analytics, and operator review, but it must not own claim lifecycle truth, voucher redemption, settlement execution, notification transport, provider callbacks, audit truth, wallet mutation, or money movement.

## Ownership

`x-campaign` owns:

- campaign execution context
- recipient engagement visibility context
- portable-code reference visibility context
- claim visibility request/result envelopes
- readiness checks for campaign-side visibility planning
- read-only claim visibility summaries for operators

External packages own:

- claim submission and claim lifecycle mutation
- voucher redemption and execution semantics
- provider callbacks and reconciliation
- notification delivery and feedback receipts
- immutable audit storage
- settlement and money movement

## Explicit Non-Goals

- No direct x-change package dependency
- No direct voucher package dependency
- No voucher redemption
- No claim lifecycle mutation
- No provider calls
- No journal writes
- No notification delivery
- No route or controller surface
- No wallet mutation
- No money movement

## Boundary Rules

Engagement and claim visibility must be explicit, inspectable, and side-effect free.

Visibility outputs must preserve planning keys, execution identifiers, recipient identifiers, generated portable-code references, claim status snapshots, correlation metadata, and operator-safe blockers.

Unknown or incomplete visibility requests must fail closed before queued work, claim lifecycle mutation, redemption, provider calls, audit writes, delivery, wallet mutation, or money effects.

## Phase 7 Slice Sequence

### Phase 7A — Engagement / Claim Visibility Boundary Plan

Document the visibility boundary before claim visibility contracts are introduced.

### Phase 7B — Claim Visibility Request/Result Contract Baseline

Introduce claim visibility DTOs and a planning contract without implementation side effects.

### Phase 7C — In-Memory Claim Visibility Planning Baseline

Add a side-effect-free planner that turns portable-code generation context and claim status snapshots into claim visibility results.

### Phase 7D — Repository-Backed Claim Visibility Workspace Baseline

Compose claim visibility planning with stored campaign plans and portable-code generation planning while preserving fail-closed behavior for missing campaign, execution, or recipient state.

### Phase 7E — Queued Payload to Claim Visibility Mapping Baseline

Map queued campaign payloads into claim visibility workspace requests without querying claim systems or mutating lifecycle state.

### Phase 7F — Claim Visibility Read Model and Parity

Expose read-only claim visibility summaries and prove Phase 7 remains a visibility layer, not a claim runtime layer.

## Phase 8 Boundary

Phase 8 may begin analytics or reporting aggregation only after Phase 7 visibility behavior is explicit, tested, and committed.

