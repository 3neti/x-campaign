# Phase 10 Campaign Cockpit / Operator API Integration Boundary

Phase 10 introduces the package-side integration surface for campaign Cockpit and operator API consumers.

This phase is intentionally read-only. It prepares data contracts, read models, workspace composition, and response envelopes that a host application can expose later through real routes and controllers.

## Scope

- read-only Cockpit summaries
- operator API response envelopes
- campaign dashboard cards
- campaign detail panels
- execution, generation, delivery, claim, analytics, report, and export posture
- repository-backed composition over existing package workspaces
- safe metadata for host authorization and redaction layers

## Explicit Non-Goals

- No routes
- No controllers
- No Inertia pages
- No Vue components
- No campaign mutation
- No recipient import mutation
- No queue dispatch
- No Pay Code issuance
- No feedback delivery
- No journal writes
- No provider calls
- No file generation
- No file storage
- No wallet mutation
- No money movement

## Ownership Boundary

`x-campaign` may describe campaign state for Cockpit and operator API consumers.

It must not become the Cockpit UI, HTTP API, authorization layer, feedback transport, journal sink, Pay Code issuer, voucher runtime, or settlement engine.

## Phase 10 Slice Sequence

### Phase 10A — Cockpit / Operator Integration Boundary Plan

Document the integration boundary and prove no concrete routes, controllers, pages, or transports exist.

### Phase 10B — Cockpit Summary Contract Baseline

Define Cockpit summary request/result DTOs and a read model contract.

### Phase 10C — In-Memory Cockpit Summary Builder Baseline

Compose existing summaries into an operator-safe Cockpit summary without persistence or HTTP behavior.

### Phase 10D — Repository-Backed Cockpit Workspace Baseline

Compose existing repository-backed workspaces into Cockpit summaries without mutation.

### Phase 10E — Operator API Response Presenter Baseline

Project Cockpit summaries into host-safe API response envelopes without registering routes.

### Phase 10F — Cockpit / Operator Integration Parity

Verify Phase 10 remains a read-only package integration surface and does not introduce Cockpit UI, HTTP routes, controllers, mutation, delivery, journal, or execution behavior.
