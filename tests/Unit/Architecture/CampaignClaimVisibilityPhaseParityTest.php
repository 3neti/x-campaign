<?php

declare(strict_types=1);

it('documents the phase seven claim visibility as-built architecture', function () {
    $document = file_get_contents(__DIR__.'/../../../docs/x-campaign-architecture.md') ?: '';

    expect($document)
        ->toContain('## Phase 7A Boundary')
        ->toContain('## Phase 7B Boundary')
        ->toContain('## Phase 7C Boundary')
        ->toContain('## Phase 7D Boundary')
        ->toContain('## Phase 7E Boundary')
        ->toContain('## Phase 7F Boundary')
        ->toContain('claim visibility read model');
});

it('keeps phase seven visibility architecture free of claim runtime and settlement side effects', function () {
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
        ->not->toContain('LBHurtado\\XChange')
        ->not->toContain('LBHurtado\\Voucher')
        ->not->toContain('Bavix\\Wallet')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Http::')
        ->not->toContain('dispatchSync(');

    expect($orchestrationSource)->not->toContain('DB::transaction');
});
