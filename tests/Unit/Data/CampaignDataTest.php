<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Data\CampaignAudienceData;
use LBHurtado\XCampaign\Data\CampaignBatchData;
use LBHurtado\XCampaign\Data\CampaignData;
use LBHurtado\XCampaign\Data\CampaignDeliveryData;
use LBHurtado\XCampaign\Data\CampaignDeliveryResultData;
use LBHurtado\XCampaign\Data\CampaignExecutionData;
use LBHurtado\XCampaign\Data\CampaignFeatureProfileData;
use LBHurtado\XCampaign\Data\CampaignImportData;
use LBHurtado\XCampaign\Data\CampaignRecipientData;
use LBHurtado\XCampaign\Enums\CampaignStatus;

it('serializes the campaign dto baseline', function () {
    $campaign = new CampaignData(
        id: 'campaign-1',
        name: 'Educational Assistance 2027',
        description: 'Scholar assistance release',
        featureProfile: 'baseline',
        owner: 'operations',
        issuer: 'issuer-1',
        metadata: ['program_blueprint_reference' => 'blueprint-1'],
    );

    expect($campaign->toArray())->toMatchArray([
        'id' => 'campaign-1',
        'name' => 'Educational Assistance 2027',
        'description' => 'Scholar assistance release',
        'featureProfile' => 'baseline',
        'owner' => 'operations',
        'issuer' => 'issuer-1',
        'status' => 'draft',
        'scheduledAt' => null,
        'metadata' => ['program_blueprint_reference' => 'blueprint-1'],
    ]);
});

it('accepts normalized campaign status grammar values', function () {
    $campaign = new CampaignData(
        name: 'Payroll Batch 2027-01',
        status: CampaignStatus::normalize('scheduled')->value,
    );

    expect($campaign->status)->toBe('scheduled')
        ->and($campaign->toArray()['status'])->toBe('scheduled');
});

it('keeps phase zero dto defaults side effect free', function () {
    expect((new CampaignAudienceData(name: 'All Scholars'))->toArray())->toMatchArray([
        'name' => 'All Scholars',
        'status' => 'draft',
        'metadata' => [],
    ])
        ->and((new CampaignRecipientData(mobile: '+639171234567'))->toArray())->toMatchArray([
            'mobile' => '+639171234567',
            'metadata' => [],
        ])
        ->and((new CampaignExecutionData(campaignId: 'campaign-1'))->toArray())->toMatchArray([
            'campaignId' => 'campaign-1',
            'status' => 'planned',
        ])
        ->and((new CampaignBatchData(executionId: 'execution-1'))->toArray())->toMatchArray([
            'executionId' => 'execution-1',
            'sequence' => 1,
            'recipientCount' => 0,
            'status' => 'planned',
        ])
        ->and((new CampaignDeliveryData(channel: 'sms'))->toArray())->toMatchArray([
            'channel' => 'sms',
            'status' => 'planned',
            'payload' => [],
        ])
        ->and((new CampaignDeliveryResultData)->toArray())->toMatchArray([
            'status' => 'planned',
            'retryable' => false,
        ])
        ->and((new CampaignImportData(source: 'manual'))->toArray())->toMatchArray([
            'source' => 'manual',
            'status' => 'pending',
            'recipientCount' => 0,
        ])
        ->and((new CampaignFeatureProfileData)->toArray())->toMatchArray([
            'name' => 'baseline',
            'features' => [],
            'metadata' => [],
        ]);
});
