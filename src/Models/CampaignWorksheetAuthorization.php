<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignWorksheetAuthorization extends Model
{
    protected $fillable = ['campaign_worksheet_id', 'manifest_hash', 'beneficiary_count', 'principal_minor', 'currency', 'status', 'approval_pay_code'];

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
}
