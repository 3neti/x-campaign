<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetImportRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetImportData;
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

it('appends encrypted beneficiaries only while the owner worksheet is a draft', function () {
    $this->artisan('migrate:fresh')->run();

    $repository = app(CampaignWorksheetRepository::class);
    $worksheet = $repository->put(new CampaignWorksheetData(
        reference: null,
        ownerType: 'App\\Models\\User',
        ownerId: '5',
        profile: 'assistance',
        name: 'Emergency Assistance',
    ));

    $updated = $repository->appendRow(
        (string) $worksheet->reference,
        'App\\Models\\User',
        '5',
        new CampaignWorksheetRowData(
            reference: null,
            ordinal: 0,
            beneficiary: ['mobile' => '09173011987', 'remarks' => 'July request'],
            amountMinor: 5_000,
            deliveryPreference: 'manual',
        ),
    );

    expect($updated->rows)->toHaveCount(1)
        ->and($updated->rows[0]->ordinal)->toBe(1)
        ->and($updated->rows[0]->beneficiary['mobile'])->toBe('09173011987');

    DB::table('campaign_worksheets')
        ->where('reference', $worksheet->reference)
        ->update(['status' => 'frozen']);

    expect(fn () => $repository->appendRow(
        (string) $worksheet->reference,
        'App\\Models\\User',
        '5',
        new CampaignWorksheetRowData(null, 0, ['mobile' => '09179999999'], 1_000),
    ))->toThrow(InvalidArgumentException::class);
});

it('stages encrypted import rows and applies a valid import only once', function () {
    $this->artisan('migrate:fresh')->run();
    $worksheets = app(CampaignWorksheetRepository::class);
    $imports = app(CampaignWorksheetImportRepository::class);
    $worksheet = $worksheets->put(new CampaignWorksheetData(null, 'App\\Models\\User', '5', 'payroll', 'July Import'));

    $staged = $imports->stage(new CampaignWorksheetImportData(
        null, (string) $worksheet->reference, 'staged', 'csv', hash('sha256', 'private-file'), 1,
        [['beneficiary' => ['mobile' => '09173011987'], 'amount_minor' => 125_000, 'currency' => 'PHP', 'delivery_preference' => 'sms']], [], ['mobile' => 'mobile', 'amount' => 'amount'],
    ), 'App\\Models\\User', '5');

    $ciphertext = DB::table('campaign_worksheet_imports')->value('rows_ciphertext');
    $applied = $imports->apply((string) $worksheet->reference, (string) $staged->reference, 'App\\Models\\User', '5');

    expect($ciphertext)->not->toContain('09173011987')
        ->and($applied->status)->toBe('applied')
        ->and($worksheets->findForOwner((string) $worksheet->reference, 'App\\Models\\User', '5')?->rows)->toHaveCount(1)
        ->and(fn () => $imports->apply((string) $worksheet->reference, (string) $staged->reference, 'App\\Models\\User', '5'))
        ->toThrow(InvalidArgumentException::class);
});
