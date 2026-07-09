# Phase 13 Campaign Host Integration Boundary

Phase 13 defines how host applications can integrate `x-campaign` safely through host-safe integration manifests and read-only handoff envelopes.

The package may describe which package-side workspaces, presenters, read models, and capability seams are available to a host. It may also expose DTOs and builders that help a host application register its own routes, controllers, authorization, redaction, and operational workflows.

Host integration is expressed as package-side descriptors for host-owned routes and controllers.

It must not become the host application.

## Scope

- host integration boundary documentation
- host integration manifest contracts
- in-memory manifest building
- repository-backed host integration workspace composition
- host response presentation
- architecture parity checks

## Non-Goals

- No route registration
- No controller registration
- No middleware or policy ownership
- No host authentication implementation
- No host authorization implementation
- No redaction policy enforcement
- No deployment automation
- No environment writes
- No queue workers
- No migrations
- No provider calls
- No Pay Code issuance
- No feedback delivery
- No journal writes
- No wallet mutation
- No money movement

## Slice Sequence

- Phase 13A — Host Integration Boundary Plan
- Phase 13B — Host Integration Manifest Contract Baseline
- Phase 13C — In-Memory Host Integration Manifest Builder
- Phase 13D — Repository-Backed Host Integration Workspace Baseline
- Phase 13E — Host Integration Response Presenter Baseline
- Phase 13F — Host Integration Parity

## Ownership

`x-campaign` owns package-side integration descriptions and safe handoff DTOs.

Host applications own:

- routes
- controllers
- middleware
- policies
- authentication
- authorization
- redaction
- public API versioning
- request validation
- deployment and worker operations

## Boundary Rule

Phase 13 may answer:

```text
What x-campaign capabilities can this host expose safely, and what does the host still own?
```

Phase 13 must not perform:

```text
Register or execute the host integration.
```
