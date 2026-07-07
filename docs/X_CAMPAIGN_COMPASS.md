# x-campaign Compass

## Mission

Build `x-campaign` as the beneficiary distribution platform for the x-change Settlement Operating System.

## Current Slice

Wave 5 — Phase 1R: Approved Import Recipient Attachment Mutation Decision Point.

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
- Added Phase 1G campaign planning workspace contract:
  - `CampaignPlanningWorkspace`
- Added Phase 1G repository-backed workspace:
  - `RepositoryBackedCampaignPlanningWorkspace`
- Bound the planning workspace contract to the repository-backed implementation.
- Added Phase 1G Pest coverage for create/store, update, schedule, archive, summary, missing-key fail-closed behavior, binding, and no migrations/queues/issuance/delivery/journal ownership.
- Added Phase 1H audience import planning contract:
  - `PlansCampaignAudienceImports`
- Added Phase 1H audience import planning action:
  - `PlanCampaignAudienceImport`
- Added audience import planning DTOs:
  - `CampaignAudienceImportPlanningInputData`
  - `CampaignAudienceImportPlanData`
- Bound the audience import planning contract to the non-parsing baseline implementation.
- Added Phase 1H Pest coverage for import-intent planning, manual defaults, empty-audience fail-closed behavior, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1I audience import workspace contract:
  - `CampaignAudienceImportWorkspace`
- Added Phase 1I repository-backed audience import workspace:
  - `RepositoryBackedCampaignAudienceImportWorkspace`
- Bound the audience import workspace contract to the repository-backed non-parsing implementation.
- Added Phase 1I Pest coverage for planning imports against stored campaign audiences, missing planning keys, missing audiences, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1J recipient import row planning contract:
  - `PlansCampaignRecipientImportRows`
- Added Phase 1J recipient import row planner:
  - `PlanCampaignRecipientImportRow`
- Added recipient import row DTOs:
  - `CampaignRecipientImportRowPlanningInputData`
  - `CampaignRecipientImportRowData`
- Bound the recipient import row planning contract to the non-importing implementation.
- Added Phase 1J Pest coverage for row normalization, common aliases, invalid row diagnostics, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1K recipient import row workspace contract:
  - `CampaignRecipientImportRowWorkspace`
- Added Phase 1K repository-backed recipient import row workspace:
  - `RepositoryBackedCampaignRecipientImportRowWorkspace`
- Bound the recipient import row workspace contract to the repository-backed non-importing implementation.
- Added Phase 1K Pest coverage for planning rows against stored campaign audiences, invalid-row diagnostics with validated context, missing planning keys, missing audiences, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1L audience import row collection planning contract:
  - `PlansCampaignAudienceImportRowCollections`
- Added Phase 1L audience import row collection planner:
  - `PlanCampaignAudienceImportRowCollection`
- Added audience import row collection DTOs:
  - `CampaignAudienceImportRowCollectionPlanningInputData`
  - `CampaignAudienceImportRowCollectionPlanData`
- Bound the audience import row collection planning contract to the non-importing implementation.
- Added Phase 1L Pest coverage for planning in-memory row collections, valid/invalid row summaries, empty collections, context validation handoff, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1M audience import review summary contract:
  - `BuildsCampaignAudienceImportReviewSummaries`
- Added Phase 1M audience import review summary read model:
  - `CampaignAudienceImportReviewSummaryReadModel`
- Added audience import review DTOs:
  - `CampaignAudienceImportReviewSummaryData`
  - `CampaignAudienceImportReviewRowIssueData`
- Bound the audience import review summary contract to the read-only implementation.
- Added Phase 1M Pest coverage for review-required summaries, ready-for-approval summaries, empty summaries, binding, and no file parsing/persistence/queues/issuance/delivery behavior.
- Added Phase 1N audience import approval decision contract:
  - `DecidesCampaignAudienceImportApprovals`
- Added Phase 1N approval decision action:
  - `DecideCampaignAudienceImportApproval`
- Added audience import approval decision DTOs:
  - `CampaignAudienceImportApprovalDecisionInputData`
  - `CampaignAudienceImportApprovalDecisionData`
- Bound the audience import approval decision contract to the decision-only implementation.
- Added Phase 1N Pest coverage for approval, rejection, blocked approval, unknown decision fail-closed behavior, binding, and no persistence/queues/issuance/delivery/audience mutation behavior.
- Added Phase 1O audience import approval workspace contract:
  - `CampaignAudienceImportApprovalWorkspace`
- Added Phase 1O repository-backed approval workspace:
  - `RepositoryBackedCampaignAudienceImportApprovalWorkspace`
- Added audience import approval workspace DTOs:
  - `CampaignAudienceImportApprovalWorkspaceInputData`
  - `CampaignAudienceImportApprovalWorkspaceResultData`
- Bound the audience import approval workspace contract to the repository-backed non-mutating implementation.
- Added Phase 1O Pest coverage for approving, blocking approval, rejecting, missing planning keys, missing audiences, binding, and no persistence/queues/issuance/delivery/audience mutation behavior.
- Added Phase 1P approved import recipient attachment planning contract:
  - `PlansCampaignAudienceImportRecipientAttachments`
- Added Phase 1P recipient attachment planning action:
  - `PlanCampaignAudienceImportRecipientAttachments`
- Added audience import recipient attachment planning DTO:
  - `CampaignAudienceImportRecipientAttachmentPlanData`
- Bound the recipient attachment planning contract to the planning-only implementation.
- Added Phase 1P Pest coverage for approved attachment planning, non-approved blocking, not-ready review blocking, partial row planning, binding, and no persistence/queues/issuance/delivery/audience mutation behavior.
- Added Phase 1Q approved import recipient attachment workspace contract:
  - `CampaignAudienceImportRecipientAttachmentWorkspace`
- Added Phase 1Q repository-backed recipient attachment workspace:
  - `RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace`
- Added audience import recipient attachment workspace result DTO:
  - `CampaignAudienceImportRecipientAttachmentWorkspaceResultData`
- Bound the recipient attachment workspace contract to the repository-backed non-mutating implementation.
- Added Phase 1Q Pest coverage for approved workspace planning, invalid-row blocking, operator rejection blocking, missing planning keys, missing audiences, binding, and no persistence/queues/issuance/delivery/audience mutation behavior.
- Added Phase 1R approved import recipient attachment mutation decision contract:
  - `DecidesCampaignAudienceImportRecipientAttachmentMutations`
- Added Phase 1R recipient attachment mutation decision action:
  - `DecideCampaignAudienceImportRecipientAttachmentMutation`
- Added recipient attachment mutation decision DTOs:
  - `CampaignAudienceImportRecipientAttachmentMutationDecisionInputData`
  - `CampaignAudienceImportRecipientAttachmentMutationDecisionData`
- Bound the mutation decision contract to the decision-only implementation.
- Added Phase 1R Pest coverage for allowed decisions, deferred decisions, blocked plans, partial plans, unknown decisions, binding, and no persistence/queues/issuance/delivery/audience mutation behavior.

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
- Campaign planning workspace integration composes existing planning actions, repository state, and read models. It is not a controller, API, workflow executor, persistence layer, queue layer, delivery layer, journal writer, or Pay Code issuer.
- Audience import planning records import intent only. Phase 1H does not read files, parse rows, persist imports, create recipients, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Audience import workspace integration validates import intent against stored in-memory campaign/audience plans. It still does not read files, parse rows, persist imports, create recipients, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Recipient import row planning normalizes one raw row array into recipient planning data, row status, diagnostics, and side-effect metadata. It does not parse files, persist recipients, attach recipients to audiences, queue work, issue Pay Codes, send feedback, write journals, or move money.
- Recipient import row workspace integration validates row planning against stored in-memory campaign/audience context. It still does not parse files, persist recipients, attach recipients to audiences, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Audience import row collection planning composes the recipient row workspace across in-memory row arrays and returns aggregate row counts/diagnostics. It does not read files, parse files, persist imports, attach recipients to audiences, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Audience import review summaries are read-only projections over row collection plans. They do not approve imports, attach recipients, persist state, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Audience import approval decisions are decision-only DTO outputs over review summaries. They do not persist approval state, attach recipients, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Audience import approval workspace integration composes stored in-memory campaign/audience context, row collection planning, review summaries, and approval decisions. It does not persist approval state, attach recipients, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Approved import recipient attachment planning converts an approved import workspace result into a recipient attachment plan only. It identifies attachable recipient planning inputs and blocked rows, but it does not persist approval state, mutate campaign audiences, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Approved import recipient attachment workspace integration composes approval workspace results with attachment planning. It returns approval context and attachment readiness together, but it does not persist approval state, mutate campaign audiences, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.
- Approved import recipient attachment mutation decisions are decision-only gates. They can mark a ready attachment plan as allowed for a future mutation, deferred, or blocked, but they do not persist state, mutate campaign audiences, queue ingestion, issue Pay Codes, send feedback, write journals, or move money.

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
- Phase 1G focused failing baseline was observed before implementation: 5 failed, 9 passed, 104 assertions.
- Phase 1G focused result after implementation: `14 passed, 135 assertions`.
- Phase 1G full package result: `48 passed, 413 assertions`.
- Phase 1G syntax checks passed for `src`, `tests`, and `config`.
- Phase 1G `composer validate --strict` passed.
- Phase 1G formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1H focused failing baseline was observed before implementation: 4 failed, 10 passed, 123 assertions.
- Phase 1H focused result after implementation: `14 passed, 155 assertions`.
- Phase 1H full package result: `53 passed, 464 assertions`.
- Phase 1H syntax checks passed for `src`, `tests`, and `config`.
- Phase 1H `composer validate --strict` passed.
- Phase 1H formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1I focused failing baseline was observed before implementation: 4 failed, 11 passed, 141 assertions.
- Phase 1I focused result after implementation: `15 passed, 167 assertions`.
- Phase 1I full package result: `58 passed, 509 assertions`.
- Phase 1I syntax checks passed for `src`, `tests`, and `config`.
- Phase 1I `composer validate --strict` passed.
- Phase 1I formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1J focused failing baseline was observed before implementation: 4 failed, 12 passed, 160 assertions.
- Phase 1J focused result after implementation: `16 passed, 199 assertions`.
- Phase 1J full package result: `63 passed, 567 assertions`.
- Phase 1J syntax checks passed for `src`, `tests`, and `config`.
- Phase 1J `composer validate --strict` passed.
- Phase 1J formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1K focused failing baseline was observed before implementation: 5 failed, 13 passed, 179 assertions.
- Phase 1K focused result after implementation: `18 passed, 215 assertions`.
- Phase 1K full package result: `69 passed, 622 assertions`.
- Phase 1K syntax checks passed for `src`, `tests`, and `config`.
- Phase 1K `composer validate --strict` passed.
- Phase 1K formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1L focused failing baseline was observed before implementation: 4 failed, 14 passed, 199 assertions.
- Phase 1L focused result after implementation: `18 passed, 244 assertions`.
- Phase 1L full package result: `74 passed, 687 assertions`.
- Phase 1L syntax checks passed for `src`, `tests`, and `config`.
- Phase 1L `composer validate --strict` passed.
- Phase 1L formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1M focused failing baseline was observed before implementation: 4 failed, 15 passed, 217 assertions.
- Phase 1M focused result after implementation: `19 passed, 261 assertions`.
- Phase 1M full package result: `79 passed, 750 assertions`.
- Phase 1M syntax checks passed for `src`, `tests`, and `config`.
- Phase 1M `composer validate --strict` passed.
- Phase 1M formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1N focused failing baseline was observed before implementation: 5 failed, 16 passed, 238 assertions.
- Phase 1N focused result after implementation: `21 passed, 281 assertions`.
- Phase 1N full package result: `85 passed, 814 assertions`.
- Phase 1N syntax checks passed for `src`, `tests`, and `config`.
- Phase 1N `composer validate --strict` passed.
- Phase 1N formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1O focused failing baseline was observed before implementation: 6 failed, 17 passed, 261 assertions.
- Phase 1O focused result after implementation: `23 passed, 298 assertions`.
- Phase 1O full package result: `92 passed, 874 assertions`.
- Phase 1O syntax checks passed for `src`, `tests`, and `config`.
- Phase 1O `composer validate --strict` passed.
- Phase 1O formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1P focused failing baseline was observed before implementation: 5 failed, 18 passed, 280 assertions.
- Phase 1P focused result after implementation: `23 passed, 342 assertions`.
- Phase 1P full package result: `98 passed, 957 assertions`.
- Phase 1P syntax checks passed for `src`, `tests`, and `config`.
- Phase 1P `composer validate --strict` passed.
- Phase 1P formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1Q focused failing baseline was observed before implementation: 6 failed, 19 passed, 303 assertions.
- Phase 1Q focused result after implementation: `25 passed, 345 assertions`.
- Phase 1Q full package result: `105 passed, 1022 assertions`.
- Phase 1Q syntax checks passed for `src`, `tests`, and `config`.
- Phase 1Q `composer validate --strict` passed.
- Phase 1Q formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.
- Phase 1R focused failing baseline was observed before implementation: 6 failed, 20 passed, 322 assertions.
- Phase 1R focused result after implementation: `26 passed, 370 assertions`.
- Phase 1R full package result: `112 passed, 1091 assertions`.
- Phase 1R syntax checks passed for `src`, `tests`, and `config`.
- Phase 1R `composer validate --strict` passed.
- Phase 1R formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package.

## Next Recommended Slice

Phase 1S — Approved Import Recipient Attachment In-Memory Mutation Baseline, before migrations, queues, Pay Code generation, or delivery.

## Open Questions

- Which x-change contract should later serve as the Pay Code generation gateway.
- Which x-feedback contract should later serve as the delivery handoff.
- Which x-journal event shape should later receive campaign events.
