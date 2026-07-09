# Phase 15 Campaign Host Adoption Boundary

Phase 15 defines how a host application, initially `x-change`, can adopt the package-side `x-campaign` seams safely.

This phase is not host implementation. It is the package-side integration contract finalization layer.

## Ownership Boundary

`x-change owns host route registration`.

`x-change owns controller execution`.

`x-change owns authorization and redaction`.

`x-change owns request validation, API resources, middleware, policies, rate limiting, and operator identity`.

`x-campaign describes safe integration seams`.

`x-campaign does not register host routes, create host controllers, enforce host policies, mutate money, generate Pay Codes directly, deliver feedback directly, or write journal records directly`.

## Phase 15 Slices

- Phase 15A — Host Adoption Boundary Plan
- Phase 15B — x-change Integration Manifest Contract
- Phase 15C — Cockpit Consumption Map
- Phase 15D — Public API Endpoint Recommendation Matrix
- Phase 15E — Host Mutation Authorization Checklist
- Phase 15F — Host Adoption Parity Report

## Safe Integration Surfaces

Phase 15 may describe:

- available workspaces
- read-only cockpit consumption
- public API endpoint candidates
- mutation readiness gates
- host authorization requirements
- redaction requirements
- external package responsibilities
- known unsupported host behaviors

## Explicit Non-Goals

Phase 15 must not implement:

- package-owned routes
- package-owned controllers
- package-owned form requests
- package-owned API resources
- package-owned middleware
- package-owned policies
- Pay Code generation execution
- feedback delivery execution
- journal writing execution
- provider calls
- queue dispatch side effects
- durable campaign mutation beyond already-approved in-memory seams
- wallet mutation
- money movement

## Completion Criteria

Phase 15 is complete when `x-campaign` can hand `x-change` a coherent host adoption map with:

- package surfaces
- host responsibilities
- endpoint recommendations
- cockpit consumption recommendations
- mutation authorization gates
- a parity report that maps functional specifications to as-built classes
