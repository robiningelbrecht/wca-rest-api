<?php

namespace App\Domain\Continent\Country;

use App\Infrastructure\Overview\Item;

readonly class Country implements Item
{
    private function __construct(
        private Iso2Code $iso2Code,
        private string $name,
    ) {
    }

    public static function fromState(
        Iso2Code $iso2Code,
        string $name,
    ): self {
        return new self($iso2Code, $name);
    }

    public function getIso2Code(): Iso2Code
    {
        return $this->iso2Code;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'iso2Code' => $this->iso2Code,
            'name' => $this->name,
        ];
    }
}
