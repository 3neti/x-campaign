<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowData;
use LBHurtado\XCampaign\Data\CampaignRecipientImportRowPlanningInputData;
use LBHurtado\XCampaign\Data\CampaignRecipientPlanningInputData;

it('normalizes a raw recipient import row without importing or persisting recipients', function () {
    $planned = (new PlanCampaignRecipientImportRow)->handle(new CampaignRecipientImportRowPlanningInputData(
        importId: 'import-001',
        audienceId: 'audience-001',
        rowNumber: 7,
        row: [
            'id' => ' recipient-001 ',
            'name' => '  Juan Dela Cruz  ',
            'mobile' => ' +63 917 000 0001 ',
            'email' => ' JUAN@EXAMPLE.TEST ',
            'address' => ' Manila ',
            'external_reference' => ' EXT-001 ',
            'ignored' => 'keep in raw only',
        ],
    ));

    expect(new PlanCampaignRecipientImportRow)->toBeInstanceOf(PlansCampaignRecipientImportRows::class)
        ->and($planned)->toBeInstanceOf(CampaignRecipientImportRowData::class)
        ->and($planned->importId)->toBe('import-001')
        ->and($planned->audienceId)->toBe('audience-001')
        ->and($planned->rowNumber)->toBe(7)
        ->and($planned->status)->toBe('valid')
        ->and($planned->recipient)->toBeInstanceOf(CampaignRecipientPlanningInputData::class)
        ->and($planned->recipient->id)->toBe('recipient-001')
        ->and($planned->recipient->name)->toBe('Juan Dela Cruz')
        ->and($planned->recipient->mobile)->toBe('+639170000001')
        ->and($planned->recipient->email)->toBe('juan@example.test')
        ->and($planned->recipient->address)->toBe('Manila')
        ->and($planned->recipient->externalReference)->toBe('EXT-001')
        ->and($planned->raw['ignored'])->toBe('keep in raw only')
        ->and($planned->errors)->toBe([])
        ->and($planned->effects)->toMatchArray([
            'parses_files' => false,
            'persists' => false,
            'queues_jobs' => false,
            'adds_recipients' => false,
            'issues_pay_codes' => false,
            'sends_feedback' => false,
            'writes_journal' => false,
            'moves_money' => false,
        ]);
});

it('marks rows invalid when name and contact fields are missing', function () {
    $planned = (new PlanCampaignRecipientImportRow)->handle(new CampaignRecipientImportRowPlanningInputData(
        importId: 'import-002',
        audienceId: 'audience-001',
        rowNumber: 8,
        row: [
            'external_reference' => 'EXT-002',
        ],
    ));

    expect($planned->status)->toBe('invalid')
        ->and($planned->errors)->toBe([
            'name' => 'Recipient name is required.',
            'contact' => 'At least one recipient contact field is required.',
        ])
        ->and($planned->recipient->externalReference)->toBe('EXT-002');
});

it('supports common column aliases for recipient import rows', function () {
    $planned = (new PlanCampaignRecipientImportRow)->handle(new CampaignRecipientImportRowPlanningInputData(
        importId: 'import-003',
        audienceId: 'audience-001',
        rowNumber: 9,
        row: [
            'full_name' => 'Maria Santos',
            'phone' => '0917 123 4567',
            'external_id' => 'EXT-003',
        ],
    ));

    expect($planned->status)->toBe('valid')
        ->and($planned->recipient->name)->toBe('Maria Santos')
        ->and($planned->recipient->mobile)->toBe('09171234567')
        ->and($planned->recipient->externalReference)->toBe('EXT-003');
});
