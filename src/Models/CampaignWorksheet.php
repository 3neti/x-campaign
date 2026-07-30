<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CampaignWorksheet extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'owner_type',
        'owner_id',
        'profile',
        'name',
        'currency',
        'status',
        'pay_code_template_reference',
        'fulfillment_mode',
        'delivery_plan',
        'metadata',
        'rows_hash',
        'instruction_blueprint_ciphertext',
        'instruction_blueprint_hash',
        'instruction_blueprint_schema',
        'instruction_blueprint_revision',
        'manifest_hash',
        'frozen_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $worksheet): void {
            $worksheet->reference ??= (string) Str::ulid();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'delivery_plan' => 'array',
            'metadata' => 'array',
            'instruction_blueprint_ciphertext' => 'encrypted:array',
            'instruction_blueprint_revision' => 'integer',
            'frozen_at' => 'immutable_datetime',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<CampaignWorksheetRow, $this>
     */
    public function rows(): HasMany
    {
        return $this->hasMany(CampaignWorksheetRow::class)->orderBy('ordinal');
    }

    /** @return HasMany<CampaignWorksheetImport, $this> */
    public function imports(): HasMany
    {
        return $this->hasMany(CampaignWorksheetImport::class);
    }

    /** @return HasMany<CampaignWorksheetAuthorization, $this> */
    public function authorizations(): HasMany
    {
        return $this->hasMany(CampaignWorksheetAuthorization::class);
    }
}
