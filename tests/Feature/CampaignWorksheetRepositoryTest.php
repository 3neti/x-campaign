<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetRowData;

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

it('persists an encrypted owner-scoped campaign worksheet without execution side effects', function () {
    $this->artisan('migrate:fresh')->run();

    $repository = app(CampaignWorksheetRepository::class);
    $stored = $repository->put(new CampaignWorksheetData(
        reference: null,
        ownerType: 'App\\Models\\User',
        ownerId: '5',
        profile: 'payroll',
        name: 'July Payroll',
        deliveryPlan: ['csv', 'sms'],
        rows: [
            new CampaignWorksheetRowData(
                reference: null,
                ordinal: 1,
                beneficiary: [
                    'name' => 'Maria Santos',
                    'mobile' => '09173011987',
                    'bank_account' => '113-001-00001-9',
                    'email' => 'maria@example.test',
                    'remarks' => 'Payroll July',
                ],
                amountMinor: 125000,
                deliveryPreference: 'sms',
            ),
        ],
    ));

    $ciphertext = DB::table('campaign_worksheet_rows')->value('beneficiary_ciphertext');
    $retrieved = $repository->findForOwner(
        (string) $stored->reference,
        'App\\Models\\User',
        '5',
    );

    expect(Schema::hasTable('campaign_worksheets'))->toBeTrue()
        ->and(Schema::hasTable('campaign_worksheet_rows'))->toBeTrue()
        ->and($stored->reference)->not->toBeNull()
        ->and($stored->rows)->toHaveCount(1)
        ->and($retrieved?->rows[0]->beneficiary['mobile'])->toBe('09173011987')
        ->and($retrieved?->rows[0]->amountMinor)->toBe(125000)
        ->and($ciphertext)->not->toContain('09173011987')
        ->and($ciphertext)->not->toContain('113-001-00001-9');

    $summary = $repository->summariesForOwner('App\\Models\\User', '5');

    expect($summary)->toHaveCount(1)
        ->and($summary[0]->beneficiaryCount)->toBe(1)
        ->and($summary[0]->principalMinor)->toBe(125000)
        ->and($summary[0]->name)->toBe('July Payroll');
});

it('rejects unsupported worksheet profiles and non-positive row amounts', function () {
    $this->artisan('migrate:fresh')->run();

    $repository = app(CampaignWorksheetRepository::class);

    expect(fn () => $repository->put(new CampaignWorksheetData(
        reference: null,
        ownerType: 'App\\Models\\User',
        ownerId: '5',
        profile: 'unknown',
        name: 'Invalid',
    )))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $repository->put(new CampaignWorksheetData(
            reference: null,
            ownerType: 'App\\Models\\User',
            ownerId: '5',
            profile: 'assistance',
            name: 'Invalid amount',
            rows: [new CampaignWorksheetRowData(null, 1, [], 0)],
        )))->toThrow(InvalidArgumentException::class);
});
