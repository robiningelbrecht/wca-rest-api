<?php

namespace App\Domain\Rank;

use App\Infrastructure\Overview\Item;

readonly class Rank implements Item
{
    private function __construct(
        private RankType $rankType,
        private string $personId,
        private string $eventId,
        private int $best,
        private int $worldRank,
        private int $continentRank,
        private int $countryRank,
    ) {
    }

    public static function fromState(
        RankType $rankType,
        string $personId,
        string $eventId,
        int $best,
        int $worldRank,
        int $continentRank,
        int $countryRank,
    ): self {
        return new self(
            rankType: $rankType,
            personId: $personId,
            eventId: $eventId,
            best: $best,
            worldRank: $worldRank,
            continentRank: $continentRank,
            countryRank: $countryRank,
        );
    }

    public function getRankType(): RankType
    {
        return $this->rankType;
    }

    public function getPersonId(): string
    {
        return $this->personId;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getBest(): int
    {
        return $this->best;
    }

    public function getWorldRank(): int
    {
        return $this->worldRank;
    }

    public function getContinentRank(): int
    {
        return $this->continentRank;
    }

    public function getCountryRank(): int
    {
        return $this->countryRank;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'rankType' => $this->rankType->value,
            'personId' => $this->personId,
            'eventId' => $this->eventId,
            'best' => $this->best,
            'rank' => [
                'world' => $this->worldRank,
                'continent' => $this->continentRank,
                'country' => $this->countryRank,
            ],
        ];
    }
}
