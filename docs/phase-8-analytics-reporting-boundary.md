# Phase 8 Analytics / Reporting Aggregation Boundary

## Purpose

Phase 8 introduces campaign-side analytics and reporting aggregation.

The campaign package may aggregate campaign planning, portable-code generation, delivery handoff, and claim visibility summaries for operator review and reporting. It must not become the source of lifecycle truth, execute workflows, send notifications, mutate claims, write audit records, call providers, or move money.

## Ownership

`x-campaign` owns:

- campaign analytics snapshot DTOs
- aggregation over existing campaign read models
- operator-safe reporting summaries
- read-only analytics workspace composition
- queued payload mapping into analytics workspace requests

External packages own:

- Pay Code generation truth
- claim lifecycle truth
- notification delivery truth
- journal/audit truth
- provider callbacks and reconciliation
- settlement and money movement

## Explicit Non-Goals

- No lifecycle truth ownership
- No claim lifecycle mutation
- No notification delivery
- No journal writes
- No provider calls
- No route or controller surface
- No export/PDF/spreadsheet generation
- No durable analytics persistence
- No wallet mutation
- No money movement

## Boundary Rules

Analytics aggregation must be explicit, inspectable, and side-effect free.

Aggregated outputs must preserve planning keys, execution identifiers, source summary counts, blockers, and no-side-effect metadata.

Unknown or incomplete analytics requests must fail closed before queued work, lifecycle mutation, provider calls, audit writes, delivery, persistence, wallet mutation, or money effects.

## Phase 8 Slice Sequence

### Phase 8A — Analytics / Reporting Aggregation Boundary Plan

Document the analytics/reporting aggregation boundary before contracts are introduced.

### Phase 8B — Analytics Snapshot Contract Baseline

Introduce analytics input/result DTOs and an aggregation contract without implementation side effects.

### Phase 8C — In-Memory Analytics Snapshot Aggregation Baseline

Add a side-effect-free aggregator over existing read-model summaries.

### Phase 8D — Repository-Backed Analytics Workspace Baseline

Compose analytics aggregation with stored campaign plans and existing handoff/visibility workspaces.

### Phase 8E — Queued Payload to Analytics Mapping Baseline

Map queued campaign payloads into analytics workspace requests without running reports.

### Phase 8F — Analytics Read Model and Parity

Expose read-only analytics summaries and prove Phase 8 remains an aggregation layer, not a reporting transport or lifecycle layer.

## Phase 9 Boundary

Phase 9 may begin operator report/export handoff planning only after Phase 8 aggregation behavior is explicit, tested, and committed.

