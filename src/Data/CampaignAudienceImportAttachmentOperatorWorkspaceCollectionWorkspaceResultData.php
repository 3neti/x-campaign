<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Data;

use Spatie\LaravelData\Data;

class CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspaceResultData extends Data
{
    /**
     * @param  array<string, bool>  $effects
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public readonly CampaignAudienceImportAttachmentOperatorWorkspaceCollectionData $collection,
        public readonly array $effects = [],
        public readonly array $metadata = [],
    ) {}
}
