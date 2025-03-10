<?php

namespace App\Domain\Competition;

use App\Infrastructure\ValueObject\Geography\Coordinates;

readonly class Venue implements \JsonSerializable
{
    private function __construct(
        private string $name,
        private ?string $address = null,
        private ?string $details = null,
        private ?Coordinates $coordinates = null,
    ) {
    }

    public static function fromValues(
        string $name,
        string $address = null,
        string $details = null,
        Coordinates $coordinates = null,
    ): self {
        return new self(
            $name,
            $address,
            $details,
            $coordinates,
        );
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'details' => $this->details,
            'coordinates' => $this->coordinates,
        ];
    }
}
