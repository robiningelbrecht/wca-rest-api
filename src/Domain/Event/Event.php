<?php

namespace App\Domain\Event;

use App\Infrastructure\Overview\Item;

readonly class Event implements Item
{
    private function __construct(
        private string $id,
        private string $name,
        private string $format,
    ) {
    }

    public static function fromState(
        string $id,
        string $name,
        string $format,
    ): self {
        return new self(
            id: $id,
            name: $name,
            format: $format
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'format' => $this->format,
        ];
    }
}
