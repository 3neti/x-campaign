# x-campaign Compass

## Mission

Build `x-campaign` as the beneficiary distribution platform for the x-change Settlement Operating System.

## Current Slice

Wave 5 — Phase 0: Architecture Foundation.

## Status

Complete.

## Completed Work

- Created independent package skeleton at `/Users/rli/PhpstormProjects/packages/x-campaign`.
- Established Composer package `3neti/x-campaign`.
- Established namespace `LBHurtado\\XCampaign`.
- Added package service provider.
- Added Phase 0 contracts:
  - `CampaignChannelDriver`
  - `PayCodeGenerationGateway`
  - `CampaignClaimStatusProvider`
  - `CampaignFeatureProfileResolver`
- Added Phase 0 DTOs for campaign, audience, recipient, execution, batch, delivery, import, delivery result, and feature profile.
- Added model-name scaffolds without persistence behavior.
- Added architecture, domain model, and test strategy documents.
- Added Phase 0 Pest coverage for DTO defaults, service-provider binding, and architecture boundaries.

## Discoveries

- `/Users/rli/PhpstormProjects/packages/x-campaign` did not exist before Wave 5.
- Neighboring packages use `3neti/*` Composer names and `LBHurtado\\X*` namespaces.

## Risks

- Future phases must avoid duplicating x-change Program Blueprints, voucher templates, Pay Code generation, claim lifecycle, settlement, disbursement, and wallet behavior.
- Future delivery integration must avoid duplicating x-feedback transport ownership.
- Future audit/reporting integration must avoid duplicating x-journal audit ownership.

## Architectural Decisions

- `x-campaign` owns beneficiary distribution, not execution.
- Phase 0 contains no migrations, routes, controllers, jobs, provider clients, execution calls, notification sends, or money movement.
- `spatie/laravel-data` is used for DTO baselines to match neighboring packages.
- Package dependencies are kept minimal: Laravel support, Spatie Data, Pest, and Testbench only.

## Test Coverage Status

- `composer validate --strict` passed.
- `php -d memory_limit=1G vendor/bin/pest` passed: 6 tests, 82 assertions.
- `php -l` passed for modified PHP files under `src`, `tests`, and `config`.
- Initial non-elevated Pest run failed because sandbox permissions blocked Testbench/Pest cache writes under `vendor`; elevated rerun passed.

## Next Recommended Slice

Phase 1A — Campaign Core DTO and state grammar baseline, before adding persistence.

## Open Questions

- Whether persistence should be introduced in Phase 1 or kept behind repositories first.
- Which x-change contract should later serve as the Pay Code generation gateway.
- Which x-feedback contract should later serve as the delivery handoff.
- Which x-journal event shape should later receive campaign events.
