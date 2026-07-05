<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign;

use Illuminate\Support\ServiceProvider;
use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Services\CampaignStateGrammar;
use LBHurtado\XCampaign\Services\ConfigCampaignFeatureProfileResolver;

class XCampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/x-campaign.php', 'x-campaign');

        $this->app->singleton(
            CampaignFeatureProfileResolver::class,
            ConfigCampaignFeatureProfileResolver::class,
        );

        $this->app->singleton(CampaignStateGrammar::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/x-campaign.php' => config_path('x-campaign.php'),
            ], 'x-campaign-config');
        }
    }
}
