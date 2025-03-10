<?php

namespace App\Domain\Competition;

use App\Domain\Continent\Country\Iso2Code;
use App\Infrastructure\Overview\Item;
use App\Infrastructure\ValueObject\Time\DateRange;

readonly class Competition implements Item
{
    private function __construct(
        private string $id,
        private string $name,
        private string $city,
        private Iso2Code $country,
        private DateRange $date,
        private bool $isCanceled,
        /** @var string[] */
        private array $events,
        /** @var array<mixed> */
        private array $wcaDelegates,
        private Venue $venue,
        /** @var array<mixed> */
        private array $organisers = [],
        private ?string $information = null,
        private ?string $externalWebsite = null,
    ) {
    }

    /**
     * @param string[]     $events
     * @param array<mixed> $wcaDelegates
     * @param array<mixed> $organisers
     */
    public static function fromState(
        string $id,
        string $name,
        string $city,
        Iso2Code $country,
        DateRange $date,
        bool $isCanceled,
        array $events,
        array $wcaDelegates,
        Venue $venue,
        array $organisers = [],
        string $information = null,
        string $externalWebsite = null,
    ): self {
        return new self(
            id: $id,
            name: $name,
            city: $city,
            country: $country,
            date: $date,
            isCanceled: $isCanceled,
            events: $events,
            wcaDelegates: $wcaDelegates,
            venue: $venue,
            organisers: $organisers,
            information: $information,
            externalWebsite: $externalWebsite,
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
            'date' => $this->date,
            'isCanceled' => $this->isCanceled,
            'events' => $this->events,
            'wcaDelegates' => $this->wcaDelegates,
            'organisers' => array_filter($this->organisers),
            'venue' => $this->venue,
            'information' => $this->information,
            'externalWebsite' => $this->externalWebsite,
        ];
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
