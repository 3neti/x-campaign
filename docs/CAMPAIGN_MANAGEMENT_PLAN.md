# Campaign management extraction plan

## Objective

Make `3neti/x-campaign` own campaign management while x-change supplies the
Cockpit, Pay Code templates, and execution adapters. Preserve existing endpoint
URLs, QR codes, payroll workflows, persisted references, and claim/payment journeys.

Track execution and evidence in [the compass](CAMPAIGN_MANAGEMENT_COMPASS.md).
Update it after every implementation, verification, scope, or release decision.

## Ownership

| Concern | Owner |
| --- | --- |
| Campaign identity, owner, creator, schedule, limits, participation | x-campaign |
| Worksheet imports and approval records | x-campaign |
| Templates and instruction interpretation | x-change |
| Issuance, claim, settlement, recovery | Existing execution packages and x-change adapters |
| Cockpit, public routes, sharing stamp, paired displays | x-change |
| Durable audit recording | x-journal through integration |

Dependency direction is x-change → x-campaign, never circular. Usage labels
(Payroll, Ayuda, Lead, Collection, Custom) are not separate execution engines.
Batch and public-endpoint entry paths share management vocabulary, not a forced
single execution implementation. Stored lifecycle and derived availability differ.

## Gate 1 — Baseline and contracts

- Map ownership, dependencies, routes, persisted references and compatibility.
- Characterize generation, availability, attribution and start counts.
- Verify payroll approval/fulfillment, SMS/recovery and intake-to-payment coverage.
- Define completion: intake accepted for intake-only; confirmed payout for
  disbursement; confirmed payment for collection; both accepted intake and
  confirmed payment for intake-plus-collection. Policy issuance is separate.
- Exit: reproducible baseline and reviewed package contracts.

## Gate 2 — Behavior-preserving extraction

- Extract endpoint identity, persistence access, scheduling rules and management
  read models into x-campaign in reviewable sub-slices.
- Keep template ownership/interpretation, issuance, routes, redirects, Cockpit and
  seller/customer display pairing in x-change.
- Use small boundary contracts; reuse existing interfaces only where they fit.
- Preserve tables, IDs, references, slugs, attribution metadata and migration
  identities. Never rewrite deployed migrations or duplicate templates.
- Retain compatibility classes for consumers, morph identities and queued work.
- Exit: existing behavior passes through the extracted boundary unchanged.

## Gate 3 — Availability and limits hardening

- Account-authorized, journaled Pause/Resume; pausing blocks new starts only.
- Derive Scheduled, Open, Paused, Outside hours, Ended and Limit reached.
- Enforce start limits under concurrency and budget limits with defined failed
  issuance/reservation-release accounting.
- Stable start-operation retry identity prevents duplicate issuance.
- Campaign budgets never replace Treasury or issuance authorization.
- Exit: authorization, concurrency, retry, schedule and budget tests pass.

## Gate 4 — Participation and attribution

- Record creator independently of owner and link successful starts to Pay Codes.
- Consume authoritative intake/payout/payment outcomes idempotently.
- Record management actions through x-journal; do not duplicate PII or evidence.
- Preserve historical start counts; backfill only proven attribution. Missing
  creator/outcome evidence stays unknown.
- Exit: counts reconcile to underlying records and completion semantics.

## Gate 5 — Cockpit management

- Endpoint-first list with creator/date, availability and progress counts.
- Show QR & Share, Copy Link and authorized Pause/Resume.
- Pagination, filters, template/schedule/limit details, attributed Pay Codes and
  activity history. Reuse the existing stamp and enlarged endpoint QR.
- Exit: desktop/mobile and duplicate-name browser acceptance.

## Gate 6 — Release and verification

1. Focused and wider regressions in both packages.
2. Local sandbox and x-PayOut adoption; simulated-provider browser scenarios.
3. Publish x-campaign before the x-change consumer update, with release approval.
4. Deploy testing and verify existing links; adopt in x-PayOut after acceptance.
5. Live transfers, paid SMS and new financial scenarios require explicit approval.

## Boundaries and stopping point

No payroll execution redesign, accounting changes, claim/pay route merger,
merchant registry, or cleanroom reset. Review Gates 1–2 before adding behavior.
Do not present baseline bugs as fixed by extraction. Known research findings to
verify: ordinary-start limit race, stored budget cap enforcement gap, absent
creator/participation ledger, list capped at 25, and exposure amount-unit ambiguity.
