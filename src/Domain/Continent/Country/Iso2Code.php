<?php

namespace App\Domain\Continent\Country;

use App\Infrastructure\ValueObject\String\NonEmptyStringLiteral;

readonly class Iso2Code extends NonEmptyStringLiteral
{
    public function __construct(
        string $string,
    ) {
        parent::__construct($string);
    }

    public static function fromString(string $string): static
    {
        if (2 != strlen($string)) {
            throw new \InvalidArgumentException('Invalid ISO2 code');
        }

        return new static($string);
    }
}
