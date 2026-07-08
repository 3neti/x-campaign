<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign;

use Illuminate\Support\ServiceProvider;
use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\ArchiveCampaignPlan;
use LBHurtado\XCampaign\Actions\AttachCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportApproval;
use LBHurtado\XCampaign\Actions\DecideCampaignAudienceImportRecipientAttachmentMutation;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImport;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Actions\PlanCampaignAudienceImportRowCollection;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Actions\PlanCampaignRecipientImportRow;
use LBHurtado\XCampaign\Actions\RemoveRecipientFromCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\ScheduleCampaignPlan;
use LBHurtado\XCampaign\Actions\UpdateCampaignPlan;
use LBHurtado\XCampaign\Contracts\AddsAudiencesToCampaignPlans;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\AttachesCampaignAudienceImportRecipientsInMemory;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorReadModels;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAudienceImportReviewSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Contracts\CampaignPlanningWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;
use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;
use LBHurtado\XCampaign\Repositories\EloquentCampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Services\CampaignStateGrammar;
use LBHurtado\XCampaign\Services\ConfigCampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPlanningWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignRecipientImportRowWorkspace;

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
        $this->app->singleton(CreatesCampaignPlans::class, CreateCampaignPlan::class);
        $this->app->singleton(UpdatesCampaignPlans::class, UpdateCampaignPlan::class);
        $this->app->singleton(SchedulesCampaignPlans::class, ScheduleCampaignPlan::class);
        $this->app->singleton(ArchivesCampaignPlans::class, ArchiveCampaignPlan::class);
        $this->app->singleton(AddsAudiencesToCampaignPlans::class, AddAudienceToCampaignPlan::class);
        $this->app->singleton(AddsRecipientsToCampaignAudiencePlans::class, AddRecipientToCampaignAudiencePlan::class);
        $this->app->singleton(AttachesCampaignAudienceImportRecipientsInMemory::class, AttachCampaignAudienceImportRecipientsInMemory::class);
        $this->app->singleton(RemovesRecipientsFromCampaignAudiencePlans::class, RemoveRecipientFromCampaignAudiencePlan::class);
        $this->app->singleton(DecidesCampaignAudienceImportApprovals::class, DecideCampaignAudienceImportApproval::class);
        $this->app->singleton(DecidesCampaignAudienceImportRecipientAttachmentMutations::class, DecideCampaignAudienceImportRecipientAttachmentMutation::class);
        $this->app->singleton(PlansCampaignAudienceImportRecipientAttachments::class, PlanCampaignAudienceImportRecipientAttachments::class);
        $this->app->singleton(PlansCampaignAudienceImports::class, PlanCampaignAudienceImport::class);
        $this->app->singleton(PlansCampaignAudienceImportRowCollections::class, PlanCampaignAudienceImportRowCollection::class);
        $this->app->singleton(PlansCampaignRecipientImportRows::class, PlanCampaignRecipientImportRow::class);
        $this->app->singleton(PlansCampaignExecutions::class, PlanCampaignExecution::class);
        $this->app->singleton(PlansCampaignExecutionBatches::class, PlanCampaignExecutionBatches::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorReadModels::class, CampaignAudienceImportAttachmentOperatorReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries::class, CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections::class, CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries::class, CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportReviewSummaries::class, CampaignAudienceImportReviewSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignSummaries::class, CampaignSummaryReadModel::class);
        $this->app->singleton(CampaignPlanRepository::class, InMemoryCampaignPlanRepository::class);
        $this->app->singleton(CampaignPlanSnapshotRepository::class, EloquentCampaignPlanSnapshotRepository::class);
        $this->app->singleton(CampaignPlanningWorkspace::class, RepositoryBackedCampaignPlanningWorkspace::class);
        $this->app->singleton(CampaignAudienceImportAttachmentOperatorWorkspace::class, RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace::class);
        $this->app->singleton(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class, RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class);
        $this->app->singleton(CampaignAudienceImportApprovalWorkspace::class, RepositoryBackedCampaignAudienceImportApprovalWorkspace::class);
        $this->app->singleton(CampaignAudienceImportRecipientAttachmentMutationWorkspace::class, RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace::class);
        $this->app->singleton(CampaignAudienceImportRecipientAttachmentWorkspace::class, RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace::class);
        $this->app->singleton(CampaignAudienceImportWorkspace::class, RepositoryBackedCampaignAudienceImportWorkspace::class);
        $this->app->singleton(CampaignRecipientImportRowWorkspace::class, RepositoryBackedCampaignRecipientImportRowWorkspace::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/x-campaign.php' => config_path('x-campaign.php'),
            ], 'x-campaign-config');
        }
    }
}
