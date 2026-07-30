<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Services;

use JsonException;

final class CampaignWorksheetManifestHasher
{
    /**
     * @param  array<string, mixed>|array<int, mixed>  $value
     *
     * @throws JsonException
     */
    public function hash(array $value): string
    {
        return hash_hmac(
            'sha256',
            json_encode($this->canonicalize($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            (string) config('app.key'),
        );
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    public function canonicalize(array $value): array
    {
        if (array_is_list($value)) {
            return array_map(
                fn (mixed $item): mixed => is_array($item) ? $this->canonicalize($item) : $item,
                $value,
            );
        }

        ksort($value);

        return array_map(
            fn (mixed $item): mixed => is_array($item) ? $this->canonicalize($item) : $item,
            $value,
        );
    }
}
