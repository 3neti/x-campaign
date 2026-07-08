# Phase 9 Operator Report / Export Handoff Boundary

## Purpose

Phase 9 introduces operator report and export handoff infrastructure.

The campaign package may describe operator-safe report requests and export handoff requests over existing analytics summaries. It must not become a PDF renderer, spreadsheet generator, file store, delivery provider, audit system, lifecycle truth source, or money movement layer.

## Ownership

`x-campaign` owns:

- operator-safe report request DTOs
- report builder contracts over existing campaign analytics summaries
- export handoff request DTOs
- export planning metadata
- queued payload mapping into report/export requests

External packages or hosts own:

- concrete PDF generation
- spreadsheet generation
- object storage
- report delivery
- notification transport
- audit trails
- lifecycle truth
- settlement and money movement

## Explicit Non-Goals

- No PDF generation
- No spreadsheet generation
- No CSV file generation
- No file storage
- No report delivery
- No notification sending
- No journal writes
- No lifecycle truth ownership
- No claim lifecycle mutation
- No provider calls
- No route or controller surface
- No durable report persistence
- No wallet mutation
- No money movement

## Boundary Rules

Report/export handoff must be explicit, inspectable, and side-effect free.

Report outputs must preserve planning keys, execution identifiers, analytics counts, blockers, requested format, and no-side-effect metadata.

Unknown or incomplete report/export requests must fail closed before file generation, storage, delivery, lifecycle mutation, provider calls, audit writes, notification sends, wallet mutation, or money effects.

## Phase 9 Slice Sequence

### Phase 9A — Operator Report / Export Handoff Boundary Plan

Document the operator report/export handoff boundary before report contracts or export handoff contracts are introduced.

### Phase 9B — Operator Report Contract Baseline

Introduce operator report request/result DTOs and a report builder contract without rendering, storage, delivery, or persistence.

### Phase 9C — In-Memory Operator Report Builder Baseline

Add a side-effect-free report builder over existing analytics operator summaries.

### Phase 9D — Export Handoff Contract Baseline

Introduce export handoff request/result DTOs and a handoff planner contract without creating files or calling storage.

### Phase 9E — Queued Payload to Export Handoff Mapping Baseline

Map queued campaign payloads into export handoff requests without running exports.

### Phase 9F — Operator Report / Export Parity

Expose read-only report/export operator summaries and prove Phase 9 remains a handoff layer, not a rendering, storage, delivery, or lifecycle layer.
