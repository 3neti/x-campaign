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
