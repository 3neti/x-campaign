<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignPlan extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'planning_key',
        'campaign_id',
        'name',
        'status',
        'metadata',
        'effects',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'effects' => 'array',
        ];
    }
}
