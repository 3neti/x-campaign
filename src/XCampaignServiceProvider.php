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
use LBHurtado\XCampaign\Actions\PlanCampaignClaimVisibility;
use LBHurtado\XCampaign\Actions\PlanCampaignDeliveryHandoff;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionHandoff;
use LBHurtado\XCampaign\Actions\PlanCampaignPortableCodeGeneration;
use LBHurtado\XCampaign\Actions\PlanCampaignQueueDispatch;
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
use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsOperatorSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignAnalyticsSnapshots;
use LBHurtado\XCampaign\Contracts\BuildsCampaignClaimVisibilitySummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignCockpitSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignDeliveryHandoffSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignExecutionHandoffSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignOperatorReports;
use LBHurtado\XCampaign\Contracts\BuildsCampaignPortableCodeGenerationSummaries;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignClaimStatusProvider;
use LBHurtado\XCampaign\Contracts\CampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignCockpitWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignExecutionHandoffWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Contracts\CampaignPlanRepository;
use LBHurtado\XCampaign\Contracts\CampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Contracts\CampaignPlanningWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignPortableCodeGenerationWorkspace;
use LBHurtado\XCampaign\Contracts\CampaignRecipientImportRowWorkspace;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportApprovals;
use LBHurtado\XCampaign\Contracts\DecidesCampaignAudienceImportRecipientAttachmentMutations;
use LBHurtado\XCampaign\Contracts\DispatchesCampaignQueuedPlans;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToAnalyticsSnapshots;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToClaimVisibilities;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToDeliveryHandoffs;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExecutionHandoffs;
use LBHurtado\XCampaign\Contracts\MapsCampaignQueuedPayloadsToExportHandoffs;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRowCollections;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImportRecipientAttachments;
use LBHurtado\XCampaign\Contracts\PlansCampaignAudienceImports;
use LBHurtado\XCampaign\Contracts\PlansCampaignClaimVisibilities;
use LBHurtado\XCampaign\Contracts\PlansCampaignDeliveryHandoffs;
use LBHurtado\XCampaign\Contracts\PlansCampaignExportHandoffs;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionHandoffs;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;
use LBHurtado\XCampaign\Contracts\PlansCampaignPortableCodeGenerations;
use LBHurtado\XCampaign\Contracts\PlansCampaignQueueDispatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignRecipientImportRows;
use LBHurtado\XCampaign\Contracts\PayCodeGenerationGateway;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAudienceImportReviewSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsOperatorSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignAnalyticsSnapshotBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignClaimVisibilitySummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignCockpitSummaryBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignDeliveryHandoffSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignExecutionHandoffSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignExportHandoffPlanner;
use LBHurtado\XCampaign\ReadModels\CampaignOperatorReportBuilder;
use LBHurtado\XCampaign\ReadModels\CampaignPortableCodeGenerationSummaryReadModel;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;
use LBHurtado\XCampaign\Gateways\NullPortableCodeGenerationGateway;
use LBHurtado\XCampaign\Gateways\NullCampaignClaimStatusProvider;
use LBHurtado\XCampaign\Queue\CampaignQueueDispatcher;
use LBHurtado\XCampaign\Repositories\EloquentCampaignPlanSnapshotRepository;
use LBHurtado\XCampaign\Repositories\InMemoryCampaignPlanRepository;
use LBHurtado\XCampaign\Services\CampaignStateGrammar;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadAnalyticsSnapshotMapper;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadClaimVisibilityMapper;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadDeliveryHandoffMapper;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExecutionHandoffMapper;
use LBHurtado\XCampaign\Services\CampaignQueuedPayloadExportHandoffMapper;
use LBHurtado\XCampaign\Services\ConfigCampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportApprovalWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignAnalyticsWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignClaimVisibilityWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignCockpitWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignDeliveryHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPlanningWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignExecutionHandoffWorkspace;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPortableCodeGenerationWorkspace;
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
        $this->app->singleton(PlansCampaignClaimVisibilities::class, PlanCampaignClaimVisibility::class);
        $this->app->singleton(PlansCampaignDeliveryHandoffs::class, PlanCampaignDeliveryHandoff::class);
        $this->app->singleton(PlansCampaignExportHandoffs::class, CampaignExportHandoffPlanner::class);
        $this->app->singleton(PlansCampaignRecipientImportRows::class, PlanCampaignRecipientImportRow::class);
        $this->app->singleton(PlansCampaignQueueDispatches::class, PlanCampaignQueueDispatch::class);
        $this->app->singleton(DispatchesCampaignQueuedPlans::class, CampaignQueueDispatcher::class);
        $this->app->singleton(MapsCampaignQueuedPayloadsToAnalyticsSnapshots::class, CampaignQueuedPayloadAnalyticsSnapshotMapper::class);
        $this->app->singleton(MapsCampaignQueuedPayloadsToClaimVisibilities::class, CampaignQueuedPayloadClaimVisibilityMapper::class);
        $this->app->singleton(MapsCampaignQueuedPayloadsToDeliveryHandoffs::class, CampaignQueuedPayloadDeliveryHandoffMapper::class);
        $this->app->singleton(MapsCampaignQueuedPayloadsToExecutionHandoffs::class, CampaignQueuedPayloadExecutionHandoffMapper::class);
        $this->app->singleton(MapsCampaignQueuedPayloadsToExportHandoffs::class, CampaignQueuedPayloadExportHandoffMapper::class);
        $this->app->singleton(CampaignClaimStatusProvider::class, NullCampaignClaimStatusProvider::class);
        $this->app->singleton(PayCodeGenerationGateway::class, NullPortableCodeGenerationGateway::class);
        $this->app->singleton(PlansCampaignExecutions::class, PlanCampaignExecution::class);
        $this->app->singleton(PlansCampaignExecutionBatches::class, PlanCampaignExecutionBatches::class);
        $this->app->singleton(PlansCampaignExecutionHandoffs::class, PlanCampaignExecutionHandoff::class);
        $this->app->singleton(PlansCampaignPortableCodeGenerations::class, PlanCampaignPortableCodeGeneration::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorReadModels::class, CampaignAudienceImportAttachmentOperatorReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaries::class, CampaignAudienceImportAttachmentOperatorWorkspaceCollectionOperatorSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportAttachmentOperatorWorkspaceCollections::class, CampaignAudienceImportAttachmentOperatorWorkspaceCollectionReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportRecipientAttachmentMutationSummaries::class, CampaignAudienceImportRecipientAttachmentMutationSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAudienceImportReviewSummaries::class, CampaignAudienceImportReviewSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAnalyticsOperatorSummaries::class, CampaignAnalyticsOperatorSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignAnalyticsSnapshots::class, CampaignAnalyticsSnapshotBuilder::class);
        $this->app->singleton(BuildsCampaignClaimVisibilitySummaries::class, CampaignClaimVisibilitySummaryReadModel::class);
        $this->app->singleton(BuildsCampaignCockpitSummaries::class, CampaignCockpitSummaryBuilder::class);
        $this->app->singleton(BuildsCampaignDeliveryHandoffSummaries::class, CampaignDeliveryHandoffSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignExecutionHandoffSummaries::class, CampaignExecutionHandoffSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignOperatorReports::class, CampaignOperatorReportBuilder::class);
        $this->app->singleton(BuildsCampaignPortableCodeGenerationSummaries::class, CampaignPortableCodeGenerationSummaryReadModel::class);
        $this->app->singleton(BuildsCampaignSummaries::class, CampaignSummaryReadModel::class);
        $this->app->singleton(CampaignPlanRepository::class, InMemoryCampaignPlanRepository::class);
        $this->app->singleton(CampaignPlanSnapshotRepository::class, EloquentCampaignPlanSnapshotRepository::class);
        $this->app->singleton(CampaignPlanningWorkspace::class, RepositoryBackedCampaignPlanningWorkspace::class);
        $this->app->singleton(CampaignAnalyticsWorkspace::class, RepositoryBackedCampaignAnalyticsWorkspace::class);
        $this->app->singleton(CampaignAudienceImportAttachmentOperatorWorkspace::class, RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspace::class);
        $this->app->singleton(CampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class, RepositoryBackedCampaignAudienceImportAttachmentOperatorWorkspaceCollectionWorkspace::class);
        $this->app->singleton(CampaignAudienceImportApprovalWorkspace::class, RepositoryBackedCampaignAudienceImportApprovalWorkspace::class);
        $this->app->singleton(CampaignAudienceImportRecipientAttachmentMutationWorkspace::class, RepositoryBackedCampaignAudienceImportRecipientAttachmentMutationWorkspace::class);
        $this->app->singleton(CampaignAudienceImportRecipientAttachmentWorkspace::class, RepositoryBackedCampaignAudienceImportRecipientAttachmentWorkspace::class);
        $this->app->singleton(CampaignAudienceImportWorkspace::class, RepositoryBackedCampaignAudienceImportWorkspace::class);
        $this->app->singleton(CampaignClaimVisibilityWorkspace::class, RepositoryBackedCampaignClaimVisibilityWorkspace::class);
        $this->app->singleton(CampaignCockpitWorkspace::class, RepositoryBackedCampaignCockpitWorkspace::class);
        $this->app->singleton(CampaignDeliveryHandoffWorkspace::class, RepositoryBackedCampaignDeliveryHandoffWorkspace::class);
        $this->app->singleton(CampaignExecutionHandoffWorkspace::class, RepositoryBackedCampaignExecutionHandoffWorkspace::class);
        $this->app->singleton(CampaignPortableCodeGenerationWorkspace::class, RepositoryBackedCampaignPortableCodeGenerationWorkspace::class);
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
