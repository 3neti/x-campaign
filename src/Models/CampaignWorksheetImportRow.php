<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignWorksheetImportRow extends Model
{
    protected $fillable = [
        'source_row',
        'status',
        'source_ciphertext',
        'normalized_ciphertext',
        'errors_ciphertext',
        'applied_at',
    ];

    protected $hidden = [
        'source_ciphertext',
        'normalized_ciphertext',
        'errors_ciphertext',
    ];

    protected function casts(): array
    {
        return [
            'source_row' => 'integer',
            'source_ciphertext' => 'encrypted:array',
            'normalized_ciphertext' => 'encrypted:array',
            'errors_ciphertext' => 'encrypted:array',
            'applied_at' => 'immutable_datetime',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(
            CampaignWorksheetImport::class,
            'campaign_worksheet_import_id',
        );
    }
}
