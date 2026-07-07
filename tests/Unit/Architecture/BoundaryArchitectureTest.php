<?php

declare(strict_types=1);

it('keeps phase zero free of execution and notification package dependencies', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/**/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/**/**/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('LBHurtado\\XChange')
        ->not->toContain('LBHurtado\\XFeedback')
        ->not->toContain('LBHurtado\\XJournal')
        ->not->toContain('LBHurtado\\XAction')
        ->not->toContain('LBHurtado\\Voucher')
        ->not->toContain('Bavix\\Wallet')
        ->not->toContain('Netbank')
        ->not->toContain('Paynamics');
});

it('does not scaffold routes controllers jobs migrations or execution owners in phase zero', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/routes'))->toBeFalse()
        ->and(is_dir($root.'/database/migrations'))->toBeFalse()
        ->and(is_dir($root.'/src/Http/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Jobs'))->toBeFalse()
        ->and(is_dir($root.'/src/Execution'))->toBeFalse()
        ->and(is_dir($root.'/src/Payments'))->toBeFalse()
        ->and(is_dir($root.'/src/Wallets'))->toBeFalse();
});

it('does not introduce campaign execution side effects in phase one a', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/**/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/**/**/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('dispatch(')
        ->not->toContain('Mail::')
        ->not->toContain('Notification::')
        ->not->toContain('Http::')
        ->not->toContain('DB::transaction')
        ->not->toContain('redeem')
        ->not->toContain('disburse')
        ->not->toContain('withdraw');
});

it('keeps phase one b planning actions free of persistence transport and execution calls', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/Actions/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/Services/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('Model::')
        ->not->toContain('save(')
        ->not->toContain('create(')
        ->not->toContain('update(')
        ->not->toContain('delete(')
        ->not->toContain('dispatch(')
        ->not->toContain('Mail::')
        ->not->toContain('Notification::')
        ->not->toContain('Http::')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Wallet');
});

it('keeps phase one c audience recipient planning free of imports persistence and transport', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/Actions/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/Data/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('save(')
        ->not->toContain('dispatch(')
        ->not->toContain('Mail::')
        ->not->toContain('Notification::')
        ->not->toContain('Http::')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Wallet');
});

it('keeps phase one d execution planning free of queues issuance delivery and persistence', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/Actions/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/Data/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('ShouldQueue')
        ->not->toContain('Bus::')
        ->not->toContain('dispatch(')
        ->not->toContain('Queue::')
        ->not->toContain('save(')
        ->not->toContain('DB::')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Http::')
        ->not->toContain('Wallet');
});

it('keeps phase one e read models free of routes persistence execution delivery and journal ownership', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/ReadModels/*.php') ?: [],
        ...glob(__DIR__.'/../../../src/Data/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('Route::')
        ->not->toContain('Controller')
        ->not->toContain('extends Model')
        ->not->toContain('Eloquent')
        ->not->toContain('save(')
        ->not->toContain('DB::')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('ExecutionEngine')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one f repositories in memory without migrations queues issuance delivery or journal ownership', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/Repositories/*.php') ?: [],
        __DIR__.'/../../../src/Contracts/CampaignPlanRepository.php',
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect($source)
        ->not->toContain('extends Model')
        ->not->toContain('Eloquent')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('create(')
        ->not->toContain('update(')
        ->not->toContain('delete(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one g repository integration free of migrations queues issuance delivery and journal ownership', function () {
    $source = collect([
        ...glob(__DIR__.'/../../../src/Workspaces/*.php') ?: [],
        __DIR__.'/../../../src/Contracts/CampaignPlanningWorkspace.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('extends Model')
        ->not->toContain('Eloquent')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one h audience import planning free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/PlanCampaignAudienceImport.php',
        __DIR__.'/../../../src/Contracts/PlansCampaignAudienceImports.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportPlanningInputData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportPlanData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one i audience import workspace integration free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/Workspaces/RepositoryBackedCampaignAudienceImportWorkspace.php',
        __DIR__.'/../../../src/Contracts/CampaignAudienceImportWorkspace.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one j recipient import row planning free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/PlanCampaignRecipientImportRow.php',
        __DIR__.'/../../../src/Contracts/PlansCampaignRecipientImportRows.php',
        __DIR__.'/../../../src/Data/CampaignRecipientImportRowPlanningInputData.php',
        __DIR__.'/../../../src/Data/CampaignRecipientImportRowData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one k recipient import row workspace integration free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/Workspaces/RepositoryBackedCampaignRecipientImportRowWorkspace.php',
        __DIR__.'/../../../src/Contracts/CampaignRecipientImportRowWorkspace.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one l audience import row collection planning free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/PlanCampaignAudienceImportRowCollection.php',
        __DIR__.'/../../../src/Contracts/PlansCampaignAudienceImportRowCollections.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRowCollectionPlanningInputData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRowCollectionPlanData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one m audience import review summaries free of file parsing persistence queues delivery and issuance', function () {
    $source = collect([
        __DIR__.'/../../../src/ReadModels/CampaignAudienceImportReviewSummaryReadModel.php',
        __DIR__.'/../../../src/Contracts/BuildsCampaignAudienceImportReviewSummaries.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportReviewSummaryData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportReviewRowIssueData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet');
});

it('keeps phase one n audience import approval decisions free of persistence queues delivery issuance and audience mutation', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/DecideCampaignAudienceImportApproval.php',
        __DIR__.'/../../../src/Contracts/DecidesCampaignAudienceImportApprovals.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportApprovalDecisionInputData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportApprovalDecisionData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet')
        ->not->toContain('AddRecipient')
        ->not->toContain('RemoveRecipient');
});

it('keeps phase one o audience import approval workspace free of persistence queues delivery issuance and audience mutation', function () {
    $source = collect([
        __DIR__.'/../../../src/Workspaces/RepositoryBackedCampaignAudienceImportApprovalWorkspace.php',
        __DIR__.'/../../../src/Contracts/CampaignAudienceImportApprovalWorkspace.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportApprovalWorkspaceInputData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportApprovalWorkspaceResultData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet')
        ->not->toContain('AddRecipient')
        ->not->toContain('RemoveRecipient');
});

it('keeps phase one p audience import recipient attachment planning free of persistence queues delivery issuance and audience mutation', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/PlanCampaignAudienceImportRecipientAttachments.php',
        __DIR__.'/../../../src/Contracts/PlansCampaignAudienceImportRecipientAttachments.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRecipientAttachmentPlanData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet')
        ->not->toContain('AddRecipient')
        ->not->toContain('RemoveRecipient');
});

it('keeps phase one q audience import recipient attachment workspace free of persistence queues delivery issuance and audience mutation', function () {
    $source = collect([
        __DIR__.'/../../../src/Workspaces/RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace.php',
        __DIR__.'/../../../src/Contracts/CampaignAudienceImportRecipientAttachmentWorkspace.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRecipientAttachmentWorkspaceResultData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet')
        ->not->toContain('AddRecipient')
        ->not->toContain('RemoveRecipient');
});

it('keeps phase one r recipient attachment mutation decisions free of persistence queues delivery issuance and audience mutation', function () {
    $source = collect([
        __DIR__.'/../../../src/Actions/DecideCampaignAudienceImportRecipientAttachmentMutation.php',
        __DIR__.'/../../../src/Contracts/DecidesCampaignAudienceImportRecipientAttachmentMutations.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRecipientAttachmentMutationDecisionInputData.php',
        __DIR__.'/../../../src/Data/CampaignAudienceImportRecipientAttachmentMutationDecisionData.php',
    ])->filter(fn (string $file): bool => is_file($file))
        ->map(fn (string $file): string => file_get_contents($file) ?: '')
        ->implode("\n");

    expect($source)
        ->not->toContain('UploadedFile')
        ->not->toContain('SplFileObject')
        ->not->toContain('Storage::')
        ->not->toContain('fopen(')
        ->not->toContain('file_get_contents(')
        ->not->toContain('str_getcsv')
        ->not->toContain('League\\Csv')
        ->not->toContain('DB::')
        ->not->toContain('Schema::')
        ->not->toContain('Migration')
        ->not->toContain('save(')
        ->not->toContain('ShouldQueue')
        ->not->toContain('dispatch(')
        ->not->toContain('PayCode')
        ->not->toContain('Voucher')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Journal')
        ->not->toContain('Wallet')
        ->not->toContain('AddRecipient')
        ->not->toContain('RemoveRecipient');
});
