<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class EndpointCampaign extends Model
{
    protected $table = 'x_change_lead_campaigns';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'pay_code_template_id',
        'active_template_version_id',
        'merchant_display_name',
        'merchant_slug',
        'endpoint_slug',
        'title',
        'description',
        'status',
        'usage_count',
        'last_started_at',
        'starts_limit',
        'expires_at',
        'merchant_registry_id',
        'merchant_certification_status',
        'merchant_certification_snapshot',
        'settings',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $campaign): void {
            $campaign->reference ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'usage_count' => 'integer',
            'last_started_at' => 'datetime',
            'starts_limit' => 'integer',
            'expires_at' => 'datetime',
            'merchant_certification_snapshot' => 'array',
            'settings' => 'array',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
