<?php

namespace App\Domain\Competition\Championship;

use App\Domain\Competition\Competition;
use App\Infrastructure\Overview\Item;
use App\Infrastructure\ValueObject\String\Slug;

readonly class Championship implements Item
{
    private function __construct(
        private Competition $competition,
        private string $region,
    ) {
    }

    public static function fromCompetitionAndRegion(
        Competition $competition,
        string $region,
    ): self {
        return new self($competition, $region);
    }

    public function getId(): string
    {
        return $this->competition->getId();
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            ...$this->competition->jsonSerialize(),
            'region' => Slug::fromString($this->region),
        ];
    }
}
