# Campaign management compass

## North star

Campaign management belongs to x-campaign; financial execution and Cockpit
integration stay in their existing owners. Existing links and money rules survive.

## Current position

2026-09-18: model/availability, management reads, creation, public/slug lookup and
successful-start persistence are extracted locally. The three baseline
officer-authorization failures are fixed. Gates 1–2 remain pending final parity
and release-boundary review.
Release and local browser verification authorized on 2026-09-18. Published
x-campaign v1.1.0 (`3d63087`) and x-change v1.0.25 (`fd54ed6a`), with consumer
minimum `^1.1` and synchronized lockfile. Local sandbox upgraded to both releases.
No Cloud deployment in this gate. Browser run found a demo-driver redirect issue
described below; do not report the full intake-to-payment journey as passed.
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

### Corrective local gate — payment action owns success navigation

- Package source change is in x-change `resources/js/pages/x-change/claim/Success.vue`,
  not the host or x-rider configuration. A usable canonical
  `x-change.claim-success.continue-to-payment` action suppresses both countdown
  and Rider runtime redirects. Existing paired-payment suppression remains.
  Rider messages and the explicit payment action remain visible.
- Unrelated, disabled or unusable actions do not suppress normal redirects;
  removing the payment action restores normal redirect rendering. No financial
  rules, issuance, collection state, provider access or endpoint data changed.
- Frontend: 82 passed across eight success/redirect/payment suites. Backend:
  67 passed / 616 assertions across claim-success, payment-handoff and Leads.
  Composer validation, asset doctor, production build and diff check passed.
- Browser verified the existing accepted application `AUI-PT9T` with the temporary
  local Composer path integration. `x-rider.driver` remained `demo`. The success
  page stayed in place beyond the eight-second demo timeout, retaining the
  application message and “Continue to payment”. Clicking the actual CTA opened
  `/x/pay/AUI-PT9T` with PHP 100.00 due and PHP 0.00 collected.
- No new voucher, payment, SMS, or QR attempt was created in this corrective gate.
  This retests the previously failing success-to-payment segment, not a fresh
  paid lifecycle. The earlier misleading introduction copy remains a separate
  polish item. No tag, push or Cloud deployment in this corrective gate.
- Temporary integration is restored to the released v1.0.25 sandbox build after
  acceptance; the correction requires its own reviewed release to persist there.

### Released dependency and local browser gate — 2026-09-18

- Repeated x-campaign suite: 468 passed / 4,594 assertions.
- Installed released v1.1.0 normally in x-change, without the temporary overlay:
  111 consumer tests passed / 1,016 assertions. Composer strict checks passed.
- Both tags and main commits pushed to their respective 3neti repositories.
- Sandbox: Composer updated only x-campaign and x-change; generated build inputs
  matched package source, production Vite build passed. No broad installation,
  commissioning, migrations, or Cloud operation was run.
- Additional host smoke tests: 4 passed, 1 failed (`DashboardTest` authenticated
  case: no `users` table in its in-memory database). `tests/Pest.php` has
  `RefreshDatabase` commented out. Not fixed here; no old-release reproduction
  was run, so this is not presented as a proven release-independent baseline.
- In-app browser created a fresh endpoint and settlement Pay Code `AUI-PT9T`.
  Persisted endpoint usage is exactly 1. Application/mobile/reference evidence
  was captured; no beneficiary bank account was requested. Synthetic application
  details were used. The scenario did not request OTP.
- **Handoff failed:** local `x-rider.driver=demo` loads `demo-redirect` from
  `config/x-rider-drivers/demo.yaml`, pointing at `https://example.com/success`
  after eight seconds. Logs record `rider.redirect.started` for this code after
  `accepted_success`. Browser safety blocked that unrelated external destination.
  No security bypass or driver/configuration change was made.
- Direct diagnostic navigation to the same code's payment page succeeded;
  generated a visible PHP 100.00 QR Ph. Attempt
  `01M2SX8BFK42H1NQRKFJRBW95V` is `awaiting_payment`, `settled_at=null`,
  expected amount 10,000 minor units. No payment or SMS was made by this run.
- Additional UX observation: introductory demo page says “Pay with Pay Code” /
  “Pay now”, before the intake form says “Submit Application”.
- Browser screenshot capture was unavailable (zero-width capture response);
  accessibility snapshots, application logs and read-only database evidence
  support the observations. Payment tab retained for inspection.
- [Local campaign endpoint](https://x-change-sandbox.test/x/o/lester-hurtado-manila/aui-on-demand-insurance-payment-tegdfv)
  mints another Pay Code when opened; do not reopen simply to inspect this run.
- [Existing payment page](https://x-change-sandbox.test/x/pay/AUI-PT9T?attempt=01M2SX8BFK42H1NQRKFJRBW95V)
  is session-bound and its QR expires. It is not evidence of payment settlement.

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

Release dependency ordering and installed-package regression verification are
complete. The redirect correction is implemented and browser-verified locally.
Next, review/release the x-change correction, adopt it normally in the sandbox,
and repeat a fresh AUI lifecycle. Keep this separate from new
pause/budget/participation behavior and preserve the paired-display boundary.

## 2026-09-19 endpoint management first UI slice

- Implemented the first management-dashboard slice after the user confirmed the
  richer endpoint list was not yet manifested in testing.
- x-campaign read model now exposes `created_at` and `updated_at` for endpoint
  rows without adding database reads or changing persistence.
- x-change Cockpit endpoint rows now render the full public URL, creator display
  metadata, derived availability state, exposure, started/completed/in-progress
  progress, Show QR & Share, Copy Link, and reversible Pause/Resume controls.
- Pause/Resume is intentionally scoped to new starts only: it changes endpoint
  status between `active` and `paused`, records an audit event, and does not
  cancel existing Pay Codes or mutate issued vouchers.
- Progress is derived from durable starts and currently available display-session
  evidence. The fuller participation ledger and complete direct-start completion
  attribution remain a later extraction gate.
- Focused verification:
  - x-campaign `tests/Feature/EndpointCampaignSummaryTest.php`: 2 passed, 25 assertions.
  - x-change `tests/Feature/Cockpit/CockpitCampaignWorksheetTest.php`
    filtered endpoint coverage: 3 passed, 50 assertions.
  - x-change `tests/frontend/cockpit/CockpitCampaignWorksheet.test.ts`:
    1 file passed, 22 tests.
- Formatting: x-campaign Pint passed; x-change PHP formatted with the host Pint
  binary because the x-change package checkout has no local Pint binary.

## Update discipline

Update this file after each meaningful slice and test/release outcome. Record
failures and remaining work honestly. Keep the historical package compass intact
and link this active workstream from it. Do not record secrets or customer data.
