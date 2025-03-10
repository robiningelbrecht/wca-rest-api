<?php

declare(strict_types=1);

namespace App\Domain\Result;

enum Record: string
{
    case WR = 'WR';
    case CR = 'CR';
    case NR = 'NR';

    public static function tryFromMap(string $value = null): ?self
    {
        if (!$value) {
            return null;
        }

        return match (strtoupper($value)) {
            'WR' => self::WR,
            'NR' => self::NR,
            default => self::CR,
        };
    }
}
