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
