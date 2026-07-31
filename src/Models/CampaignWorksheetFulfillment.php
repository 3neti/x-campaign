<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignWorksheetFulfillment extends Model
{
    protected $fillable = ['campaign_worksheet_authorization_id', 'campaign_worksheet_row_id', 'mode', 'status', 'pay_code', 'provider_transfer_reference', 'metadata'];

    protected static function booted(): void
    {
        static::creating(function (self $fulfillment): void {
            $fulfillment->reference ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    /** @return BelongsTo<CampaignWorksheetAuthorization, $this> */
    public function authorization(): BelongsTo
    {
        return $this->belongsTo(CampaignWorksheetAuthorization::class, 'campaign_worksheet_authorization_id');
    }

    /** @return BelongsTo<CampaignWorksheetRow, $this> */
    public function row(): BelongsTo
    {
        return $this->belongsTo(CampaignWorksheetRow::class, 'campaign_worksheet_row_id');
    }
}
