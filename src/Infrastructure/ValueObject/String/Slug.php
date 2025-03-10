<?php

namespace App\Infrastructure\ValueObject\String;

use Ausi\SlugGenerator\SlugGenerator;

readonly class Slug implements \Stringable, \JsonSerializable
{
    private function __construct(
        private string $slug
    ) {
    }

    public function __toString(): string
    {
        return $this->slug;
    }

    public function jsonSerialize(): string
    {
        return $this->slug;
    }

    public static function fromString(string $string): self
    {
        return new self((new SlugGenerator())->generate($string));
    }
}
