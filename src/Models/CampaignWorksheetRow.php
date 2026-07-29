<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CampaignWorksheetRow extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'ordinal',
        'beneficiary_ciphertext',
        'amount_minor',
        'currency',
        'delivery_preference',
        'status',
        'metadata',
    ];

    protected $hidden = [
        'beneficiary_ciphertext',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $row): void {
            $row->reference ??= (string) Str::ulid();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'beneficiary_ciphertext' => 'encrypted:array',
            'metadata' => 'array',
            'amount_minor' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<CampaignWorksheet, $this>
     */
    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(CampaignWorksheet::class, 'campaign_worksheet_id');
    }
}
