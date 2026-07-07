<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportApprovalWorkspaceResultData extends Data
{
    /**
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignAudienceImportRowCollectionPlanData $collection,
        public readonly CampaignAudienceImportReviewSummaryData $summary,
        public readonly CampaignAudienceImportApprovalDecisionData $decision,
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
