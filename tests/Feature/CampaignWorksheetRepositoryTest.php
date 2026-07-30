<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetImportRepository;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetIntakeRepository;
use LBHurtado\XCampaign\Contracts\CampaignWorksheetRepository;
use LBHurtado\XCampaign\Data\CampaignWorksheetData;
use LBHurtado\XCampaign\Data\CampaignWorksheetImportData;
use LBHurtado\XCampaign\Data\CampaignWorksheetIntakeData;
use LBHurtado\XCampaign\Data\CampaignWorksheetRowData;

beforeEach(function (): void {
    config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
});

it('stages an encrypted owner intake and converts selected valid rows atomically', function () {
    $this->artisan('migrate:fresh')->run();
    $intakes = app(CampaignWorksheetIntakeRepository::class);
    $staged = $intakes->stage(new CampaignWorksheetIntakeData(
        reference: null,
        ownerType: 'App\\Models\\User',
        ownerId: '5',
        status: 'staged',
        sourceName: 'july-payroll.csv',
        sourceFormat: 'csv',
        contentHash: hash('sha256', 'july-payroll'),
        rowCount: 2,
        sourceHeaders: ['mobile', 'amount'],
        sourceSheet: null,
        mapping: ['mobile' => 'mobile', 'amount' => 'amount'],
        suggestion: ['profile' => 'payroll', 'fulfillment_mode' => 'pay_code_distribution'],
        rows: [
            [
                'source_row' => 2,
                'status' => 'valid',
                'source' => ['mobile' => '09173011987', 'amount' => '100.00'],
                'normalized' => ['beneficiary' => ['mobile' => '09173011987'], 'amount_minor' => 10_000, 'currency' => 'PHP', 'delivery_preference' => 'sms'],
                'errors' => [],
            ],
            [
                'source_row' => 3,
                'status' => 'invalid',
                'source' => ['mobile' => '', 'amount' => '50.00'],
                'normalized' => null,
                'errors' => ['A mobile number is required.'],
            ],
        ],
    ));

    $ciphertext = DB::table('campaign_worksheet_intake_rows')->where('source_row', 2)->value('source_ciphertext');
    $converted = $intakes->convert(
        (string) $staged->reference,
        'App\\Models\\User',
        '5',
        new CampaignWorksheetData(null, 'App\\Models\\User', '5', 'payroll', 'July Payroll'),
        [2],
    );

    expect($ciphertext)->not->toContain('09173011987')
        ->and($converted->status)->toBe('draft')
        ->and($converted->rows)->toHaveCount(1)
        ->and($converted->rows[0]->amountMinor)->toBe(10_000)
        ->and($intakes->findForOwner((string) $staged->reference, 'App\\Models\\User', '5')?->status)
        ->toBe('converted')
        ->and($intakes->convert(
            (string) $staged->reference,
            'App\\Models\\User',
            '5',
            new CampaignWorksheetData(null, 'App\\Models\\User', '5', 'payroll', 'Duplicate'),
            [2],
        )->reference)->toBe($converted->reference);
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

it('deletes only an owner draft and cascades its private working data', function () {
    $this->artisan('migrate:fresh')->run();

    $worksheets = app(CampaignWorksheetRepository::class);
    $imports = app(CampaignWorksheetImportRepository::class);
    $draft = $worksheets->put(new CampaignWorksheetData(
        null,
        'App\\Models\\User',
        '5',
        'payroll',
        'Mistaken Import',
        rows: [new CampaignWorksheetRowData(null, 1, ['mobile' => '09173011987'], 10_000)],
    ));
    $imports->stage(new CampaignWorksheetImportData(
        null,
        (string) $draft->reference,
        'staged',
        'csv',
        hash('sha256', 'mistaken-import'),
        1,
        [],
        [],
        ['mobile' => 'mobile', 'amount' => 'amount'],
    ), 'App\\Models\\User', '5');

    expect(fn () => $worksheets->deleteDraft(
        (string) $draft->reference,
        'App\\Models\\User',
        '6',
    ))->toThrow(InvalidArgumentException::class);

    $worksheets->deleteDraft((string) $draft->reference, 'App\\Models\\User', '5');

    expect($worksheets->findForOwner((string) $draft->reference, 'App\\Models\\User', '5'))->toBeNull()
        ->and(DB::table('campaign_worksheet_rows')->count())->toBe(0)
        ->and(DB::table('campaign_worksheet_imports')->count())->toBe(0)
        ->and(DB::table('campaign_worksheet_import_rows')->count())->toBe(0);
});

it('refuses to delete a frozen or authorized worksheet', function () {
    $this->artisan('migrate:fresh')->run();

    $worksheets = app(CampaignWorksheetRepository::class);
    $worksheet = $worksheets->put(new CampaignWorksheetData(
        null,
        'App\\Models\\User',
        '5',
        'payroll',
        'Protected Payroll',
        rows: [new CampaignWorksheetRowData(null, 1, ['mobile' => '09173011987'], 10_000)],
    ));
    $worksheets->freeze((string) $worksheet->reference, 'App\\Models\\User', '5');

    expect(fn () => $worksheets->deleteDraft(
        (string) $worksheet->reference,
        'App\\Models\\User',
        '5',
    ))->toThrow(InvalidArgumentException::class, 'Only a draft campaign worksheet may be deleted.')
        ->and($worksheets->findForOwner(
            (string) $worksheet->reference,
            'App\\Models\\User',
            '5',
        ))->not->toBeNull();
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

it('stages encrypted source rows and applies only valid rows idempotently', function () {
    $this->artisan('migrate:fresh')->run();
    $worksheets = app(CampaignWorksheetRepository::class);
    $imports = app(CampaignWorksheetImportRepository::class);
    $worksheet = $worksheets->put(new CampaignWorksheetData(
        null,
        'App\\Models\\User',
        '5',
        'payroll',
        'Scalable Import',
    ));

    $staged = $imports->stage(new CampaignWorksheetImportData(
        reference: null,
        worksheetReference: (string) $worksheet->reference,
        status: 'staged',
        sourceFormat: 'xlsx',
        contentHash: hash('sha256', 'scalable-private-file'),
        rowCount: 2,
        validRows: [],
        validationErrors: [],
        mapping: ['mobile' => 'Phone', 'amount' => 'Salary'],
        stagedRows: [
            [
                'source_row' => 2,
                'status' => 'valid',
                'source' => ['Phone' => '09173011987', 'Salary' => '1250.00'],
                'normalized' => [
                    'beneficiary' => ['mobile' => '09173011987'],
                    'amount_minor' => 125_000,
                    'currency' => 'PHP',
                    'delivery_preference' => 'sms',
                ],
                'errors' => [],
            ],
            [
                'source_row' => 3,
                'status' => 'invalid',
                'source' => ['Phone' => '', 'Salary' => '100.00'],
                'normalized' => null,
                'errors' => ['A mobile number or bank account is required.'],
            ],
        ],
        sourceHeaders: ['Phone', 'Salary'],
        sourceSheet: 'Payroll',
    ), 'App\\Models\\User', '5');

    $sourceCiphertext = DB::table('campaign_worksheet_import_rows')
        ->where('source_row', 2)
        ->value('source_ciphertext');
    $applied = $imports->apply(
        (string) $worksheet->reference,
        (string) $staged->reference,
        'App\\Models\\User',
        '5',
    );

    expect($sourceCiphertext)->not->toContain('09173011987')
        ->and($applied->status)->toBe('applied_with_errors')
        ->and($applied->validationErrors)->toHaveCount(1)
        ->and($worksheets->findForOwner(
            (string) $worksheet->reference,
            'App\\Models\\User',
            '5',
        )?->rows)->toHaveCount(1)
        ->and(fn () => $imports->apply(
            (string) $worksheet->reference,
            (string) $staged->reference,
            'App\\Models\\User',
            '5',
        ))->toThrow(InvalidArgumentException::class);
});

it('revalidates only unapplied import rows and may apply the remainder once', function () {
    $this->artisan('migrate:fresh')->run();
    $worksheets = app(CampaignWorksheetRepository::class);
    $imports = app(CampaignWorksheetImportRepository::class);
    $worksheet = $worksheets->put(new CampaignWorksheetData(
        null,
        'App\\Models\\User',
        '5',
        'payroll',
        'Remapped Import',
    ));
    $staged = $imports->stage(new CampaignWorksheetImportData(
        null,
        (string) $worksheet->reference,
        'staged',
        'csv',
        hash('sha256', 'remap'),
        1,
        [],
        [],
        ['amount' => 'Salary'],
        stagedRows: [[
            'source_row' => 2,
            'status' => 'invalid',
            'source' => ['Phone' => '09173011987', 'Salary' => '50.00'],
            'normalized' => null,
            'errors' => ['A mobile number or bank account is required.'],
        ]],
    ), 'App\\Models\\User', '5');

    $remapped = $imports->replaceUnappliedRows(
        (string) $worksheet->reference,
        (string) $staged->reference,
        'App\\Models\\User',
        '5',
        ['mobile' => 'Phone', 'amount' => 'Salary'],
        [[
            'source_row' => 2,
            'status' => 'valid',
            'source' => ['Phone' => '09173011987', 'Salary' => '50.00'],
            'normalized' => [
                'beneficiary' => ['mobile' => '09173011987'],
                'amount_minor' => 5_000,
                'currency' => 'PHP',
                'delivery_preference' => 'manual',
            ],
            'errors' => [],
        ]],
    );
    $applied = $imports->apply(
        (string) $worksheet->reference,
        (string) $staged->reference,
        'App\\Models\\User',
        '5',
    );

    expect($remapped->validRows)->toHaveCount(1)
        ->and($applied->status)->toBe('applied')
        ->and($worksheets->findForOwner(
            (string) $worksheet->reference,
            'App\\Models\\User',
            '5',
        )?->rows)->toHaveCount(1);
});

it('freezes a non-empty draft into an immutable owner-scoped manifest', function () {
    $this->artisan('migrate:fresh')->run();
    $repository = app(CampaignWorksheetRepository::class);
    $worksheet = $repository->put(new CampaignWorksheetData(null, 'App\\Models\\User', '5', 'payroll', 'Freeze', rows: [
        new CampaignWorksheetRowData(null, 1, ['mobile' => '09173011987'], 1_000),
    ]));
    $frozen = $repository->freeze((string) $worksheet->reference, 'App\\Models\\User', '5');

    expect($frozen->status)->toBe('awaiting_authorization')
        ->and($frozen->rowsHash)->not->toBeNull()
        ->and($frozen->frozenAt)->not->toBeNull()
        ->and(fn () => $repository->appendRow((string) $worksheet->reference, 'App\\Models\\User', '5', new CampaignWorksheetRowData(null, 2, ['mobile' => '09170000000'], 1_000)))
        ->toThrow(InvalidArgumentException::class);
});

it('keeps staged import history available after a worksheet is frozen', function () {
    $this->artisan('migrate:fresh')->run();
    $worksheets = app(CampaignWorksheetRepository::class);
    $imports = app(CampaignWorksheetImportRepository::class);
    $worksheet = $worksheets->put(new CampaignWorksheetData(null, 'App\\Models\\User', '5', 'payroll', 'Import History', rows: [
        new CampaignWorksheetRowData(null, 1, ['mobile' => '09173011987'], 1_000),
    ]));

    $staged = $imports->stage(new CampaignWorksheetImportData(
        null, (string) $worksheet->reference, 'staged', 'csv', hash('sha256', 'history-file'), 1,
        [['beneficiary' => ['mobile' => '09173011987'], 'amount_minor' => 1_000]], [], ['mobile' => 'mobile', 'amount' => 'amount'],
    ), 'App\\Models\\User', '5');
    $worksheets->freeze((string) $worksheet->reference, 'App\\Models\\User', '5');

    expect($imports->forOwner((string) $worksheet->reference, 'App\\Models\\User', '5'))
        ->toHaveCount(1)
        ->and($imports->forOwner((string) $worksheet->reference, 'App\\Models\\User', '5')[0]->reference)
        ->toBe($staged->reference);
});
