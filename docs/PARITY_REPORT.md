# x-campaign Parity Report

## Executive Summary

Status: package-side host adoption baseline is aligned for read-only and decision-support integration.

`x-campaign` now exposes package-safe seams for campaign planning, audience planning, execution planning, import planning, handoff planning, analytics/read models, operational readiness, Cockpit consumption, public API descriptions, and host mutation decision support.

The package still does not own host routes, controllers, form requests, API resources, middleware, policies, operator authorization, provider delivery, journal storage, Pay Code generation semantics, wallet mutation, or money movement.

## Functional Specification vs As-Built Classes

| Functional area | Functional specification intent | As-built package classes | Current parity |
| --- | --- | --- | --- |
| Campaign lifecycle | Create, update, schedule, archive campaigns | `CreateCampaignPlan`, `UpdateCampaignPlan`, `ScheduleCampaignPlan`, `ArchiveCampaignPlan`, `CampaignPlanningWorkspace` | Aligned as in-memory/repository-backed planning baseline |
| Audience management | Import and manage recipient audiences | `PlanCampaignAudienceImport`, `PlanCampaignAudienceImportRowCollection`, `CampaignAudienceImportWorkspace`, `CampaignAudienceImportApprovalWorkspace` | Aligned as planning/review/approval baseline; file parsing remains host/future work |
| Recipient management | Add/remove recipients and track audience assignment | `AddRecipientToCampaignAudiencePlan`, `RemoveRecipientFromCampaignAudiencePlan`, recipient import row and attachment workspaces | Aligned for in-memory planning and approved attachment |
| Campaign execution | Create execution records and execution partitions | `PlanCampaignExecution`, `PlanCampaignExecutionBatches`, `CampaignExecutionHandoffWorkspace` | Aligned as planning/handoff baseline; no real queue execution |
| Pay Code generation | Consume x-change Pay Code generation; do not own voucher templates | `PlanCampaignPortableCodeGeneration`, `PayCodeGenerationGateway`, `NullPortableCodeGenerationGateway`, `CampaignPortableCodeGenerationWorkspace` | Aligned as gateway/handoff baseline; no direct generation semantics |
| Distribution and feedback | Plan delivery and hand off to feedback infrastructure | `PlanCampaignDeliveryHandoff`, `CampaignDeliveryHandoffWorkspace` | Aligned as handoff baseline; no provider delivery |
| Claim visibility | Consume claim status from x-change | `CampaignClaimStatusProvider`, `NullCampaignClaimStatusProvider`, `CampaignClaimVisibilityWorkspace` | Aligned as visibility baseline; no claim lifecycle ownership |
| Analytics | Build campaign metrics and operator summaries | `CampaignAnalyticsSnapshotBuilder`, `CampaignAnalyticsWorkspace`, `CampaignOperatorReportBuilder` | Aligned as read-model/report baseline |
| Cockpit consumption | Expose campaign intelligence inside x-change Cockpit | `CampaignCockpitWorkspace`, `CampaignCockpitConsumptionMapBuilder`, `CampaignCockpitApiResponsePresenter` | Aligned as read-only host consumption baseline |
| Public API | Describe host-safe API candidates | `CampaignPublicApiDescriptorBuilder`, `CampaignPublicApiWorkspace`, `CampaignPublicApiResponsePresenter`, `CampaignPublicApiEndpointRecommendationMatrixBuilder` | Aligned as descriptor/recommendation baseline; no package routes |
| Host mutation authorization | Ensure host controls mutation gates | `CampaignHostMutationAuthorizationChecklistBuilder` | Aligned as decision-support baseline; no package authorization engine |

## Host Adoption Surface

| Surface | Package seam | Host responsibility | Package responsibility | Mutation status |
| --- | --- | --- | --- | --- |
| Campaign dashboard | `CampaignCockpitWorkspace` | route, controller, authorization, redaction | read model and response shape | Read-only |
| Campaign explorer | `CampaignPlanningWorkspace` | route, controller, request validation, authorization | planning workspace contract | Host-gated |
| Recipient explorer | audience import / attachment operator workspaces | route, controller, authorization, redaction | read model aggregation | Read-only / host-gated |
| Production readiness | `CampaignProductionReadinessWorkspace` | route, controller, deployment decision | readiness assessment | Read-only |
| Host integration | `CampaignHostIntegrationWorkspace` and Phase 15 manifest DTOs | adoption decision and host wiring | capability description | Read-only |
| Public API | `CampaignPublicApiWorkspace` and endpoint recommendation matrix | actual API routes, controllers, resources, validation | descriptor and presenter seams | Read-only by default |
| Mutation-capable operations | mutation authorization checklist | authorization, idempotency, pricing, funding, journal/feedback handoff | checklist shape and blocked operation list | Blocked until host explicitly implements |

## As-Built Feature / Benefit Table

| Feature | Benefit |
| --- | --- |
| In-memory planning actions | Allows campaign workflows to be characterized without durable side effects |
| Repository-backed workspaces | Gives hosts stable composition seams before real persistence expansion |
| Gateway contracts | Keeps Pay Code, claim, feedback, and provider responsibilities outside campaign core |
| Effect metadata | Makes side-effect boundaries explicit and testable |
| Cockpit consumption map | Shows x-change exactly which campaign surfaces are safe to display |
| Endpoint recommendation matrix | Lets x-change wire routes/controllers without `x-campaign` owning transport |
| Host mutation authorization checklist | Prevents accidental exposure of mutating operations without host gates |
| Architecture parity tests | Protect package boundaries as scaffolding grows |

## Remaining Gaps

| Gap | Required future owner / slice |
| --- | --- |
| Real file parsing for CSV/Excel imports | Future audience import implementation |
| Durable campaign tables and repositories | Future persistence hardening |
| Queue-backed campaign execution | Future queue execution slice |
| Real Pay Code generation gateway implementation | x-change host integration |
| Real feedback delivery handoff implementation | x-feedback / host integration |
| Real journal event emission | x-journal / host integration |
| Host API routes/controllers/resources | x-change host application |
| Host authorization/redaction policies | x-change host application |
| Provider callbacks and reconciliation | x-change / provider integration |
| Recipient longitudinal intelligence | Future recipient intelligence phase |

## Verification

Latest Phase 15F verification:

- Focused parity tests: `3 passed, 27 assertions`
- Full package tests: `429 passed, 4434 assertions`
- Composer validation: `composer validate --strict` passed
- Formatter note: `vendor/bin/pint --dirty --format agent` is unavailable because `vendor/bin/pint` does not exist in this package

## Verdict

The package is ready for host-side x-change adoption planning.

The next implementation should happen in the host application only after selecting the exact x-change routes/controllers/API resources and preserving the boundaries documented here.
