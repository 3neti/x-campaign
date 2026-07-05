# x-campaign Domain Model

## Campaign

A distribution initiative, such as a payroll batch, educational assistance release, scholarship release, or disaster relief program.

## CampaignAudience

A collection of recipients targeted by a campaign.

## CampaignRecipient

A beneficiary. Recipients are long-lived and should support cumulative recipient history in later phases.

## CampaignExecution

A campaign run. A campaign may have many executions over time.

## CampaignBatch

A partition of an execution used for scale and queueability.

## CampaignDelivery

One planned or attempted delivery to one recipient through one channel.

## CampaignImport

An audience ingestion event, such as manual entry, CSV import, API import, or future spreadsheet ingestion.

## External References

Phase 0 allows references to external execution concepts through contracts and metadata. It does not create campaign-owned voucher templates or claim templates.

