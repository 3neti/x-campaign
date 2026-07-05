<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Tests;

use LBHurtado\XCampaign\XCampaignServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            XCampaignServiceProvider::class,
        ];
    }
}
