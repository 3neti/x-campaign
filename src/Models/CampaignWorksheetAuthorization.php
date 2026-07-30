<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignWorksheetAuthorization extends Model
{
    protected $fillable = [
        'campaign_worksheet_id',
        'manifest_hash',
        'rows_hash',
        'instruction_blueprint_ciphertext',
        'instruction_blueprint_hash',
        'instruction_blueprint_schema',
        'beneficiary_count',
        'principal_minor',
        'currency',
        'status',
        'approval_pay_code',
        'approved_by_type',
        'approved_by_id',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'instruction_blueprint_ciphertext' => 'encrypted:array',
            'approved_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $authorization): void {
            $authorization->reference ??= (string) Str::ulid();
        });
    }

    /** @return BelongsTo<CampaignWorksheet, $this> */
    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(CampaignWorksheet::class, 'campaign_worksheet_id');
    }

    /** @return HasMany<CampaignWorksheetFulfillment, $this> */
    public function fulfillments(): HasMany
    {
        return $this->hasMany(CampaignWorksheetFulfillment::class);
    }
}
