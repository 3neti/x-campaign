# Phase 2 Durable Storage Boundary

## Mission

Phase 2 introduces durable campaign storage carefully, without changing campaign behavior or crossing into queues, Pay Code generation, delivery, journal writes, provider integrations, or money movement.

The existing Phase 1 in-memory repository remains the behavior baseline. Durable storage must preserve that baseline behind package contracts.

## Storage Ownership

`x-campaign` owns durable campaign planning records for:

- campaign planning state
- campaign audience planning state
- campaign recipient planning state
- campaign import planning state
- campaign import row review state

`x-campaign` does not own:

- Pay Code generation
- voucher issuance
- settlement execution
- feedback delivery
- journal/audit truth
- wallet or money movement

## Proposed Tables

The durable storage baseline should start with these tables:

| Table | Purpose |
| --- | --- |
| `campaign_plans` | Root campaign planning aggregate and current status. |
| `campaign_audiences` | Audience planning records linked to a campaign plan. |
| `campaign_recipients` | Recipient planning records linked to a campaign audience. |
| `campaign_imports` | Audience import planning/review records. |
| `campaign_import_rows` | Normalized recipient import row review state. |

## Identifier Policy

Durable records must preserve existing caller-facing identifiers:

- planning key
- campaign id
- audience id
- recipient id
- import id
- import row id

Generated database primary keys are storage details and must not replace portable identifiers in DTOs.

## JSON Boundary

The first durable storage baseline may use JSON columns for flexible metadata, source payloads, effects, blockers, and derived read-model context.

JSON columns must not become hidden lifecycle truth. Lifecycle truth remains explicit in status and identifier columns.

## Explicit Non-Goals

- No queues
- No Pay Code generation
- No delivery
- No journal writes
- No provider calls
- No wallet mutation
- No money movement
- No Cockpit routes
- No file parsing

## Phase 2 Slice Sequence

1. Phase 2B — persistence contract and DTO baseline.
2. Phase 2C — migration readiness review and schema invariant tests.
3. Phase 2D — database migration baseline.
4. Phase 2E — Eloquent repository baseline.
5. Phase 2F — persistence integration parity tests.

## Acceptance Rules

Each Phase 2 slice must:

- keep the existing public planning DTO behavior intact
- preserve in-memory repository behavior unless explicitly replacing a binding in a tested slice
- add tests before implementation
- maintain architecture boundary tests
- commit as a coherent checkpoint
