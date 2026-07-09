<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\PresentsCampaignCockpitApiResponses;
use LBHurtado\XCampaign\Data\CampaignCockpitApiResponseData;
use LBHurtado\XCampaign\Data\CampaignCockpitSummaryData;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitApiResponsePresenter;

it('presents a cockpit summary as a host-safe operator api response envelope', function () {
    $presenter = new CampaignCockpitApiResponsePresenter;

    $response = $presenter->present(phase10eCockpitSummary());

    expect($presenter)->toBeInstanceOf(PresentsCampaignCockpitApiResponses::class)
        ->and($response)->toBeInstanceOf(CampaignCockpitApiResponseData::class)
        ->and($response->status)->toBe('ok')
        ->and($response->data)->toMatchArray([
            'planning_key' => 'planning-cockpit-api',
            'execution_id' => 'execution-cockpit-api',
            'operator_id' => 'operator-cockpit',
            'cards' => ['campaign' => ['recipient_count' => 10]],
            'panels' => ['report' => ['title' => 'Campaign Operator Summary']],
            'actions' => ['refresh' => ['available' => true]],
        ])
        ->and($response->meta)->toMatchArray([
            'source' => 'campaign-cockpit-api-response-presenter',
            'read_only' => true,
            'routes_registered' => false,
            'controllers_registered' => false,
        ])
        ->and($response->effects->toArray())->toMatchArray([
            'persists' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('marks attention-required summaries as attention responses without hiding blockers', function () {
    $presenter = new CampaignCockpitApiResponsePresenter;

    $summary = new CampaignCockpitSummaryData(
        status: 'attention_required',
        planningKey: 'planning-cockpit-api',
        executionId: 'execution-cockpit-api',
        operatorId: 'operator-cockpit',
        blockers: ['operator attention required'],
    );

    $response = $presenter->present($summary);

    expect($response->status)->toBe('attention_required')
        ->and($response->data)->toMatchArray([
            'blockers' => ['operator attention required'],
        ]);
});

function phase10eCockpitSummary(): CampaignCockpitSummaryData
{
    return new CampaignCockpitSummaryData(
        status: 'ready',
        planningKey: 'planning-cockpit-api',
        executionId: 'execution-cockpit-api',
        operatorId: 'operator-cockpit',
        cards: ['campaign' => ['recipient_count' => 10]],
        panels: ['report' => ['title' => 'Campaign Operator Summary']],
        actions: ['refresh' => ['available' => true]],
        metadata: ['request_id' => 'request-cockpit-api'],
    );
}
