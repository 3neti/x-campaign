<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData extends Data
{
    /**
     * @param  array<int, CampaignAudienceImportAttachmentOperatorWorkspaceResultData>  $results
     * @param  array<int, string>  $blockers
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly string $planningKey = '',
        public readonly string $status = 'empty',
        public readonly int $totalAudiences = 0,
        public readonly int $completeAudiences = 0,
        public readonly int $attentionRequiredAudiences = 0,
        public readonly int $emptyAudiences = 0,
        public readonly int $totalImports = 0,
        public readonly int $attachedRows = 0,
        public readonly int $blockedRows = 0,
        public readonly int $recipientDelta = 0,
        public readonly array $results = [],
        public readonly array $blockers = [],
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
