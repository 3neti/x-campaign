<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\RemoveRecipientFromCampaignAudiencePlan;
use LBHurtado\XCampaign\Contracts\AddsAudiencesToCampaignPlans;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanData;
use LBHurtado\XCampaign\Data\CampaignAudiencePlanningInputData;
use LBHurtado\XCampaign\Data\CampaignPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

it('adds an audience to a campaign plan in memory', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(
        name: 'Educational Assistance 2027',
    ));

    $planned = (new AddAudienceToCampaignPlan)->handle($plan, new CampaignAudiencePlanningInputData(
        id: 'audience-scholars',
        name: 'All Scholars',
        metadata: ['source' => 'planning-list'],
    ));

    expect(new AddAudienceToCampaignPlan)->toBeInstanceOf(AddsAudiencesToCampaignPlans::class)
        ->and($planned->audiences)->toHaveCount(1)
        ->and($planned->audiences[0])->toBeInstanceOf(CampaignAudiencePlanData::class)
        ->and($planned->audiences[0]->audience->id)->toBe('audience-scholars')
        ->and($planned->audiences[0]->audience->name)->toBe('All Scholars')
        ->and($planned->audiences[0]->audience->status)->toBe('draft')
        ->and($planned->audiences[0]->recipients)->toBe([])
        ->and($planned->effects['persists'])->toBeFalse()
        ->and($planned->effects['imports_files'])->toBeFalse()
        ->and($planned->effects['sends_feedback'])->toBeFalse()
        ->and($planned->metadata['audience_planning'])->toBe('in-memory');
});

it('adds a recipient to a campaign audience plan without importing files or sending messages', function () {
    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Payroll Batch 2027-01')),
        new CampaignAudiencePlanningInputData(id: 'audience-payroll-a', name: 'Payroll Group A'),
    );

    $planned = (new AddRecipientToCampaignAudiencePlan)->handle($plan, 'audience-payroll-a', new CampaignRecipientPlanningInputData(
        name: 'Maria Santos',
        mobile: '  +63 917 123 4567 ',
        email: ' MARIA@EXAMPLE.COM ',
        externalReference: ' employee-001 ',
        metadata: ['department' => 'operations'],
    ));

    expect(new AddRecipientToCampaignAudiencePlan)->toBeInstanceOf(AddsRecipientsToCampaignAudiencePlans::class)
        ->and($planned->audiences[0]->recipients)->toHaveCount(1)
        ->and($planned->audiences[0]->recipients[0]->name)->toBe('Maria Santos')
        ->and($planned->audiences[0]->recipients[0]->mobile)->toBe('+639171234567')
        ->and($planned->audiences[0]->recipients[0]->email)->toBe('maria@example.com')
        ->and($planned->audiences[0]->recipients[0]->externalReference)->toBe('employee-001')
        ->and($planned->audiences[0]->recipients[0]->metadata)->toBe(['department' => 'operations'])
        ->and($planned->effects['persists'])->toBeFalse()
        ->and($planned->effects['imports_files'])->toBeFalse()
        ->and($planned->effects['sends_feedback'])->toBeFalse()
        ->and($planned->effects['issues_pay_codes'])->toBeFalse();
});

it('removes a recipient from an audience plan in memory without deleting persisted records', function () {
    $plan = (new AddAudienceToCampaignPlan)->handle(
        (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Relief Campaign')),
        new CampaignAudiencePlanningInputData(id: 'audience-relief', name: 'Relief Beneficiaries'),
    );

    $withRecipient = (new AddRecipientToCampaignAudiencePlan)->handle($plan, 'audience-relief', new CampaignRecipientPlanningInputData(
        name: 'Juan Dela Cruz',
        mobile: '+639181234567',
        externalReference: 'beneficiary-001',
    ));

    $withoutRecipient = (new RemoveRecipientFromCampaignAudiencePlan)->handle(
        $withRecipient,
        'audience-relief',
        'beneficiary-001',
    );

    expect(new RemoveRecipientFromCampaignAudiencePlan)->toBeInstanceOf(RemovesRecipientsFromCampaignAudiencePlans::class)
        ->and($withRecipient->audiences[0]->recipients)->toHaveCount(1)
        ->and($withoutRecipient->audiences[0]->recipients)->toBe([])
        ->and($withoutRecipient->effects['persists'])->toBeFalse()
        ->and($withoutRecipient->effects['deletes_records'])->toBeFalse()
        ->and($withoutRecipient->metadata['recipient_removed_in_memory'])->toBeTrue();
});

it('fails closed when adding a recipient to an unknown audience plan', function () {
    $plan = (new CreateCampaignPlan)->handle(new CampaignPlanningInputData(name: 'Missing Audience Test'));

    expect(fn () => (new AddRecipientToCampaignAudiencePlan)->handle(
        $plan,
        'missing-audience',
        new CampaignRecipientPlanningInputData(name: 'No Audience'),
    ))->toThrow(InvalidArgumentException::class, 'Unknown campaign audience plan [missing-audience].');
});
