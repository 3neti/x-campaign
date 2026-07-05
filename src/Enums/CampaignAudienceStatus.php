<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Enums;

use InvalidArgumentException;

enum CampaignAudienceStatus: string
{
    case Draft = 'draft';
    case Importing = 'importing';
    case Ready = 'ready';
    case Locked = 'locked';
    case Archived = 'archived';

    public static function normalize(null|string|self $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        $value = is_string($status) ? strtolower(trim($status)) : '';

        return match ($value) {
            '', 'draft' => self::Draft,
            'importing', 'import' => self::Importing,
            'ready', 'active' => self::Ready,
            'locked', 'lock' => self::Locked,
            'archived', 'archive' => self::Archived,
            default => throw new InvalidArgumentException("Unknown campaign audience status [{$status}]."),
        };
    }
}
