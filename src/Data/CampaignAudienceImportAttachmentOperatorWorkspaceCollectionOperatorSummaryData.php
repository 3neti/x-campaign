<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryData extends Data
{
    /**
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey = '',
        public readonly ?string $campaignName = null,
        public readonly string $status = 'empty',
        public readonly string $operatorPosture = 'no_audience_activity',
        public readonly int $totalAudiences = 0,
        public readonly int $completeAudiences = 0,
        public readonly int $attentionRequiredAudiences = 0,
        public readonly int $emptyAudiences = 0,
        public readonly int $totalImports = 0,
        public readonly int $attachedRows = 0,
        public readonly int $blockedRows = 0,
        public readonly int $recipientDelta = 0,
        public readonly int $blockerCount = 0,
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
