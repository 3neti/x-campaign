# Phase 14 Campaign Public API Boundary

Phase 14 defines package-side public API descriptors for `x-campaign`.

The package may describe host-owned API routes and controllers, expected methods, endpoint names, response presenters, authorization requirements, redaction requirements, idempotency expectations, and request validation ownership. It may expose DTOs and read models that help a host application wire public APIs safely.

It must not register the public API.

## Scope

- public API boundary documentation
- public API endpoint descriptor contracts
- in-memory public API descriptor building
- repository-backed public API workspace composition
- public API response presentation
- architecture parity checks

## Non-Goals

- No route registration
- No controller registration
- No request validation ownership
- No middleware or policy ownership
- No API resource ownership
- No OpenAPI publishing
- No host authentication implementation
- No host authorization implementation
- No redaction policy enforcement
- No queue workers
- No migrations
- No provider calls
- No Pay Code issuance
- No feedback delivery
- No journal writes
- No wallet mutation
- No money movement

## Slice Sequence

- Phase 14A — Public API Boundary Plan
- Phase 14B — Public API Descriptor Contract Baseline
- Phase 14C — In-Memory Public API Descriptor Builder
- Phase 14D — Repository-Backed Public API Workspace Baseline
- Phase 14E — Public API Response Presenter Baseline
- Phase 14F — Public API Parity

## Ownership

`x-campaign` owns package-side API descriptions and safe response DTOs.

Host applications own:

- route registration
- controllers
- form requests / validation
- API resources
- middleware
- policies
- authentication
- authorization
- redaction
- API versioning
- rate limiting

## Boundary Rule

Phase 14 may answer:

```text
What public API surface can a host expose for x-campaign, and what does the host still own?
```

Phase 14 must not perform:

```text
Register public API endpoints.
```
