# x-campaign Compass

## Mission

Build `x-campaign` as the beneficiary distribution platform for the x-change Settlement Operating System.

## Current Slice

Wave 5 — Phase 1F: Campaign Planning Repository Contract Baseline.

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
- Added Phase 1A campaign, audience, and execution status grammar enums.
- Added `CampaignStateGrammar` for side-effect-free transition checks.
- Bound `CampaignStateGrammar` in the package service provider.
- Added Phase 1A Pest coverage for status normalization, fail-closed unknown statuses, transition grammar, service-provider binding, and no-side-effect boundaries.
- Added Phase 1B action contracts:
  - `CreatesCampaignPlans`
  - `UpdatesCampaignPlans`
  - `SchedulesCampaignPlans`
  - `ArchivesCampaignPlans`
- Added Phase 1B in-memory planning actions:
  - `CreateCampaignPlan`
  - `UpdateCampaignPlan`
  - `ScheduleCampaignPlan`
  - `ArchiveCampaignPlan`
- Added planning DTOs:
  - `CampaignPlanningInputData`
  - `CampaignPlanData`
- Bound planning action contracts to in-memory implementations.
- Added Phase 1B Pest coverage for planning actions, action contract bindings, and no persistence/transport/execution side effects.
- Added Phase 1C audience and recipient planning contracts:
  - `AddsAudiencesToCampaignPlans`
  - `AddsRecipientsToCampaignAudiencePlans`
  - `RemovesRecipientsFromCampaignAudiencePlans`
- Added Phase 1C in-memory planning actions:
  - `AddAudienceToCampaignPlan`
  - `AddRecipientToCampaignAudiencePlan`
  - `RemoveRecipientFromCampaignAudiencePlan`
- Added audience and recipient planning DTOs:
  - `CampaignAudiencePlanData`
  - `CampaignAudiencePlanningInputData`
  - `CampaignRecipientPlanningInputData`
- Bound audience and recipient planning contracts to in-memory implementations.
- Added Phase 1C Pest coverage for audience planning, recipient normalization, recipient removal, unknown-audience fail-closed behavior, contract bindings, and no import/persistence/transport side effects.
- Added Phase 1D execution planning contracts:
  - `PlansCampaignExecutions`
  - `PlansCampaignExecutionBatches`
- Added Phase 1D in-memory planning actions:
  - `PlanCampaignExecution`
  - `PlanCampaignExecutionBatches`
- Added execution planning DTOs:
  - `CampaignExecutionPlanningInputData`
  - `CampaignExecutionPlanData`
- Bound execution planning contracts to in-memory implementations.
- Added Phase 1D Pest coverage for execution planning, unknown-audience fail-closed behavior, batch planning, invalid batch size rejection, action bindings, and no queue/issuance/delivery/persistence side effects.
- Added Phase 1E summary read model contract:
  - `BuildsCampaignSummaries`
- Added Phase 1E read model:
  - `CampaignSummaryReadModel`
- Added summary DTOs:
  - `CampaignSummaryData`
  - `CampaignAudienceSummaryData`
  - `CampaignExecutionSummaryData`
- Bound the summary read model contract to the read-only implementation.
- Added Phase 1E Pest coverage for side-effect-free campaign summaries, audience summary rows, execution summary rows, empty-plan summaries, contract binding, and no route/persistence/execution/delivery/journal ownership.
- Added Phase 1F campaign planning repository contract:
  - `CampaignPlanRepository`
- Added Phase 1F in-memory repository:
  - `InMemoryCampaignPlanRepository`
- Bound the planning repository contract to the in-memory baseline.
- Added Phase 1F Pest coverage for storing, retrieving, listing, forgetting, empty-key rejection, contract binding, and no migrations/queues/issuance/delivery/journal ownership.

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
- Campaign state grammar is descriptive infrastructure only. It does not execute campaigns, issue Pay Codes, send notifications, write journals, call providers, or mutate wallets.
- Unknown campaign core statuses fail closed instead of silently falling back to execution-like states.
- Campaign planning actions are in-memory only. They describe campaign plans and state changes without persistence, queues, distribution execution, notification delivery, journal writes, provider calls, wallet access, or money movement.
- Audience and recipient planning actions are in-memory only. They do not import files, persist recipients, send messages, issue Pay Codes, write journals, call providers, or mutate wallets.
- Recipient planning normalizes presentation contact fields only. It is not KYC, identity verification, notification routing, or execution authorization.
- Campaign execution planning is in-memory only. It does not queue jobs, issue Pay Codes, send feedback, write journals, call providers, persist state, or move money.
- Execution batches are planning partitions only. They are not queue batches, job batches, delivery batches, or execution records.
- Campaign read models are derived from existing in-memory planning DTOs only. They do not query persistence, register routes, execute campaigns, deliver feedback, write journals, issue Pay Codes, or expose recipient detail records.
- Campaign planning repositories are introduced as contracts before persistence. The Phase 1F implementation is process-local and in-memory only; it does not create tables, use Eloquent, queue work, issue Pay Codes, send feedback, write journals, or move money.

## Test Coverage Status

- `composer validate --strict` passed.
- `php -d memory_limit=1G vendor/bin/pest` passed: 6 tests, 82 assertions.
- `php -l` passed for modified PHP files under `src`, `tests`, and `config`.
- Initial non-elevated Pest run failed because sandbox permissions blocked Testbench/Pest cache writes under `vendor`; elevated rerun passed.
- Phase 1A focused failing baseline was observed before implementation: 5 failed, 5 passed, 86 assertions.
- Phase 1A focused result after implementation: `13 passed, 121 assertions`.
- Phase 1A full package result: `13 passed, 121 assertions`.
- Phase 1A formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1B focused failing baseline was observed before implementation: 5 failed, 4 passed, 35 assertions.
- Phase 1B focused result after implementation: `9 passed, 73 assertions`.
- Phase 1B full package result: `19 passed, 171 assertions`.
- Phase 1B syntax checks passed for `src`, `tests`, and `config`.
- Phase 1B `composer validate --strict` passed.
- Phase 1B formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1C focused failing baseline was observed before implementation: 5 failed, 5 passed, 48 assertions.
- Phase 1C focused result after implementation: `10 passed, 80 assertions`.
- Phase 1C full package result: `25 passed, 216 assertions`.
- Phase 1C syntax checks passed for `src`, `tests`, and `config`.
- Phase 1C `composer validate --strict` passed.
- Phase 1C formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1D focused failing baseline was observed before implementation: 5 failed, 6 passed, 61 assertions.
- Phase 1D focused result after implementation: `11 passed, 96 assertions`.
- Phase 1D full package result: `31 passed, 265 assertions`.
- Phase 1D syntax checks passed for `src`, `tests`, and `config`.
- Phase 1D `composer validate --strict` passed.
- Phase 1D formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1E focused failing baseline was observed before implementation: 4 failed, 7 passed, 73 assertions.
- Phase 1E focused result after implementation: `11 passed, 117 assertions`.
- Phase 1E full package result: `36 passed, 323 assertions`.
- Phase 1E syntax checks passed for `src`, `tests`, and `config`.
- Phase 1E `composer validate --strict` passed.
- Phase 1E formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1F focused failing baseline was observed before implementation: 6 failed, 7 passed, 86 assertions.
- Phase 1F focused result after implementation: `13 passed, 120 assertions`.
- Phase 1F full package result: `42 passed, 369 assertions`.
- Phase 1F syntax checks passed for `src`, `tests`, and `config`.
- Phase 1F `composer validate --strict` passed.
- Phase 1F formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.

## Next Recommended Slice

Phase 1G — Campaign Planning Repository Integration Baseline, before migrations, queues, Pay Code generation, or delivery.

## Open Questions

- Which x-change contract should later serve as the Pay Code generation gateway.
- Which x-feedback contract should later serve as the delivery handoff.
- Which x-journal event shape should later receive campaign events.
