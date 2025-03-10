<?php

namespace App\Domain\Continent;

use App\Infrastructure\Overview\Item;
use App\Infrastructure\ValueObject\String\Slug;

readonly class Continent implements Item
{
    private function __construct(
        private string $id,
        private string $name,
    ) {
    }

    public static function fromState(
        string $id,
        string $name,
    ): self {
        return new self($id, $name);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSlug(): Slug
    {
        return Slug::fromString($this->name);
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getSlug(),
            'name' => $this->name,
        ];
    }
}
