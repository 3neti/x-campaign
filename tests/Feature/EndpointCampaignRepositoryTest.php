<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LBHurtado\XCampaign\Contracts\EndpointCampaignRepository;
use LBHurtado\XCampaign\Models\EndpointCampaign;
use LBHurtado\XCampaign\Repositories\EloquentEndpointCampaignRepository;

beforeEach(function (): void {
    Schema::create('x_change_lead_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->string('reference')->unique();
        $table->string('owner_type');
        $table->string('owner_id');
        $table->string('title');
        $table->string('status');
        $table->string('merchant_slug')->nullable();
        $table->string('endpoint_slug')->nullable();
        $table->unsignedBigInteger('usage_count')->default(0);
        $table->timestamp('last_started_at')->nullable();
        $table->unique(['merchant_slug', 'endpoint_slug']);
        $table->timestamps();
    });
});

it('creates and finds the consuming model with the existing creation event and reference', function (): void {
    $prototype = new class extends EndpointCampaign {};
    $createdReferences = [];
    $prototype::created(function ($model) use (&$createdReferences): void {
        $createdReferences[] = $model->reference;
    });
    $repository = new EloquentEndpointCampaignRepository($prototype);
    $campaign = $repository->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Created campaign',
        'status' => 'paused', 'merchant_slug' => 'merchant', 'endpoint_slug' => 'apply',
    ]);

    $found = $repository->findByPublicEndpointOrFail('merchant', 'apply');
    expect($campaign::class)->toBe($prototype::class)
        ->and($found::class)->toBe($prototype::class)
        ->and($found->is($campaign))->toBeTrue()
        ->and($createdReferences)->toBe([$campaign->reference])
        ->and($campaign->reference)->toHaveLength(26)
        ->and($found->status)->toBe('paused')
        ->and($repository->publicEndpointExists('merchant', 'apply'))->toBeTrue()
        ->and($repository->publicEndpointExists('other', 'apply'))->toBeFalse()
        ->and($repository->publicEndpointExists('merchant', 'other'))->toBeFalse();

    expect(fn () => $repository->findByPublicEndpointOrFail('other', 'apply'))->toThrow(ModelNotFoundException::class);
    expect(fn () => $repository->findByPublicEndpointOrFail('merchant', 'other'))->toThrow(ModelNotFoundException::class);
});

it('scopes merchant slug collisions by both owner type and owner id', function (): void {
    $repository = app(EndpointCampaignRepository::class);
    $repository->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Existing',
        'status' => 'paused', 'merchant_slug' => 'merchant', 'endpoint_slug' => 'apply',
    ]);

    expect($repository->merchantSlugUsedByOtherOwner('merchant', 'account', '7'))->toBeFalse()
        ->and($repository->merchantSlugUsedByOtherOwner('merchant', 'other-type', '7'))->toBeTrue()
        ->and($repository->merchantSlugUsedByOtherOwner('merchant', 'account', '8'))->toBeTrue()
        ->and($repository->merchantSlugUsedByOtherOwner('unused', 'other-type', '8'))->toBeFalse();
});

it('increments from stored usage with stale instances without model update events', function (): void {
    $repository = app(EndpointCampaignRepository::class);
    $campaign = $repository->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Counter',
        'status' => 'active', 'merchant_slug' => 'merchant', 'endpoint_slug' => 'counter',
    ])->refresh();
    $other = $repository->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Untouched',
        'status' => 'active', 'merchant_slug' => 'merchant', 'endpoint_slug' => 'untouched',
    ])->refresh();
    $stale = $repository->findByPublicEndpointOrFail('merchant', 'counter');
    $events = [];
    EndpointCampaign::updated(function ($model) use (&$events): void {
        $events[] = $model->reference;
    });
    $this->freezeSecond();
    $repository->recordSuccessfulStart($campaign);
    $repository->recordSuccessfulStart($stale);

    expect($campaign->usage_count)->toBe(0)
        ->and($stale->usage_count)->toBe(0)
        ->and($campaign->fresh()->usage_count)->toBe(2)
        ->and($campaign->fresh()->last_started_at->equalTo(now()))->toBeTrue()
        ->and($campaign->fresh()->updated_at->equalTo(now()))->toBeTrue()
        ->and($other->fresh()->usage_count)->toBe(0)
        ->and($other->fresh()->last_started_at)->toBeNull()
        ->and($events)->toBeEmpty();
});

it('keeps start recording inside the callers transaction', function (): void {
    $repository = app(EndpointCampaignRepository::class);
    $campaign = $repository->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Rollback', 'status' => 'active',
    ])->refresh();

    try {
        DB::transaction(function () use ($repository, $campaign): void {
            $repository->recordSuccessfulStart($campaign);
            throw new RuntimeException('Rollback probe');
        });
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())->toBe('Rollback probe');
    }

    expect($campaign->fresh()->usage_count)->toBe(0)
        ->and($campaign->fresh()->last_started_at)->toBeNull();
});

it('reads only the latest 25 endpoints for the exact owner without changing records', function (): void {
    foreach (range(1, 26) as $ordinal) {
        $record = new EndpointCampaign([
            'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Campaign '.$ordinal,
            'status' => $ordinal % 2 === 0 ? 'paused' : 'active',
        ]);
        $record->updated_at = now()->subMinutes(30 - $ordinal);
        $record->save();
    }
    foreach ([['other-account', '7'], ['account', '8']] as [$type, $id]) {
        EndpointCampaign::query()->create([
            'owner_type' => $type, 'owner_id' => $id, 'title' => 'Not yours', 'status' => 'active',
        ]);
    }

    DB::enableQueryLog();
    DB::flushQueryLog();
    $records = app(EndpointCampaignRepository::class)->recentForOwner('account', '7');
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($records)->toHaveCount(25)
        ->and($records->pluck('title')->all())->toBe(array_map(fn (int $i): string => 'Campaign '.$i, range(26, 2)))
        ->and($records->pluck('status')->unique()->sort()->values()->all())->toBe(['active', 'paused'])
        ->and($queries)->toHaveCount(1)
        ->and(strtolower($queries[0]['query']))->toStartWith('select')
        ->and(EndpointCampaign::query()->count())->toBe(28)
        ->and(app(EndpointCampaignRepository::class)->recentForOwner('missing', '7'))->toBeEmpty();
});

it('hydrates the consuming model subclass and dispatches its retrieved event', function (): void {
    $prototype = new class extends EndpointCampaign {};
    $created = $prototype->newQuery()->create([
        'owner_type' => 'account', 'owner_id' => '7', 'title' => 'Compatibility', 'status' => 'active',
    ]);
    $retrieved = [];
    $prototype::retrieved(function ($model) use (&$retrieved): void {
        $retrieved[] = $model->reference;
    });

    $records = (new EloquentEndpointCampaignRepository($prototype))->recentForOwner('account', '7');

    expect($records->sole()::class)->toBe($prototype::class)
        ->and($records->sole()->getMorphClass())->toBe($prototype->getMorphClass())
        ->and($retrieved)->toBe([$created->reference]);
});
