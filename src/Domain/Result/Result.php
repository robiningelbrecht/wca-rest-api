<?php

namespace App\Domain\Result;

use App\Infrastructure\Overview\Item;

readonly class Result implements Item
{
    private function __construct(
        private string $competitionId,
        private string $personId,
        private string $eventId,
        private string $round,
        private bool $isFinalRound,
        private int $position,
        private int $best,
        private int $average,
        private string $format,
        /** @var int[] */
        private array $solves,
        private ?Record $singleRecord = null,
        private ?Record $averageRecord = null,
    ) {
    }

    /**
     * @param int[] $solves
     */
    public static function fromState(
        string $competitionId,
        string $personId,
        string $eventId,
        string $round,
        bool $isFinalRound,
        int $position,
        int $best,
        int $average,
        string $format,
        array $solves,
        Record $singleRecord = null,
        Record $averageRecord = null,
    ): self {
        return new self(
            competitionId: $competitionId,
            personId: $personId,
            eventId: $eventId,
            round: $round,
            isFinalRound: $isFinalRound,
            position: $position,
            best: $best,
            average: $average,
            format: $format,
            solves: $solves,
            singleRecord: $singleRecord,
            averageRecord: $averageRecord
        );
    }

    public function getCompetitionId(): string
    {
        return $this->competitionId;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getRound(): string
    {
        return $this->round;
    }

    public function isFinalRound(): bool
    {
        return $this->isFinalRound;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getBest(): int
    {
        return $this->best;
    }

    public function getAverage(): int
    {
        return $this->average;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return int[]
     */
    public function getSolves(): array
    {
        return $this->solves;
    }

    public function getSingleRecord(): ?Record
    {
        return $this->singleRecord;
    }

    public function getAverageRecord(): ?Record
    {
        return $this->averageRecord;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'competitionId' => $this->competitionId,
            'personId' => $this->personId,
            'eventId' => $this->eventId,
            'round' => $this->round,
            'position' => $this->position,
            'best' => $this->best,
            'average' => $this->average,
            'format' => $this->format,
            'solves' => $this->solves,
        ];
    }
}
