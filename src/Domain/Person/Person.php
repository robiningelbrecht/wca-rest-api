<?php

namespace App\Domain\Person;

use App\Domain\Continent\Country\Iso2Code;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankType;
use App\Domain\Result\Result;
use App\Infrastructure\Overview\Item;
use App\Infrastructure\ValueObject\String\Slug;

readonly class Person implements Item
{
    private function __construct(
        private string $id,
        private string $name,
        private Iso2Code $country,
        /** @var string[] */
        private array $competitionIds,
        /** @var Rank[] */
        private array $ranks,
        /** @var Result[] */
        private array $results,
        /** @var string[] */
        private array $championshipIds,
    ) {
    }

    /**
     * @param string[] $competitionIds
     * @param Rank[]   $ranks
     * @param Result[] $results
     * @param string[] $championshipIds
     */
    public static function fromState(
        string $id,
        string $name,
        Iso2Code $country,
        array $competitionIds,
        array $ranks,
        array $results,
        array $championshipIds,
    ): self {
        return new self(
            id: $id,
            name: $name,
            country: $country,
            competitionIds: $competitionIds,
            ranks: $ranks,
            results: $results,
            championshipIds: $championshipIds
        );
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
        $singles = array_filter($this->ranks, fn (Rank $rank) => RankType::SINGLE === $rank->getRankType());
        $averages = array_filter($this->ranks, fn (Rank $rank) => RankType::AVERAGE === $rank->getRankType());

        $results = [];
        /** @var Result $result */
        foreach ($this->results as $result) {
            $results[$result->getCompetitionId()][$result->getEventId()][] = [
                'round' => $result->getRound(),
                'position' => $result->getPosition(),
                'best' => $result->getBest(),
                'average' => $result->getAverage(),
                'format' => $result->getFormat(),
                'solves' => $result->getSolves(),
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->getSlug(),
            'country' => $this->country,
            'numberOfCompetitions' => count($this->competitionIds),
            'competitionIds' => $this->competitionIds,
            'numberOfChampionships' => count($this->championshipIds),
            'championshipIds' => $this->championshipIds,
            'rank' => [
                'singles' => array_map(fn (Rank $rank) => [
                    'eventId' => $rank->getEventId(),
                    'best' => $rank->getBest(),
                    'rank' => [
                        'world' => $rank->getWorldRank(),
                        'continent' => $rank->getContinentRank(),
                        'country' => $rank->getCountryRank(),
                    ],
                ], array_values($singles)),
                'averages' => array_map(fn (Rank $rank) => [
                    'eventId' => $rank->getEventId(),
                    'best' => $rank->getBest(),
                    'rank' => [
                        'world' => $rank->getWorldRank(),
                        'continent' => $rank->getContinentRank(),
                        'country' => $rank->getCountryRank(),
                    ],
                ], array_values($averages)),
            ],
            'results' => $results,
            'medals' => [
                'gold' => count(array_filter($this->results, fn (Result $result) => $result->isFinalRound() && 1 == $result->getPosition())),
                'silver' => count(array_filter($this->results, fn (Result $result) => $result->isFinalRound() && 2 == $result->getPosition())),
                'bronze' => count(array_filter($this->results, fn (Result $result) => $result->isFinalRound() && 3 == $result->getPosition())),
            ],
            'records' => [
                'single' => array_count_values(array_map(
                    fn (Result $result) => $result->getSingleRecord()?->value,
                    array_filter($this->results, fn (Result $result) => !empty($result->getSingleRecord()))
                )),
                'average' => array_count_values(array_map(
                    fn (Result $result) => $result->getAverageRecord()?->value,
                    array_filter($this->results, fn (Result $result) => !empty($result->getAverageRecord()))
                )),
            ],
        ];
    }
}
