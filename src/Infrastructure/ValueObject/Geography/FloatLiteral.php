<?php

namespace App\Infrastructure\ValueObject\Geography;

class FloatLiteral implements \JsonSerializable
{
    public function __construct(
        private readonly float $float
    ) {
    }

    public function jsonSerialize(): float
    {
        return $this->float;
    }

    public static function fromString(string $string): static
    {
        if (!\is_numeric($string)) {
            throw new \InvalidArgumentException(\sprintf('Invalid %s: %s', static::class, $string));
        }

        return new static((float) \trim($string));
    }
}
