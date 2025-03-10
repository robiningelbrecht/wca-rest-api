<?php

namespace App\Infrastructure\ValueObject\Geography;

readonly class Coordinates implements \JsonSerializable
{
    public function __construct(
        private Latitude $latitude,
        private Longitude $longitude)
    {
    }

    public static function fromIntegers(
        int $latitude,
        int $longitude
    ): self {
        return new self(
            Latitude::fromString((string) ($latitude / 1000000)),
            Longitude::fromString((string) ($longitude / 1000000))
        );
    }

    public function getLatitude(): Latitude
    {
        return $this->latitude;
    }

    public function getLongitude(): Longitude
    {
        return $this->longitude;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }
}
