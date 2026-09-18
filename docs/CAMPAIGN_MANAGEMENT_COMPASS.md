# Campaign management compass

## North star

Campaign management belongs to x-campaign; financial execution and Cockpit
integration stay in their existing owners. Existing links and money rules survive.

## Current position

2026-09-18: model/availability, management reads, creation, public/slug lookup and
successful-start persistence are extracted locally. The three baseline
officer-authorization failures are fixed. Gates 1–2 remain pending final parity
and release-boundary review.
Release and local browser verification authorized on 2026-09-18: publish
x-campaign v1.1.0 first, require it in x-change, then verify the installed consumer
before publishing x-change v1.0.25. No Cloud deployment in this gate.
Full scope: [plan](CAMPAIGN_MANAGEMENT_PLAN.md).

## First controlled slice

Extract the existing endpoint model's package-neutral fields/casts/owner relation
and availability policy. Retain x-change's `LeadCampaign` compatibility class and
template relation. Retain the original table and migration in x-change for now:
its template foreign key is an x-change integration concern.

Do not change start-count timing, messages, status vocabulary, expiry semantics,
daily-window inclusivity, or issuance. This is not full Gate 2 completion.

## Second controlled slice — baseline fix and management reads

- Root cause of the three baseline failures: `campaignOfficerAuthorizationUser()`
  created bare test users without the platform wallet required by the real
  approval issuance action. The fake user has a single-wallet relation, not the
  production multi-wallet provisioning methods. Added the existing
  `fundTestUserWallet($user, 0)` helper; neither issuer nor officer receives funds.
  Explicit zero-balance assertions protect this fixture. No production wallet
  resolution, authorization, pricing or money guard was weakened.
- Added `EndpointCampaignRepository` with an Eloquent implementation for owner
  scoped management reads. Preserved both owner type and owner ID filtering,
  `updated_at DESC`, the 25-row bound and inclusion of paused records.
- Added `EndpointCampaignSummary`: package-owned row fields and defaults only;
  no database queries, URLs, QR rendering, templates or execution dependencies.
- Contextual x-change binding injects the existing `LeadCampaign` prototype so
  Eloquent hydration, retrieved events and morph identity do not change.
- Cockpit delegates query and summary construction; bulk template loading,
  template projection, route generation and QR generation stay in x-change.
- At the end of this second slice, creation, slug lookup, public endpoint lookup
  and post-issuance counter writes remained in x-change (addressed below).

## Third controlled slice — creation, lookup and successful starts

- Extended the package repository with creation, exact merchant/endpoint lookup,
  slug collision queries and successful-start recording. x-change delegates its
  existing persistence calls through contextual bindings using `LeadCampaign`.
- Template ownership/validation, merchant profile resolution, slug formatting,
  generation payload, Pay Code execution and public redirects stay in x-change.
- Creation still uses Eloquent's creating/created events and ULID reference.
  Public lookup still runs before endpoint validation handling, including paused
  records; missing merchant/endpoint pairs retain 404 behavior.
- Start recording keeps the database-side `usage_count + 1`, timestamps, caller
  transaction and original post-issuance ordering. It does not dispatch model
  update events or mutate the passed model's in-memory count.
- Paired-display transaction/row locks and its scoped campaign reads remain in
  x-change unchanged. This slice does not introduce atomic limit enforcement,
  retries, a new transaction boundary, or recovery for post-issuance write errors.
  Those existing concurrency/error windows are not claimed fixed by extraction.
- Added package tests for creation/subclass events, exact lookup, owner type/ID
  collision scoping, stale-instance increments and rollback. Consumer tests add
  missing-endpoint and failed-issuance no-increment assertions.

## Evidence

### Latest verification (third slice)

- x-campaign full suite: **468 passed, 4,594 assertions**.
- x-change combined Leads, Cockpit worksheet, officer authorization, direct
  transfer fulfillment and payout recovery: **111 passed, 1,016 assertions**.
  Includes the new failed-issuance/missing-endpoint tests and paired-display
  integration coverage. This is a scoped consumer suite, not all x-change tests.
- Pint, Composer strict validation and `git diff --check` passed in both packages.
- Consumer suite still uses the temporary sibling-source autoload overlay below;
  installed-release verification is a separate prerequisite, not yet satisfied.

### Previous verification (second slice)

- Officer authorization: 3 passed, 17 assertions after fixture correction; the
  combined run below also includes the additional zero-balance assertions.
- x-campaign full suite: 464 passed, 4,568 assertions.
- x-change combined Leads, Cockpit worksheet, officer authorization, direct
  transfer fulfillment and payout recovery: 108 passed, 1,000 assertions.
- Endpoint file after adding the list-query regression: 12 passed, 75 assertions.
  This overlaps the combined run; do not add these counts together.
- List-query regression confirms one campaign query and two template queries
  (template picker plus bulk endpoint-template load) for three distinct templates,
  with no campaign record changes.
- New package tests cover exact owner scoping (including same ID/different type),
  26-to-25 truncation/order, subclass retrieved events, read-only summaries,
  legacy/custom/null defaults and timestamp formats.
- Formatting, Composer strict validation and whitespace checks passed.

### Historical first-slice evidence

- Before extraction: x-change `tests/Feature/Leads` — 41 passed, 473 assertions.
- x-campaign full suite after extraction/formatting — 460 passed, 4,537 assertions.
- New availability/model coverage — 17 passed, 38 assertions (included above).
- x-change Leads + direct-transfer fulfillment + payout recovery + officer
  authorization — 59 passed, 3 failed, 585 assertions. All three failures are in
  `CampaignOfficerAuthorizationExecutionTest`: `PayCodeWalletNotResolved` from
  `WalletAccessService.php:42` through `IssueCampaignWorksheetApprovalPayCode`.
- Repeated those three tests with original pre-extraction classes: same three
  failures, zero assertions. Confirmed baseline issue; no wallet repair attempted.
- Expanded endpoint compatibility plus Cockpit worksheet tests — 53 passed,
  440 assertions. Includes persisted reference, morph identity, owner/template
  relationships, URL stability, and rejection without issuance/count mutation.
- Composer strict validation and whitespace checks passed in both packages.
- Formatting passed. x-change has no Pint binary; used x-campaign's installed Pint.
- Package test generator is unsupported by this Testbench workbench preset;
  added the regression file following existing package conventions instead.
- First sandboxed test attempt failed on test log/cache permissions; rerun with
  package test-write permission established the green baseline above.

### Integration method and release prerequisite

x-change tests used a temporary `/tmp/campaign-extraction-bootstrap.php` Composer
autoload overlay to load sibling x-campaign source. No vendor edits, host Composer
changes, or persistent autoload overrides were made. Ordinary installed x-campaign
v1.0.0 does not contain the new classes. Before releasing the x-change consumer,
publish the upstream additive release, raise x-change's minimum x-campaign
constraint to that release, update its lock normally, and rerun without the overlay.
Do not publish x-change alone with the current broad `^1.0` minimum.

### Changed source

- x-campaign: `Models/EndpointCampaign`, `Services/EndpointCampaignAvailability`,
  `tests/Feature/EndpointCampaignAvailabilityTest.php`, plan/compass and README pointers.
- x-change: `Models/LeadCampaign` compatibility subclass,
  `Actions/Leads/StartLeadCampaign` policy delegation, endpoint integration tests.
- Second slice x-campaign: `Contracts/EndpointCampaignRepository`,
  `Repositories/EloquentEndpointCampaignRepository`, `ReadModels/EndpointCampaignSummary`,
  `XCampaignServiceProvider`, repository/summary tests.
- Second slice x-change: `CockpitCampaignWorksheetController`, contextual binding
  in `XChangeServiceProvider`, officer fixture and endpoint list-query regression.
- The migration, public route, generation payload, start-count timing, template
  relation and paired-display lock remain unchanged. No browser check or frontend
  build was needed for this server-only slice; host/browser acceptance remains Gate 6.
- No commit, push, tag, deployment, financial operation or campaign data mutation.

## Next gate

Review full Gate 1–2 parity and the upstream release minimum, including the
intentionally retained paired-display transaction boundary. Publish the additive
x-campaign dependency before updating/releasing the x-change consumer; verify
again without the development autoload overlay. Only then proceed to new
pause/budget/participation behavior. Current authorized release gate is in progress.

## Update discipline

Update this file after each meaningful slice and test/release outcome. Record
failures and remaining work honestly. Keep the historical package compass intact
and link this active workstream from it. Do not record secrets or customer data.
