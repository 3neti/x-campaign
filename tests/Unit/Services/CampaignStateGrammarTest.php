<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Enums\CampaignAudienceStatus;
use LBHurtado\XCampaign\Enums\CampaignExecutionStatus;
use LBHurtado\XCampaign\Enums\CampaignStatus;
use LBHurtado\XCampaign\Services\CampaignStateGrammar;

it('normalizes campaign core status aliases', function () {
    expect(CampaignStatus::normalize(null))->toBe(CampaignStatus::Draft)
        ->and(CampaignStatus::normalize(''))->toBe(CampaignStatus::Draft)
        ->and(CampaignStatus::normalize('scheduled'))->toBe(CampaignStatus::Scheduled)
        ->and(CampaignStatus::normalize('RUNNING'))->toBe(CampaignStatus::Running)
        ->and(CampaignStatus::normalize('archived'))->toBe(CampaignStatus::Archived)
        ->and(CampaignAudienceStatus::normalize('ready'))->toBe(CampaignAudienceStatus::Ready)
        ->and(CampaignExecutionStatus::normalize('processing'))->toBe(CampaignExecutionStatus::Running);
});

it('fails closed for unknown campaign core statuses', function () {
    expect(fn () => CampaignStatus::normalize('executed'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign status [executed].')
        ->and(fn () => CampaignAudienceStatus::normalize('paid'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign audience status [paid].')
        ->and(fn () => CampaignExecutionStatus::normalize('redeemed'))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign execution status [redeemed].');
});

it('describes allowed campaign status transitions without executing workflows', function () {
    $grammar = new CampaignStateGrammar;

    expect($grammar->canTransition(CampaignStatus::Draft, CampaignStatus::Scheduled))->toBeTrue()
        ->and($grammar->canTransition(CampaignStatus::Scheduled, CampaignStatus::Running))->toBeTrue()
        ->and($grammar->canTransition(CampaignStatus::Running, CampaignStatus::Completed))->toBeTrue()
        ->and($grammar->canTransition(CampaignStatus::Draft, CampaignStatus::Archived))->toBeTrue()
        ->and($grammar->canTransition(CampaignStatus::Completed, CampaignStatus::Running))->toBeFalse()
        ->and($grammar->canTransition(CampaignStatus::Archived, CampaignStatus::Running))->toBeFalse()
        ->and($grammar->transitionsFor(CampaignStatus::Running))->toBe([
            'paused',
            'completed',
            'cancelled',
        ]);
});

it('describes audience and execution transitions independently from payment execution', function () {
    $grammar = new CampaignStateGrammar;

    expect($grammar->canTransition(CampaignAudienceStatus::Draft, CampaignAudienceStatus::Importing))->toBeTrue()
        ->and($grammar->canTransition(CampaignAudienceStatus::Importing, CampaignAudienceStatus::Ready))->toBeTrue()
        ->and($grammar->canTransition(CampaignAudienceStatus::Ready, CampaignAudienceStatus::Archived))->toBeTrue()
        ->and($grammar->canTransition(CampaignAudienceStatus::Archived, CampaignAudienceStatus::Ready))->toBeFalse()
        ->and($grammar->canTransition(CampaignExecutionStatus::Planned, CampaignExecutionStatus::Queued))->toBeTrue()
        ->and($grammar->canTransition(CampaignExecutionStatus::Queued, CampaignExecutionStatus::Running))->toBeTrue()
        ->and($grammar->canTransition(CampaignExecutionStatus::Running, CampaignExecutionStatus::Completed))->toBeTrue()
        ->and($grammar->canTransition(CampaignExecutionStatus::Completed, CampaignExecutionStatus::Running))->toBeFalse();
});
