<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\ArchiveCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\ScheduleCampaignPlan;
use LBHurtado\XCampaign\Actions\UpdateCampaignPlan;
use LBHurtado\XCampaign\Contracts\CampaignPlanningWorkspace;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPlanningWorkspace;

function campaignPlanningWorkspace(): RepositoryBackedCampaignPlanningWorkspace
{
    return new RepositoryBackedCampaignPlanningWorkspace(
        repository: new InMemoryCampaignPlanRepository,
        creator: new CreateCampaignPlan,
        updater: new UpdateCampaignPlan,
        scheduler: new ScheduleCampaignPlan,
        archiver: new ArchiveCampaignPlan,
        summaries: new CampaignSummaryReadModel,
    );
}

it('creates and stores a campaign plan through the repository integration seam', function () {
    $workspace = campaignPlanningWorkspace();

    $plan = $workspace->create('planning-001', new CampaignPlanningInputData(
        name: 'Integrated Campaign',
        featureProfile: 'baseline',
        owner: 'operations',
    ));

    expect($workspace)->toBeInstanceOf(CampaignPlanningWorkspace::class)
        ->and($plan->campaign->name)->toBe('Integrated Campaign')
        ->and($workspace->get('planning-001'))->toBe($plan)
        ->and($workspace->all())->toHaveKey('planning-001')
        ->and($workspace->effects())->toMatchArray([
            'persists' => false,
            'uses_database' => false,
            'queues_jobs' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('updates schedules and archives stored plans through existing planning actions', function () {
    $workspace = campaignPlanningWorkspace();

    $workspace->create('planning-002', new CampaignPlanningInputData(name: 'Initial Campaign'));

    $updated = $workspace->update('planning-002', new CampaignPlanningInputData(
        name: 'Updated Campaign',
        description: 'Updated in memory',
    ));

    $scheduled = $workspace->schedule('planning-002', '2027-05-01T09:00:00+08:00');
    $archived = $workspace->archive('planning-002', 'operator cancelled draft');

    expect($updated->campaign->name)->toBe('Updated Campaign')
        ->and($workspace->get('planning-002'))->toBe($archived)
        ->and($scheduled->campaign->status)->toBe('scheduled')
        ->and($scheduled->campaign->scheduledAt)->toBe('2027-05-01T09:00:00+08:00')
        ->and($archived->campaign->status)->toBe('archived')
        ->and($archived->metadata['archive_reason'])->toBe('operator cancelled draft');
});

it('builds summaries from repository-backed planning state without exposing persistence', function () {
    $workspace = campaignPlanningWorkspace();

    $workspace->create('planning-summary', new CampaignPlanningInputData(name: 'Summary Campaign'));

    $summary = $workspace->summary('planning-summary');

    expect($summary)->not->toBeNull()
        ->and($summary->name)->toBe('Summary Campaign')
        ->and($summary->metadata['source'])->toBe('campaign-summary-read-model')
        ->and($summary->effects['persists'])->toBeFalse();
});

it('fails closed when mutating an unknown planning key', function () {
    $workspace = campaignPlanningWorkspace();

    expect(fn () => $workspace->update('missing-key', new CampaignPlanningInputData(name: 'Missing')))
        ->toThrow(InvalidArgumentException::class, 'Unknown campaign planning key [missing-key].');
});
