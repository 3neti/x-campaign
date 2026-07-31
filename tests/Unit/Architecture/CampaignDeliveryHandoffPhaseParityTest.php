<?php

declare(strict_types=1);

it('documents the phase six delivery handoff as-built architecture', function () {
    $document = file_get_contents(__DIR__.'/../../../docs/x-campaign-architecture.md') ?: '';

    expect($document)
        ->toContain('## Phase 6A Boundary')
        ->toContain('## Phase 6B Boundary')
        ->toContain('## Phase 6C Boundary')
        ->toContain('## Phase 6D Boundary')
        ->toContain('## Phase 6E Boundary')
        ->toContain('## Phase 6F Boundary')
        ->toContain('delivery handoff read model');
});

it('keeps phase six handoff architecture free of delivery, execution, and settlement side effects', function () {
    $root = realpath(__DIR__.'/../../..');
    $sourceFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src')));
    $source = '';
    $orchestrationSource = '';

    foreach ($sourceFiles as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $source .= file_get_contents($file->getPathname()) ?: '';

            if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Repositories'.DIRECTORY_SEPARATOR)) {
                $orchestrationSource .= file_get_contents($file->getPathname()) ?: '';
            }
        }
    }

    expect($source)
        ->not->toContain('LBHurtado\\XFeedback')
        ->not->toContain('LBHurtado\\XChange')
        ->not->toContain('LBHurtado\\Voucher')
        ->not->toContain('Bavix\\Wallet')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Http::')
        ->not->toContain('dispatchSync(');

    expect($orchestrationSource)->not->toContain('DB::transaction');
});
