# x-campaign Domain Model

## Campaign

A distribution initiative, such as a payroll batch, educational assistance release, scholarship release, or disaster relief program.

Phase 1A status grammar:

- `draft`
- `scheduled`
- `running`
- `paused`
- `completed`
- `cancelled`
- `archived`

## CampaignAudience

A collection of recipients targeted by a campaign.

Phase 1A status grammar:

- `draft`
- `importing`
- `ready`
- `locked`
- `archived`

## CampaignRecipient

A beneficiary. Recipients are long-lived and should support cumulative recipient history in later phases.

## CampaignExecution

A campaign run. A campaign may have many executions over time.

Phase 1A status grammar:

- `planned`
- `queued`
- `running`
- `paused`
- `completed`
- `failed`
- `cancelled`

## CampaignBatch

A partition of an execution used for scale and queueability.

## CampaignDelivery

One planned or attempted delivery to one recipient through one channel.

## CampaignImport

An audience ingestion event, such as manual entry, CSV import, API import, or future spreadsheet ingestion.

## External References

Phase 0 allows references to external execution concepts through contracts and metadata. It does not create campaign-owned voucher templates or claim templates.

## Campaign Planning

Phase 1B introduces in-memory campaign planning contracts:

- create plan
- update plan
- schedule plan
- archive plan

Planning output is represented by `CampaignPlanData`.

Planning actions are intentionally not persistence actions. They preserve side-effect metadata showing:

- no persistence
- no Pay Code issuance
- no feedback delivery
- no journal writes
- no money movement

## Audience and Recipient Planning

Phase 1C introduces in-memory audience and recipient planning:

- add an audience to a campaign plan
- add a recipient to an audience plan
- remove a recipient from an audience plan

Audience planning output is represented by `CampaignAudiencePlanData`, which contains:

- `CampaignAudienceData`
- planned recipients
- side-effect metadata

Recipient planning accepts `CampaignRecipientPlanningInputData` and produces `CampaignRecipientData` inside the in-memory plan.

This layer intentionally does not import spreadsheets or CSV files yet. File import and bulk ingestion remain future slices.

## Execution Planning

Phase 1D introduces in-memory execution planning:

- plan an execution for a campaign audience
- partition a planned execution into batches

Execution planning output is represented by `CampaignExecutionPlanData`, which contains:

- `CampaignExecutionData`
- planned `CampaignBatchData` partitions
- side-effect metadata

Execution planning does not execute distribution. Batches are not queued jobs and do not issue Pay Codes or send messages.

## Campaign Read Models

Phase 1E introduces read-only campaign summary views:

- campaign-level summary counts
- audience summary rows
- execution summary rows

Summary output is represented by `CampaignSummaryData`, with nested `CampaignAudienceSummaryData` and `CampaignExecutionSummaryData`.

Read models are projection helpers over in-memory planning DTOs. They do not load persisted records and they intentionally avoid exposing recipient detail records.

## Campaign Planning Repository

Phase 1F introduces `CampaignPlanRepository` as the seam for future persistence.

The initial implementation, `InMemoryCampaignPlanRepository`, stores `CampaignPlanData` instances under caller-supplied planning keys.

This repository is process-local and non-durable. It exists to stabilize package contracts before migrations or durable storage are introduced.

## Campaign Planning Workspace

Phase 1G introduces `CampaignPlanningWorkspace` as the composition seam over planning actions, repository state, and summary read models.

The initial implementation, `RepositoryBackedCampaignPlanningWorkspace`, uses the in-memory repository and existing planning actions. It does not introduce persistence or execution behavior.

## Audience Import Planning

Phase 1H introduces `PlansCampaignAudienceImports` as the contract for describing audience import intent.

The initial implementation produces `CampaignAudienceImportPlanData` around `CampaignImportData`. It captures planned source metadata only and does not parse files or create recipients.

## Audience Import Workspace

Phase 1I introduces `CampaignAudienceImportWorkspace` as the composition seam between stored campaign planning state and audience import planning.

The initial implementation validates the planning key and audience id against the in-memory repository, then delegates to the import planner. It does not parse files or mutate recipient state.

## Recipient Import Row Planning

Phase 1J introduces `PlansCampaignRecipientImportRows` as the contract for normalizing a single raw recipient row into campaign recipient planning data.

The initial implementation produces `CampaignRecipientImportRowData`, which contains:

- import and audience references
- row number
- row status
- normalized `CampaignRecipientPlanningInputData`
- raw row data
- validation diagnostics
- no-side-effect metadata

This layer intentionally stops before file parsing, persistence, audience mutation, queues, Pay Code generation, delivery, journal writes, or money movement.

## Recipient Import Row Workspace

Phase 1K introduces `CampaignRecipientImportRowWorkspace` as the composition seam between stored campaign planning state and recipient import row planning.

The initial implementation validates the planning key and audience id against the in-memory repository, then delegates to the row planner. It enriches row planning output with campaign/audience context metadata, but it does not attach the row to the audience or mutate recipient state.

## Audience Import Row Collection Planning

Phase 1L introduces `PlansCampaignAudienceImportRowCollections` as the contract for planning an already-materialized collection of raw recipient rows.

The initial implementation delegates each row to `CampaignRecipientImportRowWorkspace` and returns `CampaignAudienceImportRowCollectionPlanData` with row-level results, total row counts, valid/invalid counts, and row-number diagnostics.

This layer intentionally assumes row arrays already exist in memory. It does not parse files, persist imports, attach recipients, queue work, issue Pay Codes, deliver feedback, write journals, or move money.
