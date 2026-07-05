# x-campaign Test Strategy

## Required Test Layers

- Unit tests for DTO construction, defaults, and serialization.
- Contract/binding tests for service provider seams.
- Architecture tests for package boundaries.
- Future integration tests for audience import, campaign execution, attribution, and analytics.

## Phase 0 Test Scope

Phase 0 protects:

- package bootability
- DTO baseline shape
- contract bindings
- no execution ownership
- no notification transport ownership
- no routes/controllers/jobs/migrations
- no dependency on x-change, x-feedback, x-journal, x-action, x-cockpit, or x-campaign consumers

## Boundary Testing

Architecture tests must prevent x-campaign from becoming:

- a voucher package
- an execution package
- a payment package
- a wallet package
- a notification provider package
- a claim lifecycle package

